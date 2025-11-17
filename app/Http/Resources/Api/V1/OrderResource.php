<?php
namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'total_amount' => (string)$this->total_amount,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'items' => $this->items->map(function($item){
                return [
                    'id' => $item->id,
                    'variant_id' => $item->variant_id,
                    'product_snapshot' => $item->product_snapshot,
                    'quantity' => (int)$item->quantity,
                    'unit_price' => (string)$item->unit_price,
                    'line_total' => (string)$item->line_total,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
