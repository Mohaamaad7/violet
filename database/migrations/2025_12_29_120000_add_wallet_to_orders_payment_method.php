<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add wallet, instapay, and kiosk to orders payment_method enum
 * to support Paymob's additional payment methods
 */
return new class extends Migration {
    public function up(): void
    {
        // Use raw SQL to modify the enum since Laravel's enum change has limitations
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM(
            'cod',
            'card',
            'wallet',
            'vodafone_cash',
            'orange_money',
            'etisalat_cash',
            'instapay',
            'kiosk',
            'meeza',
            'valu',
            'souhoola',
            'sympl'
        ) DEFAULT 'cod'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM(
            'cod',
            'card',
            'vodafone_cash',
            'orange_money',
            'etisalat_cash',
            'meeza',
            'valu',
            'souhoola',
            'sympl'
        ) DEFAULT 'cod'");
    }
};
