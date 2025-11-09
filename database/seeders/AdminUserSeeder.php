<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@violet.com',
            'password' => Hash::make('password'),
            'phone' => '01000000000',
            'type' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole('super-admin');

        // Create Sample Manager
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@violet.com',
            'password' => Hash::make('password'),
            'phone' => '01000000001',
            'type' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $manager->assignRole('manager');

        // Create Sample Customer
        $customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@violet.com',
            'password' => Hash::make('password'),
            'phone' => '01000000002',
            'type' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Users created successfully!');
        $this->command->info('Super Admin: admin@violet.com / password');
        $this->command->info('Manager: manager@violet.com / password');
        $this->command->info('Customer: customer@violet.com / password');
    }
}
