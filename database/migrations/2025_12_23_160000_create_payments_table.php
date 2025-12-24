<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            // Payment Info
            $table->string('reference', 50)->unique();
            $table->string('transaction_id')->nullable()->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');

            // Method & Status
            $table->enum('payment_method', [
                'card',
                'vodafone_cash',
                'orange_money',
                'etisalat_cash',
                'meeza',
                'valu',
                'souhoola',
                'sympl'
            ]);
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'refunded',
                'partially_refunded',
                'cancelled',
                'expired'
            ])->default('pending');

            // Gateway
            $table->string('gateway')->default('kashier');
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();

            // Error
            $table->string('failure_reason')->nullable();
            $table->string('failure_code')->nullable();

            // Refund
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->string('refund_reference')->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Security
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index('transaction_id');
            $table->index('gateway_transaction_id');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
