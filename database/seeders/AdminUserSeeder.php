<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get credentials from .env or fallback to defaults
        $email = env('SUPER_ADMIN_EMAIL', 'admin@violet.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'password');

        // Create Super Admin
        $superAdmin = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'phone' => '01000000000',
            'type' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole('super-admin');

        $this->command->info('Super Admin created successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Email: ' . $email);
        $this->command->info('Password: ' . $password);
        $this->command->info('Please change the password after first login.');
    }
}
