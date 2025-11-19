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
use App\Models\Product;


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

        // Authorization: Admin can view any order, vendor can view orders containing their products, customer can view their own orders.
        if (!$user->hasRole('admin') && !($user->hasRole('vendor') && $this->vendorOwnsOrderProducts($user, $order)) && !($order->user_id === $user->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new OrderResource($order->load('items'));
    }

    public function confirm(Order $order, Request $request)
    {
        $user = $request->user();
        // Authorization: Only admin or vendor who owns products in the order can confirm
        if (!$user->hasRole('admin') && !($user->hasRole('vendor') && $this->vendorOwnsOrderProducts($user, $order))) {
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
        // Authorization: Only admin or vendor who owns products in the order can cancel
        if (!$user->hasRole('admin') && !($user->hasRole('vendor') && $this->vendorOwnsOrderProducts($user, $order))) { 
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
        // Authorization: Only admin or vendor who owns products in the order can change status
        if (!$user->hasRole('admin') && !($user->hasRole('vendor') && $this->vendorOwnsOrderProducts($user, $order))) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $status = $request->validated()['status'];
            $order = $this->service->changeStatus($order, $status);
            return new OrderResource($order);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not change status', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper function to check if a vendor owns any products within an order.
     */
    protected function vendorOwnsOrderProducts($user, Order $order): bool
    {
        foreach ($order->items as $item) {
            $productId = $item->product_snapshot['product_id'] ?? null;
            $product = $productId ? Product::find($productId) : null;
            if ($product && $product->vendor_id === $user->id) { 
                return true;
            }
        }
        return false;
    }
}
