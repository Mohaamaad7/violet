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
        Schema::table('influencers', function (Blueprint $table) {
            if (!Schema::hasColumn('influencers', 'primary_platform')) {
                $table->string('primary_platform')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('influencers', 'handle')) {
                $table->string('handle')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('influencers', function (Blueprint $table) {
            $table->dropColumn(['primary_platform', 'handle']);
        });
    }
};
