<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Requests\Api\V1\ChangeOrderStatusRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected OrderService $service;
    protected OrderRepository $repo;

    public function __construct(OrderService $service, OrderRepository $repo)
    {
        $this->service = $service;
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int)$request->get('per_page', 15);

        if ($user->hasRole('admin')) {
            $orders = $this->repo->paginateAll($perPage);
            return OrderResource::collection($orders);
        }

        if ($user->hasRole('vendor')) {
            // vendor view: orders that include vendor products (simple approach)
            // This can be optimized with a proper query. For now fetch all and filter.
            $orders = Order::with('items')->whereHas('items', function($q) use ($user) {
                $q->whereJsonContains('product_snapshot->product_id', $user->id); // naive; adjust if needed
            })->paginate($perPage);

            return OrderResource::collection($orders);
        }

        $orders = $this->repo->paginateForUser($user->id, $perPage);
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        $payload = $request->validated();

        $order = $this->service->createPendingOrder($user->id, $payload);
        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(Order $order, Request $request)
    {
        $user = $request->user();

        // Authorization
        if ($user->hasRole('admin')) {
            // allowed
        } elseif ($user->hasRole('vendor')) {
            // check vendor owns any product in the order
            $owns = false;
            foreach ($order->items as $item) {
                $productId = $item->product_snapshot['product_id'] ?? null;
                if ($productId) {
                    $product = \App\Models\Product::find($productId);
                    if ($product && $product->vendor_id === $user->id) { $owns = true; break; }
                }
            }
            if (!$owns) return response()->json(['message'=>'Forbidden'], 403);
        } else {
            // customer
            if ($order->user_id !== $user->id) return response()->json(['message'=>'Forbidden'], 403);
        }

        return new OrderResource($order->load('items'));
    }

    public function confirm(Order $order, Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('admin') && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $order = $this->service->confirmOrder($order);
            return new OrderResource($order);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not confirm order', 'error' => $e->getMessage()], 500);
        }
    }

    public function cancel(Order $order, Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('admin') && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $order = $this->service->cancelOrder($order);
            return new OrderResource($order);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not cancel order', 'error' => $e->getMessage()], 500);
        }
    }

    public function changeStatus(Order $order, ChangeOrderStatusRequest $request)
    {
        $user = $request->user();
        if (!$user->hasRole('admin') && !$user->hasRole('vendor')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // vendor ownership check for status change
        if ($user->hasRole('vendor')) {
            $owns = false;
            foreach ($order->items as $item) {
                $productId = $item->product_snapshot['product_id'] ?? null;
                if ($productId) {
                    $product = \App\Models\Product::find($productId);
                    if ($product && $product->vendor_id === $user->id) { $owns = true; break; }
                }
            }
            if (!$owns) return response()->json(['message'=>'Forbidden'], 403);
        }

        try {
            $status = $request->validated()['status'];
            $order = $this->service->changeStatus($order, $status);
            return new OrderResource($order);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not change status', 'error' => $e->getMessage()], 500);
        }
    }
}
