<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Adds enhancements for the coupon module:
     * - free_shipping discount type
     * - exclude_products/categories for partial apply
     * - internal_notes for admin documentation
     */
    public function up(): void
    {
        // Step 1: Modify discount_type enum to include 'free_shipping'
        // MySQL requires raw SQL to alter ENUM columns
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN discount_type ENUM('percentage', 'fixed', 'free_shipping') DEFAULT 'percentage'");

        // Step 2: Add new columns
        Schema::table('discount_codes', function (Blueprint $table) {
            // Exclusion lists for partial apply logic
            $table->json('exclude_products')->nullable()->after('applies_to_products');
            $table->json('exclude_categories')->nullable()->after('exclude_products');

            // Internal notes for admin documentation
            $table->text('internal_notes')->nullable()->after('exclude_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Remove added columns
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropColumn(['exclude_products', 'exclude_categories', 'internal_notes']);
        });

        // Step 2: Revert enum to original values
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage'");
    }
};
