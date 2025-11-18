<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected ProductRepository $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function get(int $id): ?Product
    {
        return $this->repo->findByIdWithRelations($id);
    }

    /**
     * Create product with optional variants and inventory.
     *
     * @param array $data
     * @param int|null $vendorId
     * @return Product
     */
    public function create(array $data, ?int $vendorId = null): Product
    {
        return DB::transaction(function () use ($data, $vendorId) {
            $productData = [
                'vendor_id' => $vendorId,
                'name' => $data['name'],
                'slug' => $data['slug'] ?? \Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            $product = $this->repo->create($productData);

            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $v) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $v['sku'],
                        'price' => $v['price'],
                        'sale_price' => $v['sale_price'] ?? null,
                        'attributes' => $v['attributes'] ?? null,
                    ]);

                    // create inventory for the variant
                    $initialStock = isset($v['initial_stock']) ? (int)$v['initial_stock'] : 0;
                    Inventory::create([
                        'variant_id' => $variant->id,
                        'available' => $initialStock,
                        'reserved' => 0,
                        'low_stock_threshold' => $v['low_stock_threshold'] ?? 5,
                    ]);
                }
            }

            return $product->fresh('variants.inventory');
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $updateData = array_filter([
                'name' => $data['name'] ?? null,
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? null,
            ], fn($v) => !is_null($v));

            $this->repo->update($product, $updateData);

            // handle variants (create new or update existing)
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $v) {
                    if (!empty($v['id'])) {
                        // update
                        $variant = ProductVariant::where('id', $v['id'])
                            ->where('product_id', $product->id)
                            ->first();

                        if ($variant) {
                            $variant->update([
                                'sku' => $v['sku'] ?? $variant->sku,
                                'price' => $v['price'] ?? $variant->price,
                                'sale_price' => $v['sale_price'] ?? $variant->sale_price,
                                'attributes' => $v['attributes'] ?? $variant->attributes,
                            ]);
                        }
                    } else {
                        // create
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $v['sku'],
                            'price' => $v['price'],
                            'sale_price' => $v['sale_price'] ?? null,
                            'attributes' => $v['attributes'] ?? null,
                        ]);

                        Inventory::create([
                            'variant_id' => $variant->id,
                            'available' => isset($v['initial_stock']) ? (int)$v['initial_stock'] : 0,
                            'reserved' => 0,
                            'low_stock_threshold' => $v['low_stock_threshold'] ?? 5,
                        ]);
                    }
                }
            }

            return $product->fresh('variants.inventory');
        });
    }

    public function delete(Product $product): void
    {
        $this->repo->delete($product);
    }
}
