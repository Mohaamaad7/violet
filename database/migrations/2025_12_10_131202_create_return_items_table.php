<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name', 255);
            $table->string('product_sku', 100);
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->enum('condition', ['good', 'opened', 'damaged'])->default('good');
            $table->boolean('restocked')->default(false);
            $table->timestamp('restocked_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('return_id');
            $table->index('product_id');
            $table->index('order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
