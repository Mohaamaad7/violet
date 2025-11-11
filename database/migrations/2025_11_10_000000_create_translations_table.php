<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('locale', 10);
            $table->text('value');
            $table->string('group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['key', 'locale']);
            $table->index(['locale', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
