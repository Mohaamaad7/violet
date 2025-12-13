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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_number', 100);
            $table->integer('quantity_initial')->default(0);
            $table->integer('quantity_current')->default(0);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('supplier', 255)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'expired', 'disposed'])->default('active');
            $table->timestamps();
            
            // Indexes
            $table->index('product_id');
            $table->index('batch_number');
            $table->index('expiry_date');
            $table->index('status');
            $table->unique(['product_id', 'batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
