<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\ProductVariantRepository;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class ProductVariantService
{
    public function __construct(
        private ProductVariantRepository $repository
    ) {}

    public function store(Request $request, Product $product): ProductVariant
    {
        $this->authorizeOwnership($request->user(), $product);

        $data = $request->only(['sku', 'price', 'sale_price', 'attributes']);
        $variant = $this->repository->create($data, $product->id);

        $inventoryData = [
            'available' => (int) $request->input('initial_stock', 0),
            'low_stock_threshold' => (int) $request->input('low_stock_threshold', 5),
        ];
        $this->repository->createInventory($variant->id, $inventoryData);

        return $variant;
    }

    public function update(Request $request, ProductVariant $variant): ProductVariant
    {
        $product = $variant->product;
        $this->authorizeOwnership($request->user(), $product);

        $data = $request->only(['sku', 'price', 'sale_price', 'attributes']);
        $this->repository->update($variant, $data);

        return $variant->fresh();
    }

    public function destroy(Request $request, ProductVariant $variant): void
    {
        $product = $variant->product;
        $this->authorizeOwnership($request->user(), $product);

        $this->repository->delete($variant);
    }

    private function authorizeOwnership($user, Product $product): void
    {
        if ($user->hasRole('vendor') && $product->vendor_id !== $user->id) {
            throw new AuthorizationException('Forbidden: Unauthorized to manage this product.');
        }
    }
}