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
        Schema::table('returns', function (Blueprint $table) {
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->unsignedBigInteger('processed_by')->nullable()->after('rejected_at');
            $table->timestamp('processed_at')->nullable()->after('processed_by');

            // Add foreign keys
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['rejected_by', 'rejected_at', 'processed_by', 'processed_at']);
        });
    }
};
