<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_navigation_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('navigation_group_id')->constrained('navigation_group_configurations')->cascadeOnDelete();
            $table->boolean('is_visible')->default(true)->comment('Role visibility for this group');
            $table->integer('order_position')->default(0)->comment('Custom sort for role');
            $table->timestamps();

            $table->unique(['role_id', 'navigation_group_id'], 'unique_role_nav_group');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_navigation_groups');
    }
};
