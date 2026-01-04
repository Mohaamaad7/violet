<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert facebook_pixel_id setting
        DB::table('settings')->insert([
            'key' => 'facebook_pixel_id',
            'value' => null,
            'type' => 'string',
            'group' => 'tracking',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'facebook_pixel_id')->delete();
    }
};
