<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add customer_id columns alongside existing user_id columns
        // We keep user_id temporarily for data integrity during migration

        // 1. Orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
        });
        DB::statement('UPDATE orders SET customer_id = user_id WHERE user_id IN (SELECT id FROM customers)');

        // 2. Carts table
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
        });
        DB::statement('UPDATE carts SET customer_id = user_id WHERE user_id IN (SELECT id FROM customers)');

        // 3. Wishlists table
        Schema::table('wishlists', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
        });
        DB::statement('UPDATE wishlists SET customer_id = user_id WHERE user_id IN (SELECT id FROM customers)');

        // 4. Product Reviews table
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
        });
        DB::statement('UPDATE product_reviews SET customer_id = user_id WHERE user_id IN (SELECT id FROM customers)');

        // 5. Shipping Addresses table
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
        });
        DB::statement('UPDATE shipping_addresses SET customer_id = user_id WHERE user_id IN (SELECT id FROM customers)');

        // 6. Product Views table
        if (Schema::hasTable('product_views') && Schema::hasColumn('product_views', 'user_id')) {
            Schema::table('product_views', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            });
            DB::statement('UPDATE product_views SET customer_id = user_id WHERE user_id IN (SELECT id FROM customers)');
        }

        // 7. Code Usages table (influencer codes)
        if (Schema::hasTable('code_usages') && Schema::hasColumn('code_usages', 'user_id')) {
            Schema::table('code_usages', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            });
            DB::statement('UPDATE code_usages SET customer_id = user_id WHERE user_id IN (SELECT id FROM customers)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove customer_id columns
        $tables = ['orders', 'carts', 'wishlists', 'product_reviews', 'shipping_addresses', 'product_views', 'code_usages'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'customer_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['customer_id']);
                    $table->dropColumn('customer_id');
                });
            }
        }
    }
};
