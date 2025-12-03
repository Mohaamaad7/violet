<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix shipping_addresses schema for user saved addresses
     * 
     * Issues fixed:
     * 1. email column was NOT NULL - but saved addresses created before order don't have email
     * 2. order_id had UNIQUE constraint - but multiple saved addresses can have NULL order_id
     */
    public function up(): void
    {
        // Make email nullable - saved user addresses may not require email
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Drop unique constraint on order_id if it exists
        // Using raw SQL since Laravel 12 doesn't have Doctrine
        try {
            DB::statement('ALTER TABLE shipping_addresses DROP INDEX shipping_addresses_order_id_unique');
        } catch (\Exception $e) {
            // Unique constraint doesn't exist, that's OK
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
        
        // Re-add unique if needed (only if no duplicates)
        try {
            DB::statement('ALTER TABLE shipping_addresses ADD UNIQUE INDEX shipping_addresses_order_id_unique (order_id)');
        } catch (\Exception $e) {
            // Can't add unique due to duplicates
        }
    }
};
