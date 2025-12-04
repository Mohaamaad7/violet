<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * CleanDeploySeeder - Minimal seeder for production clean deployment
 * 
 * This seeder creates ONLY:
 * - Roles & Permissions (required for Spatie)
 * - One Super Admin user
 * 
 * NO business data (products, categories, orders, etc.)
 * 
 * Usage:
 *   php artisan db:seed --class=CleanDeploySeeder --force
 */
class CleanDeploySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Clean Deploy Seeder ===');
        $this->command->info('Creating minimal system data for production...');
        
        // Step 1: Seed Roles and Permissions
        $this->seedRolesAndPermissions();
        
        // Step 2: Create Super Admin
        $this->createSuperAdmin();
        
        // Step 3: Verify clean state
        $this->verifyCleanState();
        
        $this->command->info('');
        $this->command->info('✅ Clean deployment seeding complete!');
        $this->command->warn('⚠️  IMPORTANT: Change the Super Admin password immediately after first login!');
    }

    private function seedRolesAndPermissions(): void
    {
        $this->command->info('');
        $this->command->info('1. Creating roles and permissions...');
        
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
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

        $createdCount = 0;
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $createdCount++;
        }
        $this->command->info("   Created {$createdCount} permissions");

        // Create roles
        $roles = [
            'super-admin' => Permission::all()->pluck('name')->toArray(),
            'admin' => [
                'view products', 'create products', 'edit products', 'delete products',
                'view categories', 'create categories', 'edit categories', 'delete categories',
                'view orders', 'create orders', 'edit orders', 'manage order status',
                'view users', 'create users', 'edit users',
                'view influencers', 'manage influencer applications', 'edit influencers',
                'view commissions', 'manage payouts',
                'view discount codes', 'create discount codes', 'edit discount codes',
                'manage content', 'manage blog', 'manage pages',
                'view reports',
            ],
            'manager' => [
                'view products', 'create products', 'edit products',
                'view categories', 'create categories', 'edit categories',
                'view orders', 'edit orders', 'manage order status',
                'view users', 'edit users',
                'view reports',
            ],
            'sales' => [
                'view products',
                'view orders', 'manage order status',
            ],
            'accountant' => [
                'view orders',
                'view commissions',
                'manage payouts',
                'view reports',
            ],
            'content-manager' => [
                'view products', 'create products', 'edit products',
                'manage content',
                'manage blog',
                'manage pages',
            ],
            'customer' => [],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            if (!empty($rolePermissions)) {
                $role->syncPermissions($rolePermissions);
            }
        }
        $this->command->info('   Created ' . count($roles) . ' roles');
    }

    private function createSuperAdmin(): void
    {
        $this->command->info('');
        $this->command->info('2. Creating Super Admin user...');
        
        $email = 'admin@violet.com';
        
        // Check if already exists
        $existing = User::where('email', $email)->first();
        if ($existing) {
            $this->command->warn("   Super Admin already exists: {$email}");
            $existing->assignRole('super-admin');
            return;
        }
        
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => Hash::make('ChangeThisPassword!'),
            'phone' => null,
            'type' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole('super-admin');
        
        $this->command->info("   ✅ Super Admin created:");
        $this->command->info("      Email:    {$email}");
        $this->command->info("      Password: ChangeThisPassword!");
    }

    private function verifyCleanState(): void
    {
        $this->command->info('');
        $this->command->info('3. Verifying clean state...');
        
        $contentTables = [
            'categories',
            'products',
            'orders',
            'wishlists',
            'sliders',
            'banners',
            'blog_posts',
        ];
        
        $hasData = false;
        foreach ($contentTables as $table) {
            $count = \DB::table($table)->count();
            if ($count > 0) {
                $this->command->error("   ❌ {$table}: {$count} records (should be 0)");
                $hasData = true;
            }
        }
        
        if (!$hasData) {
            $this->command->info('   ✅ All content tables are empty');
        }
        
        // Show system tables
        $this->command->info('');
        $this->command->info('   System tables:');
        $this->command->info('   - Roles: ' . Role::count());
        $this->command->info('   - Permissions: ' . Permission::count());
        $this->command->info('   - Admin Users: ' . User::where('type', 'admin')->count());
    }
}
