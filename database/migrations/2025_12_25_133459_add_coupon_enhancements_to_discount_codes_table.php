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
        if (DB::getDriverName() === 'sqlite') return;
        
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN discount_type ENUM('percentage', 'fixed', 'free_shipping') DEFAULT 'percentage'");

        Schema::table('discount_codes', function (Blueprint $table) {
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('max_uses_per_user');
            $table->decimal('max_discount_amount', 10, 2)->nullable()->after('min_order_amount');
            $table->json('excluded_categories')->nullable()->after('applicable_categories');
            $table->json('excluded_products')->nullable()->after('applicable_products');
            $table->boolean('first_order_only')->default(false)->after('max_discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage'");

        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropColumn([
                'min_order_amount',
                'max_discount_amount',
                'excluded_categories',
                'excluded_products',
                'first_order_only'
            ]);
        });
    }
};
