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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('key', 100)->unique()->after('id');
            $table->text('value')->nullable()->after('key');
            $table->string('type', 50)->default('string')->after('value');
            $table->string('group', 50)->default('general')->after('type');
            
            // Indexes
            $table->index('key');
            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['key']);
            $table->dropIndex(['group']);
            $table->dropColumn(['key', 'value', 'type', 'group']);
        });
    }
};
