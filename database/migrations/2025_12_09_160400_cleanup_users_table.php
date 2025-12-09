<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Check if column exists
     */
    private function columnExists(string $tableName, string $columnName): bool
    {
        $database = config('database.connections.mysql.database');
        $result = DB::select("
            SELECT COUNT(*) as cnt 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
        ", [$database, $tableName, $columnName]);

        return $result[0]->cnt > 0;
    }

    /**
     * Safely drop any index/key that uses the column
     */
    private function dropIndexesUsingColumn(string $tableName, string $columnName): void
    {
        $database = config('database.connections.mysql.database');

        // Find all indexes that use this column
        $indexes = DB::select("
            SELECT DISTINCT INDEX_NAME 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
            AND INDEX_NAME != 'PRIMARY'
        ", [$database, $tableName, $columnName]);

        foreach ($indexes as $index) {
            try {
                DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$index->INDEX_NAME}`");
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
        }
    }

    /**
     * Safely drop foreign key if exists
     */
    private function dropForeignKeyIfExists(string $tableName, string $foreignKeyName): void
    {
        try {
            DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$foreignKeyName}`");
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }
    }

    /**
     * Safely drop column with all its dependencies
     */
    private function dropColumnIfExists(string $tableName, string $columnName): void
    {
        if (!$this->columnExists($tableName, $columnName)) {
            return;
        }

        // 1. Drop all indexes that use this column
        $this->dropIndexesUsingColumn($tableName, $columnName);

        // 2. Try to drop foreign key
        $this->dropForeignKeyIfExists($tableName, $tableName . '_' . $columnName . '_foreign');

        // 3. Now drop the column (with try-catch for edge cases)
        try {
            DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `{$columnName}`");
        } catch (\Exception $e) {
            // Column might have been dropped already or doesn't exist
            // Log and continue
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Delete customers from users table and drop type column
        if ($this->columnExists('users', 'type')) {
            DB::statement("DELETE FROM users WHERE type = 'customer'");
            DB::statement("ALTER TABLE `users` DROP COLUMN `type`");
        }

        // Step 2: Drop user_id from tables that now use customer_id
        $this->dropColumnIfExists('carts', 'user_id');
        $this->dropColumnIfExists('wishlists', 'user_id');
        $this->dropColumnIfExists('product_reviews', 'user_id');
        $this->dropColumnIfExists('shipping_addresses', 'user_id');

        // Step 3: Add unique constraint on customer_id + product_id for wishlists
        // (replacing the old user_id + product_id constraint)
        try {
            DB::statement("ALTER TABLE `wishlists` ADD UNIQUE KEY `wishlists_customer_id_product_id_unique` (`customer_id`, `product_id`)");
        } catch (\Exception $e) {
            // Ignore if already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add type column back to users
        if (!$this->columnExists('users', 'type')) {
            DB::statement("ALTER TABLE `users` ADD COLUMN `type` ENUM('admin', 'customer', 'influencer') DEFAULT 'admin' AFTER `locale`");
        }
    }
};
