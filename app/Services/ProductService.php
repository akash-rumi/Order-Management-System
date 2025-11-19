<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected ProductRepository $repo;
    protected ProductVariantRepository $variantRepo;
    protected ProductVariantService $variantService;

    public function __construct(ProductRepository $repo, ProductVariantRepository $variantRepo, ProductVariantService $variantService)
    {
        $this->repo = $repo;
        $this->variantRepo = $variantRepo;
        $this->variantService = $variantService;
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
                'slug' => $data['slug'] ?? Str::slug($data['name']) . '-' . Str::random(4),
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            $product = $this->repo->create($productData);

            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $v) {
                    $this->variantService->createVariant($product->id,[
                        'sku' => $v['sku'],
                        'price' => $v['price'],
                        'sale_price' => $v['sale_price'] ?? null,
                        'attributes' => $v['attributes'] ?? null,
                        'initial_stock' => $v['initial_stock'] ??  0,
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
                'is_active' => array_key_exists('is_active', $data) ? $data['is_active'] : null,
            ], fn($v) => !is_null($v));

            if (!empty($updateData)) {
                $this->repo->update($product, $updateData);
            }

            // handle variants (create new or update existing)
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $v) {
                    if (!empty($v['id'])) {
                        // update
                        $variant = $this->variantRepo->find($v['id']);
                        if ($variant && $variant->product_id == $product->id) {
                            $this->variantService->updateVariant($variant, $v);
                        } else {
                            $this->variantService->createVariant($product->id, $v);
                        }
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
