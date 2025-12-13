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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->enum('type', ['restock', 'sale', 'return', 'adjustment', 'expired', 'damaged']);
            $table->integer('quantity'); // Can be positive or negative
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference_type', 100)->nullable(); // Polymorphic type
            $table->unsignedBigInteger('reference_id')->nullable(); // Polymorphic id
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('product_id');
            $table->index('batch_id');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
