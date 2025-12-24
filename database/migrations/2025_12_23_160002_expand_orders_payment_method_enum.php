<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⚠️ ROLLBACK WARNING:
 * If you have orders with new payment methods (e.g., valu, vodafone_cash)
 * and run migrate:rollback, it will FAIL because those values won't exist
 * in the old enum. Clean the data first or use a different strategy.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cod',
                'card',
                'vodafone_cash',
                'orange_money',
                'etisalat_cash',
                'meeza',
                'valu',
                'souhoola',
                'sympl'
            ])->default('cod')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cod', 'card', 'instapay'])
                ->default('cod')->change();
        });
    }
};
