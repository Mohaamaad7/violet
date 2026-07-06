<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM(
            'card',
            'vodafone_cash',
            'orange_money',
            'etisalat_cash',
            'meeza',
            'valu',
            'souhoola',
            'sympl',
            'wallet',
            'kiosk',
            'instapay'
        )");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_method ENUM(
            'card',
            'vodafone_cash',
            'orange_money',
            'etisalat_cash',
            'meeza',
            'valu',
            'souhoola',
            'sympl'
        )");
    }
};
