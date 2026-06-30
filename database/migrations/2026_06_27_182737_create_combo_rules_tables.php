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
        Schema::create('combo_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('discount_percentage');
            $table->integer('max_uses_per_user')->nullable(); // null means unlimited
            $table->integer('priority')->default(0);
            $table->datetime('starts_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('combo_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->integer('required_quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('combo_rule_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_rules_tables');
    }
};
