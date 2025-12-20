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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Add variant support
            $table->foreignId('variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->nullOnDelete();

            // Add warehouse support
            $table->foreignId('warehouse_id')
                ->nullable()
                ->after('variant_id')
                ->constrained('warehouses')
                ->nullOnDelete();

            // Add index for variant
            $table->index('variant_id');
            $table->index('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['variant_id', 'warehouse_id']);
        });
    }
};
