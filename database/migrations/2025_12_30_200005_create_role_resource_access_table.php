<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_resource_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('resource_configuration_id')->constrained('resource_configurations')->cascadeOnDelete();
            $table->boolean('can_view')->default(true)->comment('Can view/list records');
            $table->boolean('can_create')->default(false)->comment('Can create records');
            $table->boolean('can_edit')->default(false)->comment('Can edit records');
            $table->boolean('can_delete')->default(false)->comment('Can delete records');
            $table->boolean('is_visible_in_navigation')->default(true)->comment('Show in sidebar');
            $table->integer('navigation_sort')->default(0)->comment('Custom sort order');
            $table->timestamps();

            $table->unique(['role_id', 'resource_configuration_id'], 'unique_role_resource');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_resource_access');
    }
};
