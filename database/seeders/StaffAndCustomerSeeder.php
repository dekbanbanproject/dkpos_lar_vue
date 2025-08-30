<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;

class StaffAndCustomerSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password123'), 'role' => 'admin', 'is_active' => true]
        );

        Customer::updateOrCreate(
            ['email' => 'c1@example.com'],
            ['name' => 'คุณลูกค้า', 'phone' => '0800000000', 'password' => Hash::make('123456')]
        );
    }
}
