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
        // Add 'stock_count' to the type enum
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('restock', 'sale', 'return', 'adjustment', 'expired', 'damaged', 'stock_count') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('restock', 'sale', 'return', 'adjustment', 'expired', 'damaged') NOT NULL");
    }
};
