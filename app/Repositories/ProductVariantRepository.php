<?php

namespace App\Repositories;

use App\Models\ProductVariant;
use App\Models\Inventory;

class ProductVariantRepository
{
    public function create(array $data, int $productId): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $productId,
            'sku' => $data['sku'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'attributes' => $data['attributes'] ?? null,
        ]);
    }

    public function update(ProductVariant $variant, array $data): bool
    {
        return $variant->update($data);
    }

    public function delete(ProductVariant $variant): bool
    {
        return $variant->delete();
    }

    public function createInventory(int $variantId, array $inventoryData): Inventory
    {
        return Inventory::create([
            'variant_id' => $variantId,
            'available' => $inventoryData['available'] ?? 0,
            'reserved' => 0,
            'low_stock_threshold' => $inventoryData['low_stock_threshold'] ?? 5,
        ]);
    }
}