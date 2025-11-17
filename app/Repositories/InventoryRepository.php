<?php
namespace App\Repositories;

use App\Models\Inventory;

class InventoryRepository
{
    public function findByVariantIdForUpdate(int $variantId)
    {
        return Inventory::where('variant_id', $variantId)->lockForUpdate()->first();
    }

    public function findByVariantId(int $variantId)
    {
        return Inventory::where('variant_id', $variantId)->first();
    }

    public function create(array $data): Inventory
    {
        return Inventory::create($data);
    }

    public function save(Inventory $inventory): Inventory
    {
        $inventory->save();
        return $inventory;
    }
}
