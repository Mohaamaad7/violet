<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set default value for commission_value column
        DB::statement('ALTER TABLE discount_codes ALTER COLUMN commission_value SET DEFAULT 0');

        // Update existing null values
        DB::table('discount_codes')->whereNull('commission_value')->update(['commission_value' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE discount_codes ALTER COLUMN commission_value DROP DEFAULT');
    }
};
