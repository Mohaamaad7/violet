<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add a nullable JSON metadata column to the pages table.
     * Used for page-specific structured data (e.g., About Us: vision, values, achievements).
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
