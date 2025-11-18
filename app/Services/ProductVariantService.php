<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\ProductVariantRepository;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class ProductVariantService
{
    protected $repo;

    public function __construct(ProductVariantRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Create a variant and inventory
     */
    public function createVariant(int $productId, array $data): ProductVariant
    {
        return DB::transaction(function () use ($productId, $data) {

            // 1. Create the variant
            $variant = $this->repo->create([
                'product_id'   => $productId,
                'sku'          => $data['sku'],
                'price'        => $data['price'],
                'sale_price'   => $data['sale_price'] ?? null,
                'attributes'   => $data['attributes'] ?? null,
            ]);

            // 2. Create the inventory
            $this->repo->createInventory($variant->id, [
                'available' => $data['initial_stock'] ?? 0,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
            ]);

            return $variant->fresh('inventory');
        });
    }

    /**
     * Update variant & inventory
     */
    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        return DB::transaction(function () use ($variant, $data) {

            // Update variant first
            $this->repo->update($variant, [
                'sku'        => $data['sku'] ?? $variant->sku,
                'price'      => $data['price'] ?? $variant->price,
                'sale_price' => array_key_exists('sale_price', $data)
                                ? $data['sale_price']
                                : $variant->sale_price,
                'attributes' => $data['attributes'] ?? $variant->attributes,
            ]);

            // Update inventory
            $inventory = $this->repo->getInventory($variant->id);

            if ($inventory) {
                $this->repo->updateInventory($inventory, [
                    'available' => $data['initial_stock'] ?? $inventory->available,
                    'low_stock_threshold' => $data['low_stock_threshold'] ?? $inventory->low_stock_threshold,
                ]);
            }

            return $variant->fresh('inventory');
        });
    }

    /**
     * Delete variant + inventory
     */
    public function deleteVariant(ProductVariant $variant): void
    {
        DB::transaction(function () use ($variant) {
            $this->repo->deleteInventory($variant->id);
            $this->repo->delete($variant);
        });
    }
}
