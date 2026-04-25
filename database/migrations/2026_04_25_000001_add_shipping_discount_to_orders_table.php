<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add shipping_discount_amount to orders table.
     * Stores the automatic shipping discount separately from coupon discounts
     * to maintain accurate financial reporting.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_discount_amount', 10, 2)
                  ->default(0)
                  ->after('shipping_cost')
                  ->comment('خصم الشحن التلقائي (منفصل عن discount_amount الخاص بالكوبون)');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_discount_amount');
        });
    }
};
