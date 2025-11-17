<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class OrderService
{
    protected OrderRepository $repo;
    protected InventoryService $inventoryService;

    public function __construct(OrderRepository $repo, InventoryService $inventoryService)
    {
        $this->repo = $repo;
        $this->inventoryService = $inventoryService;
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(Str::random(8));
    }

    /**
     * Create pending order and items. Does NOT deduct stock.
     */
    public function createPendingOrder(int $userId, array $payload): Order
    {
        return DB::transaction(function () use ($userId, $payload) {
            $order = $this->repo->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $userId,
                'status' => 'pending',
                'payment_status' => $payload['payment_status'] ?? 'pending',
                'total_amount' => 0,
                'shipping_address' => $payload['shipping_address'] ?? null,
                'billing_address' => $payload['billing_address'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);

            $total = '0.00';
            foreach ($payload['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['variant_id']);
                if (!$variant) {
                    throw ValidationException::withMessages(["items" => "Variant {$item['variant_id']} not found"]);
                }

                $unitPrice = $variant->sale_price ?? $variant->price;
                $quantity = (int)$item['quantity'];
                // calculate line total with bcmul if available
                if (function_exists('bcmul')) {
                    $lineTotal = bcmul((string)$unitPrice, (string)$quantity, 2);
                    $total = bcadd($total, $lineTotal, 2);
                } else {
                    $lineTotal = round($unitPrice * $quantity, 2);
                    $total = round($total + $lineTotal, 2);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'product_snapshot' => [
                        'product_id' => $variant->product->id,
                        'product_name' => $variant->product->name,
                        'sku' => $variant->sku,
                        'attributes' => $variant->attributes,
                    ],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->total_amount = $total;
            $order->save();

            return $order->fresh('items');
        });
    }

    /**
     * Confirm order: deduct stock (atomic). Throws ValidationException on insufficient stock.
     */
    public function confirmOrder(Order $order): Order
    {
        if ($order->status !== 'pending') {
            throw ValidationException::withMessages(['order' => 'Only pending orders can be confirmed.']);
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                // use InventoryService to decrement (handles lockForUpdate internally)
                try {
                    $this->inventoryService->decrement($item->variant_id, (int)$item->quantity, "Order confirm: {$order->order_number}");
                } catch (\Illuminate\Validation\ValidationException $e) {
                    // rethrow as validation exception with context
                    throw ValidationException::withMessages(['items' => $e->getMessage()]);
                }
            }

            $order->status = 'processing';
            $order->save();

            return $order->fresh('items');
        });
    }

    /**
     * Cancel order: restore inventory if it was deducted earlier.
     */
    public function cancelOrder(Order $order): Order
    {
        if ($order->status === 'cancelled') return $order;

        return DB::transaction(function () use ($order) {
            // restore only if status indicates inventory was deducted
            if (in_array($order->status, ['processing','shipped'])) {
                foreach ($order->items as $item) {
                    $this->inventoryService->increment($item->variant_id, (int)$item->quantity, "Order cancel: {$order->order_number}");
                }
            }

            $order->status = 'cancelled';
            $order->save();

            return $order->fresh('items');
        });
    }

    /**
     * Change status without inventory side-effects (admin/vendor).
     */
    public function changeStatus(Order $order, string $status): Order
    {
        $allowed = ['pending','processing','shipped','delivered','cancelled'];
        if (!in_array($status, $allowed)) {
            throw ValidationException::withMessages(['status' => 'Invalid status']);
        }

        $order->status = $status;
        $order->save();

        return $order->fresh('items');
    }
}
