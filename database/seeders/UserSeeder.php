<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        // Vendor
        User::create([
            'name' => 'Vendor One',
            'email' => 'vendor1@test.com',
            'password' => Hash::make('password'),
            'role' => 'vendor'
        ]);

        // Customer
        User::create([
            'name' => 'Customer One',
            'email' => 'customer1@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer'
        ]);
    }
}
