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
        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->integer('system_quantity'); // Quantity at time of count creation
            $table->integer('counted_quantity')->nullable(); // Physically counted
            $table->integer('difference')->nullable(); // counted - system
            $table->decimal('difference_value', 12, 2)->nullable(); // difference × cost_price
            $table->text('notes')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();

            $table->index('stock_count_id');
            $table->index('product_id');
            $table->index('variant_id');

            // Unique constraint to prevent duplicate items in same count
            $table->unique(['stock_count_id', 'product_id', 'variant_id'], 'stock_count_product_variant_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
    }
};
