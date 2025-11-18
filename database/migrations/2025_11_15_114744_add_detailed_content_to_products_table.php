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
        Schema::table('products', function (Blueprint $table) {
            $table->longText('long_description')->nullable()->after('short_description');
            $table->text('specifications')->nullable()->after('long_description');
            $table->text('how_to_use')->nullable()->after('specifications');
            $table->decimal('average_rating', 3, 2)->default(0)->after('sales_count');
            $table->integer('reviews_count')->default(0)->after('average_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['long_description', 'specifications', 'how_to_use', 'average_rating', 'reviews_count']);
        });
    }
};
