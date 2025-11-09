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
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - all permissions
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - most permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
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
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view products', 'create products', 'edit products',
            'view categories', 'create categories', 'edit categories',
            'view orders', 'edit orders', 'manage order status',
            'view users', 'edit users',
            'view reports',
        ]);

        // Sales
        $sales = Role::create(['name' => 'sales']);
        $sales->givePermissionTo([
            'view products',
            'view orders', 'manage order status',
        ]);

        // Accountant
        $accountant = Role::create(['name' => 'accountant']);
        $accountant->givePermissionTo([
            'view orders',
            'view commissions',
            'manage payouts',
            'view reports',
        ]);

        // Content Manager
        $contentManager = Role::create(['name' => 'content-manager']);
        $contentManager->givePermissionTo([
            'view products', 'create products', 'edit products',
            'manage content',
            'manage blog',
            'manage pages',
        ]);
    }
}
