<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('combo_rule_conditions', function (Blueprint $table) {
            $table->string('condition_type')->default('category')->after('combo_rule_id');
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->foreignId('product_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('combo_rule_conditions', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->dropColumn('condition_type');
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
    }
};
