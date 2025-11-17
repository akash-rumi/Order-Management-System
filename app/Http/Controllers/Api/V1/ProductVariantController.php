<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class ProductVariantController extends Controller
{
    public function __construct(
        private ProductVariantService $service
    ) {}

    public function store(Request $request, Product $product)
    {
        try {
            $request->validate([
                'sku' => 'required|string|unique:product_variants,sku',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|lte:price',
                'attributes' => 'nullable|array',
                'initial_stock' => 'sometimes|integer|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $variant = $this->service->store($request, $product);
            return response()->json(['variant' => $variant], 201);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create variant'], 500);
        }
    }

    public function update(Request $request, ProductVariant $variant)
    {
        try {
            $request->validate([
                'sku' => 'sometimes|string|unique:product_variants,sku,' . $variant->id,
                'price' => 'sometimes|numeric|min:0',
                'sale_price' => 'nullable|numeric|lte:price',
                'attributes' => 'nullable|array',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $this->service->update($request, $variant);
            return response()->json(['variant' => $variant->fresh()]);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update variant'], 500);
        }
    }

    public function destroy(Request $request, ProductVariant $variant)
    {
        try {
            $this->service->destroy($request, $variant);
            return response()->json(['message' => 'Variant deleted']);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete variant'], 500);
        }
    }
}