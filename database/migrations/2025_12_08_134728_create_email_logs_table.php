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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            
            // Reference to template
            $table->foreignId('email_template_id')
                ->nullable()
                ->constrained('email_templates')
                ->nullOnDelete();
            
            // Polymorphic relationship (Order, User, etc.)
            $table->nullableMorphs('related');
            
            // Recipient Information
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            
            // Email Content
            $table->string('subject', 500);
            $table->string('locale', 5)->default('ar');
            
            // Status Tracking
            $table->enum('status', [
                'pending',    // في الانتظار
                'queued',     // في الطابور
                'sent',       // تم الإرسال
                'delivered',  // تم التسليم
                'opened',     // تم الفتح
                'clicked',    // تم النقر
                'failed',     // فشل
                'bounced'     // ارتداد
            ])->default('pending');
            
            // Timestamps for tracking
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // Error Information
            $table->text('error_message')->nullable();
            
            // Additional Data
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes (nullableMorphs already creates index for related_type, related_id)
            $table->index('email_template_id');
            $table->index('recipient_email');
            $table->index('status');
            $table->index('sent_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
