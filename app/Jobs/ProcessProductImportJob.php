<?php
namespace App\Jobs;

use App\Models\ProductImport;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\Reader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProcessProductImportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    protected int $importId;

    public function __construct(int $importId)
    {
        $this->importId = $importId;
    }

    public function handle(ProductService $productService)
    {
        $import = ProductImport::find($this->importId);
        if (!$import) return;

        $import->status = 'processing';
        $import->save();

        $path = $import->file_path;
        $fullPath = Storage::path($path);

        // Use native fgetcsv or league/csv if installed. Use fgetcsv to avoid extra dependency.
        if (!file_exists($fullPath)) {
            $import->status = 'failed';
            $import->error_log = 'File not found';
            $import->save();
            return;
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            $import->status = 'failed';
            $import->error_log = 'Unable to open file';
            $import->save();
            return;
        }

        $header = null;
        $rows = [];
        $processed = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (!$header) {
                $header = array_map('trim', $row);
                continue;
            }
            $assoc = [];
            foreach ($header as $i => $col) {
                $assoc[$col] = $row[$i] ?? null;
            }
            $rows[] = $assoc;
        }
        fclose($handle);

        $import->rows_total = count($rows);
        $import->save();

        // Expected CSV columns (simple format):
        // name,slug,description,variant_sku,variant_price,variant_sale_price,variant_attributes_json,initial_stock,low_stock_threshold,vendor_email
        foreach ($rows as $i => $r) {
            try {
                $vendorId = null;
                if (!empty($r['vendor_email'])) {
                    $vendor = \App\Models\User::where('email', $r['vendor_email'])->first();
                    if ($vendor) $vendorId = $vendor->id;
                }

                $productData = [
                    'name' => $r['name'] ?? 'Imported product ' . ($i+1),
                    'slug' => $r['slug'] ?? null,
                    'description' => $r['description'] ?? null,
                    'is_active' => true,
                    'variants' => [
                        [
                            'sku' => $r['variant_sku'] ?? Str::upper('IMP-'.Str::random(6)),
                            'price' => (float)($r['variant_price'] ?? 0),
                            'sale_price' => isset($r['variant_sale_price']) ? (float)$r['variant_sale_price'] : null,
                            'attributes' => !empty($r['variant_attributes_json']) ? json_decode($r['variant_attributes_json'], true) : null,
                            'initial_stock' => isset($r['initial_stock']) ? (int)$r['initial_stock'] : 0,
                            'low_stock_threshold' => isset($r['low_stock_threshold']) ? (int)$r['low_stock_threshold'] : 5,
                        ]
                    ]
                ];

                // create product via service (handles variants/inventory)
                $productService->create($productData, $vendorId);

                $processed++;
                $import->rows_processed = $processed;
                $import->save();
            } catch (\Throwable $e) {
                $errors[] = "Row " . ($i+1) . " failed: " . $e->getMessage();
            }
        }

        $import->status = empty($errors) ? 'completed' : 'failed';
        $import->error_log = empty($errors) ? null : implode("\n", $errors);
        $import->save();
    }
}
