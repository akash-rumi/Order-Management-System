<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductVariantRequest;
use App\Http\Requests\Api\V1\UpdateProductVariantRequest;
use App\Http\Resources\Api\V1\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    protected ProductVariantService $service;

    public function __construct(ProductVariantService $service)
    {
        $this->service = $service;
    }

    // GET /products/{product}/variants
    public function index(Product $product)
    {
        $variants = $product->variants()->with('inventory')->get();
        return ProductVariantResource::collection($variants);
    }

    // GET /variants/{variant}
    public function show(ProductVariant $variant)
    {
        $variant->load('inventory');
        return new ProductVariantResource($variant);
    }

    // POST /products/{product}/variants
    public function store(StoreProductVariantRequest $request, Product $product)
    {
        $payload = $request->validated();
        $variant = $this->service->createVariant($product->id, $payload);
        return (new ProductVariantResource($variant))->response()->setStatusCode(201);
    }

    // PUT /variants/{variant}
    public function update(UpdateProductVariantRequest $request, ProductVariant $variant)
    {
        $payload = $request->validated();
        $variant = $this->service->updateVariant($variant, $payload);
        return new ProductVariantResource($variant);
    }

    // DELETE /variants/{variant}
    public function destroy(Request $request, ProductVariant $variant)
    {
        // vendor ownership check (already in request authorize for store/update; do check again)
        $user = $request->user();
        if ($user->hasRole('vendor') && $variant->product->vendor_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->service->deleteVariant($variant);
        return response()->json(['message' => 'Variant deleted']);
    }
}
