<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // One simple order by Customer (user_id = 3)
        Order::create([
            'order_number' => 'ORD-1001',
            'user_id' => 3,
            'status' => 'pending',
            'payment_status' => 'pending',
            'total_amount' => 1000,
            'shipping_address' => [
                'name' => 'Customer One',
                'line1' => '123 Test Street',
                'city' => 'Dhaka',
                'country' => 'Bangladesh'
            ],
            'billing_address' => null,
            'metadata' => null,
        ]);
    }
}
