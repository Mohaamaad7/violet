<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add resource_class column to role_resource_access table
 * This enables Zero-Config approach - no need for resource_configurations table
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_resource_access', function (Blueprint $table) {
            // Add resource_class column (stores the full class name)
            $table->string('resource_class')->nullable()->after('role_id');

            // Make resource_configuration_id nullable (we're moving away from it)
            $table->unsignedBigInteger('resource_configuration_id')->nullable()->change();

            // Add index for faster lookups
            $table->index(['role_id', 'resource_class']);
        });
    }

    public function down(): void
    {
        Schema::table('role_resource_access', function (Blueprint $table) {
            $table->dropIndex(['role_id', 'resource_class']);
            $table->dropColumn('resource_class');
        });
    }
};
