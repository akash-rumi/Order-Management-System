<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Simple product created by Vendor (vendor_id = 2)
        Product::create([
            'vendor_id' => 2,
            'name' => 'Basic T-Shirt',
            'slug' => 'basic-tshirt',
            'description' => 'Simple basic cotton t-shirt.',
            'is_active' => true
        ]);

        Product::create([
            'vendor_id' => 2,
            'name' => 'Blue Jeans',
            'slug' => 'blue-jeans',
            'description' => 'Comfortable denim jeans.',
            'is_active' => true
        ]);
    }
}
