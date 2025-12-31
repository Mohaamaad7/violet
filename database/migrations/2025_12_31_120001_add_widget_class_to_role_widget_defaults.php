<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add widget_class column to role_widget_defaults table
 * This enables Zero-Config approach - no need for widget_configurations table
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_widget_defaults', function (Blueprint $table) {
            // Add widget_class column (stores the full class name)
            $table->string('widget_class')->nullable()->after('role_id');

            // Make widget_configuration_id nullable (we're moving away from it)
            $table->unsignedBigInteger('widget_configuration_id')->nullable()->change();

            // Add index for faster lookups
            $table->index(['role_id', 'widget_class']);
        });
    }

    public function down(): void
    {
        Schema::table('role_widget_defaults', function (Blueprint $table) {
            $table->dropIndex(['role_id', 'widget_class']);
            $table->dropColumn('widget_class');
        });
    }
};
