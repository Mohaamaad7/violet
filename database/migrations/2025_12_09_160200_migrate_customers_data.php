<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Copy customers from users table to customers table
        DB::statement("
            INSERT INTO customers (id, name, email, email_verified_at, password, phone, profile_photo_path, status, locale, remember_token, created_at, updated_at, deleted_at)
            SELECT id, name, email, email_verified_at, password, phone, profile_photo_path, 
                   CASE WHEN status = 'suspended' THEN 'blocked' ELSE status END,
                   COALESCE(locale, 'ar'), remember_token, created_at, updated_at, deleted_at
            FROM users 
            WHERE type = 'customer'
        ");

        // Step 2: Calculate total_orders and total_spent for each customer
        DB::statement("
            UPDATE customers c
            SET total_orders = (
                SELECT COUNT(*) FROM orders WHERE user_id = c.id
            ),
            total_spent = (
                SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = c.id AND payment_status = 'paid'
            ),
            last_order_at = (
                SELECT MAX(created_at) FROM orders WHERE user_id = c.id
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Truncate customers table (data will be restored from users backup)
        DB::table('customers')->truncate();
    }
};
