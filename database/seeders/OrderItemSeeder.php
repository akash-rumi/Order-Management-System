<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderItem;

class OrderItemSeeder extends Seeder
{
    public function run()
    {
        // Order 1: buy 2 × T-Shirt variant 1
        OrderItem::create([
            'order_id' => 1,
            'variant_id' => 1,
            'product_snapshot' => [
                'product_id' => 1,
                'product_name' => 'Basic T-Shirt',
                'sku' => 'TSHIRT-BLK-M'
            ],
            'quantity' => 2,
            'unit_price' => 500,
            'line_total' => 1000
        ]);
    }
}
