<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]); 
        $adminUser->assignRole('admin'); 
        
        // Vendor
        $vendorUser = User::create([
            'name' => 'Vendor One',
            'email' => 'vendor1@test.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);
        $vendorUser->assignRole('vendor');

        // Customer
        $customerUser = User::create([
            'name' => 'Customer One',
            'email' => 'customer1@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);
        $customerUser->assignRole('customer');
    }
}
