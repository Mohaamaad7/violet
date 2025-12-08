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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['customer', 'admin', 'system'])->default('customer');
            $table->enum('category', ['order', 'auth', 'notification', 'marketing'])->default('notification');
            $table->text('description')->nullable();
            
            // Subject Lines (Bilingual)
            $table->string('subject_ar', 500);
            $table->string('subject_en', 500);
            
            // MJML Template Content
            $table->longText('content_mjml');
            
            // Available Variables for this template
            $table->json('available_variables')->nullable();
            
            // Styling Options
            $table->string('primary_color', 7)->default('#4F46E5');
            $table->string('secondary_color', 7)->default('#F59E0B');
            $table->string('logo_path', 500)->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('type');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
