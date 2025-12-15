<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'return' to category enum
        DB::statement("ALTER TABLE `email_templates` MODIFY COLUMN `category` ENUM('order', 'auth', 'notification', 'marketing', 'return') NOT NULL DEFAULT 'notification'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'return' from category enum
        DB::statement("ALTER TABLE `email_templates` MODIFY COLUMN `category` ENUM('order', 'auth', 'notification', 'marketing') NOT NULL DEFAULT 'notification'");
    }
};
