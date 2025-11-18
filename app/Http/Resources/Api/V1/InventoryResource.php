<?php
namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'variant_id' => $this->variant_id,
            'available' => (int)$this->available,
            'reserved' => (int)$this->reserved,
            'low_stock_threshold' => (int)$this->low_stock_threshold,
            'updated_at' => $this->updated_at,
        ];
    }
}
