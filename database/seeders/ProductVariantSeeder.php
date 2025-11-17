<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductVariant;

class ProductVariantSeeder extends Seeder
{
    public function run()
    {
        // Variants for Basic T-Shirt (product_id = 1)
        ProductVariant::create([
            'product_id' => 1,
            'sku' => 'TSHIRT-BLK-M',
            'price' => 500,
            'sale_price' => null,
            'attributes' => ['size' => 'M', 'color' => 'Black']
        ]);

        ProductVariant::create([
            'product_id' => 1,
            'sku' => 'TSHIRT-WHT-L',
            'price' => 550,
            'sale_price' => 500,
            'attributes' => ['size' => 'L', 'color' => 'White']
        ]);

        // Variant for Blue Jeans (product_id = 2)
        ProductVariant::create([
            'product_id' => 2,
            'sku' => 'JEANS-BLU-32',
            'price' => 1200,
            'sale_price' => null,
            'attributes' => ['waist' => 32, 'color' => 'Blue']
        ]);
    }
}
