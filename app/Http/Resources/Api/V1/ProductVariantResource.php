<?php
namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray($request)
    {
        $inventory = $this->inventory;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'price' => (string)$this->price,
            'sale_price' => $this->sale_price !== null ? (string)$this->sale_price : null,
            'attributes' => $this->attributes,
            'inventory' => $inventory ? [
                'available' => (int)$inventory->available,
                'reserved' => (int)$inventory->reserved,
                'low_stock_threshold' => (int)$inventory->low_stock_threshold,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
