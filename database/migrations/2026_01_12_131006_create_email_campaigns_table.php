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
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['offers', 'custom', 'newsletter'])->default('custom');
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->longText('content_html')->nullable();
            $table->json('content_json')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled'])->default('draft');
            
            // Targeting
            $table->enum('send_to', ['all', 'active_only', 'recent', 'custom'])->default('active_only');
            $table->json('custom_filters')->nullable();
            
            // Statistics
            $table->integer('recipients_count')->default(0);
            $table->integer('emails_sent')->default(0);
            $table->integer('emails_failed')->default(0);
            $table->integer('emails_bounced')->default(0);
            $table->integer('emails_opened')->default(0);
            $table->integer('emails_clicked')->default(0);
            
            // Timing
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Settings
            $table->integer('send_rate_limit')->default(50)->comment('Emails per minute');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('type');
            $table->index('created_by');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
