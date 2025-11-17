<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $inventory = $this->inventory;

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
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
