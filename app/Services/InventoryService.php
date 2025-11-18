<?php
namespace App\Services;

use App\Models\Inventory;
use App\Models\LowStockAlert;
use App\Repositories\InventoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    protected InventoryRepository $repo;

    public function __construct(InventoryRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Adjust inventory by delta atomically and return the Inventory model.
     * delta can be positive (increase) or negative (decrease).
     *
     * Throws ValidationException if decreasing below zero.
     */
    public function adjustByVariant(int $variantId, int $delta, ?string $reason = null, ?int $newThreshold = null): Inventory
    {
        return DB::transaction(function () use ($variantId, $delta, $reason, $newThreshold) {

            // Lock the inventory row
            $inventory = $this->repo->findByVariantIdForUpdate($variantId);

            if (!$inventory) {
                // Create a record if missing (defensive)
                $inventory = $this->repo->create([
                    'variant_id' => $variantId,
                    'available' => 0,
                    'reserved' => 0,
                    'low_stock_threshold' => $newThreshold ?? 5,
                ]);
                // re-lock newly created row
                $inventory = $this->repo->findByVariantIdForUpdate($variantId);
            }

            $before = (int)$inventory->available;
            $after = $before + $delta;

            if ($after < 0) {
                throw ValidationException::withMessages(['delta' => "Insufficient stock. Available: {$before}, requested change: {$delta}"]);
            }

            $inventory->available = $after;

            if (!is_null($newThreshold)) {
                $inventory->low_stock_threshold = $newThreshold;
            }

            $this->repo->save($inventory);

            // If now below or equal threshold, create a low stock alert record (you may also dispatch a notification job)
            if ($inventory->available <= $inventory->low_stock_threshold) {
                LowStockAlert::create([
                    'variant_id' => $inventory->variant_id,
                    'inventory_before' => $before,
                    'inventory_after' => $after,
                    'notified_to' => null,
                    'notified_at' => now(),
                ]);
                // Optionally: dispatch notification job here
            }

            return $inventory;
        });
    }

    /**
     * Safe decrement only (helper)
     */
    public function decrement(int $variantId, int $qty, ?string $reason = null): Inventory
    {
        return $this->adjustByVariant($variantId, -abs($qty), $reason);
    }

    /**
     * Safe increment only (helper)
     */
    public function increment(int $variantId, int $qty, ?string $reason = null): Inventory
    {
        return $this->adjustByVariant($variantId, abs($qty), $reason);
    }
}
