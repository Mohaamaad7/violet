<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill orders with user_id based on matching guest_email or guest_phone
     * 
     * This fixes orders that were placed by authenticated users but got
     * guest data due to a checkout flow bug.
     */
    public function up(): void
    {
        // Link orders to users by matching guest_email
        DB::statement('
            UPDATE orders o
            JOIN users u ON o.guest_email = u.email
            SET o.user_id = u.id
            WHERE o.user_id IS NULL
            AND o.guest_email IS NOT NULL
        ');

        // Also try matching by phone if user has phone set
        DB::statement('
            UPDATE orders o
            JOIN users u ON o.guest_phone = u.phone
            SET o.user_id = u.id
            WHERE o.user_id IS NULL
            AND o.guest_phone IS NOT NULL
            AND u.phone IS NOT NULL
            AND u.phone != ""
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reliably reverse this - would need to track which orders were updated
    }
};
