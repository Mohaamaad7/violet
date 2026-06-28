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
        Schema::table('combo_rules', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'fixed_price'])->default('percentage')->after('is_active');
            $table->decimal('fixed_price', 10, 2)->nullable()->after('discount_percentage');
            // We can rename discount_percentage to discount_value later or keep it as is,
            // let's just make it nullable since fixed_price might be used instead.
            $table->decimal('discount_percentage', 5, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combo_rules', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'fixed_price']);
            $table->decimal('discount_percentage', 5, 2)->nullable(false)->change();
        });
    }
};
