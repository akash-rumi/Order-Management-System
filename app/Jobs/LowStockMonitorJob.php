<?php

namespace App\Jobs;

use App\Models\Inventory;
use App\Models\LowStockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LowStockMonitorJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // find inventories where available <= low_stock_threshold
        Inventory::whereColumn('available', '<=', 'low_stock_threshold')
            ->cursor()
            ->each(function ($inventory) {
                try {
                    // don't attempt DB expressions in updateOrCreate; instead check for an alert today
                    $today = now()->toDateString();

                    $exists = LowStockAlert::where('variant_id', $inventory->variant_id)
                        ->whereDate('notified_at', $today)
                        ->exists();

                    if (! $exists) {
                        LowStockAlert::create([
                            'variant_id' => $inventory->variant_id,
                            'inventory_before' => $inventory->available,
                            'inventory_after'  => $inventory->available,
                            'notified_at'      => now(),
                            'notified_date' => now()->toDateString(),
                            'notified_to'      => null,
                        ]);

                        // Optionally: dispatch notification (email/Slack) here
                    }
                } catch (\Throwable $e) {
                    // If this is a duplicate-key race, swallow it; otherwise log
                    if (! str_contains($e->getMessage(), 'Duplicate entry')) {
                        Log::error('LowStockMonitorJob failed', [
                            'variant_id' => $inventory->variant_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }
}
