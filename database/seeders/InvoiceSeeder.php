<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;

class InvoiceSeeder extends Seeder
{
    public function run()
    {
        Invoice::create([
            'order_id' => 1,
            'pdf_path' => null,   // You can generate PDF later
            'status' => 'created'
        ]);
    }
}
