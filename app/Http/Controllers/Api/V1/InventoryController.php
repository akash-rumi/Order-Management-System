<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdjustInventoryRequest;
use App\Http\Resources\Api\V1\InventoryResource;
use App\Models\ProductVariant;
use App\Repositories\InventoryRepository;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    protected InventoryService $service;
    protected InventoryRepository $repo;

    public function __construct(InventoryService $service, InventoryRepository $repo)
    {
        $this->service = $service;
        $this->repo = $repo;
    }

    // GET /api/v1/inventories/{variant}
    public function show(ProductVariant $variant)
    {
        $inventory = $this->repo->findByVariantId($variant->id);

        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        return new InventoryResource($inventory);
    }

    // POST /api/v1/inventories/{variant}/adjust
    public function adjust(AdjustInventoryRequest $request, ProductVariant $variant)
    {
        $data = $request->validated();
        $delta = (int)$data['delta'];
        $reason = $data['reason'] ?? null;
        $newThreshold = $data['low_stock_threshold'] ?? null;

        try {
            $inventory = $this->service->adjustByVariant($variant->id, $delta, $reason, $newThreshold);
            return new InventoryResource($inventory);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unable to adjust inventory', 'error' => $e->getMessage()], 500);
        }
    }
}
