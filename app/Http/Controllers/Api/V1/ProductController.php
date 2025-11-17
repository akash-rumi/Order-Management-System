<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
        // Optionally limit index/show to public, others are protected by routes
        $this->middleware('auth:api')->only(['store','update','destroy']);
    }

    public function index(Request $request)
    {
        $perPage = (int)$request->get('per_page', 15);
        $filters = [
            'search' => $request->get('search'),
            'vendor_id' => $request->get('vendor_id'),
        ];

        $products = $this->service->list($filters, $perPage);

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $product = $this->service->get($product->id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request)
    {
        $user = $request->user();
        $vendorId = $user->hasRole('vendor') ? $user->id : ($request->get('vendor_id') ?? null);

        $product = $this->service->create($request->validated(), $vendorId);

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        // ensure vendor can only update own products
        $user = $request->user();
        if ($user->hasRole('vendor') && $product->vendor_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product = $this->service->update($product, $request->validated());

        return new ProductResource($product);
    }

    public function destroy(Request $request, Product $product)
    {
        $user = $request->user();
        if ($user->hasRole('vendor') && $product->vendor_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->service->delete($product);

        return response()->json(['message' => 'Product deleted']);
    }
}
