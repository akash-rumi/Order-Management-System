<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;

class InventorySeeder extends Seeder
{
    public function run()
    {
        // Inventory for T-shirt variants
        Inventory::create([
            'variant_id' => 1,
            'available' => 20,
            'reserved' => 0,
            'low_stock_threshold' => 5,
        ]);

        Inventory::create([
            'variant_id' => 2,
            'available' => 15,
            'reserved' => 0,
            'low_stock_threshold' => 5,
        ]);

        // Jeans variant
        Inventory::create([
            'variant_id' => 3,
            'available' => 10,
            'reserved' => 0,
            'low_stock_threshold' => 3,
        ]);
    }
}
