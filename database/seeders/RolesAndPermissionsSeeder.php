<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Products
            'view products',
            'create products',
            'edit products',
            'delete products',
            
            // Categories
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // Orders
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            'manage order status',
            
            // Users
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Roles & Permissions
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'edit permissions',
            
            // Influencers
            'view influencers',
            'manage influencer applications',
            'edit influencers',
            'view commissions',
            'manage payouts',
            
            // Discount Codes
            'view discount codes',
            'create discount codes',
            'edit discount codes',
            'delete discount codes',
            
            // Content
            'manage content',
            'manage blog',
            'manage pages',
            
            // Settings
            'view settings',
            'edit settings',
            
            // Reports
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions

        // Create roles and assign permissions

        // Super Admin - all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin - most permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'view products', 'create products', 'edit products', 'delete products',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view orders', 'create orders', 'edit orders', 'manage order status',
            'view users', 'create users', 'edit users',
            'view influencers', 'manage influencer applications', 'edit influencers',
            'view commissions', 'manage payouts',
            'view discount codes', 'create discount codes', 'edit discount codes',
            'manage content', 'manage blog', 'manage pages',
            'view reports',
        ]);

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view products', 'create products', 'edit products',
            'view categories', 'create categories', 'edit categories',
            'view orders', 'edit orders', 'manage order status',
            'view users', 'edit users',
            'view reports',
        ]);

        // Sales
        $sales = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $sales->syncPermissions([
            'view products',
            'view orders', 'manage order status',
        ]);

        // Accountant
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'view orders',
            'view commissions',
            'manage payouts',
            'view reports',
        ]);

        // Content Manager
        $contentManager = Role::firstOrCreate(['name' => 'content-manager', 'guard_name' => 'web']);
        $contentManager->syncPermissions([
            'view products', 'create products', 'edit products',
            'manage content',
            'manage blog',
            'manage pages',
        ]);

        // Customer - basic customer role (no admin permissions)
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }
}
