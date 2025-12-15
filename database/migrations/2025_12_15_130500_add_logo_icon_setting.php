<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert logo_icon setting
        DB::table('settings')->insert([
            'key' => 'logo_icon',
            'value' => 'images/logo.png',
            'type' => 'image',
            'group' => 'general',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'logo_icon')->delete();
    }
};
