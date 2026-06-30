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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('combo_rule_id')->nullable()->after('discount_code_id')->constrained('combo_rules')->nullOnDelete();
            $table->decimal('combo_discount_amount', 10, 2)->default(0)->after('shipping_discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['combo_rule_id']);
            $table->dropColumn(['combo_rule_id', 'combo_discount_amount']);
        });
    }
};
