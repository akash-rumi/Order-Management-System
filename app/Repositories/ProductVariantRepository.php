<?php

namespace App\Repositories;

use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class ProductVariantRepository
{
    /**
     * Get all variants for a specific product
     */
    public function getByProductId(int $productId)
    {
        return ProductVariant::with('inventory')
            ->where('product_id', $productId)
            ->get();
    }

    /**
     * Get a single variant by id
     */
    public function find(int $id): ?ProductVariant
    {
        return ProductVariant::with('inventory')->find($id);
    }

    /**
     * Create a product variant
     */
    public function create(array $data): ProductVariant
    {
        return ProductVariant::create($data);
    }

    /**
     * Update a product variant
     */
    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);
        return $variant;
    }

    /**
     * Delete a variant
     */
    public function delete(ProductVariant $variant): void
    {
        $variant->delete();
    }

    /**
     * Create inventory row for variant
     */
    public function createInventory(int $variantId, array $options = [])
    {
        return Inventory::create([
            'variant_id' => $variantId,
            'available' => $options['available'] ?? 0,
            'reserved' => 0,
            'low_stock_threshold' => $options['low_stock_threshold'] ?? 5,
        ]);
    }

    /**
     * Get inventory row for a variant
     */
    public function getInventory(int $variantId): ?Inventory
    {
        return Inventory::where('variant_id', $variantId)->first();
    }

    /**
     * Update inventory row
     */
    public function updateInventory(Inventory $inventory, array $options)
    {
        $inventory->update([
            'available' => $options['available'] ?? $inventory->available,
            'reserved' => $inventory->reserved,
            'low_stock_threshold' => $options['low_stock_threshold'] ?? $inventory->low_stock_threshold,
        ]);

        return $inventory;
    }

    /**
     * Delete inventory when variant is deleted
     */
    public function deleteInventory(int $variantId): void
    {
        Inventory::where('variant_id', $variantId)->delete();
    }
}
