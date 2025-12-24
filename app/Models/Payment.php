<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'customer_id',
        'reference',
        'transaction_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'gateway',
        'gateway_order_id',
        'gateway_transaction_id',
        'gateway_response',
        'failure_reason',
        'failure_code',
        'refunded_amount',
        'refund_reference',
        'paid_at',
        'refunded_at',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // ==================== Boot ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            // Auto-generate reference
            if (empty($payment->reference)) {
                $payment->reference = self::generateReference();
            }

            // Auto-set expires_at (24 hours)
            if (empty($payment->expires_at)) {
                $payment->expires_at = now()->addHours(24);
            }
        });
    }

    // ==================== Relationships ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // ==================== Scopes ====================

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '<', now());
    }

    // ==================== Accessors ====================

    protected function isPaid(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'completed'
        );
    }

    protected function isRefundable(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'completed'
            && $this->paid_at?->diffInDays(now()) <= 30
            && $this->refunded_amount < $this->amount
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                'completed' => 'success',
                'pending' => 'warning',
                'processing' => 'info',
                'failed' => 'danger',
                'refunded', 'partially_refunded' => 'gray',
                'cancelled', 'expired' => 'secondary',
                default => 'primary',
            }
        );
    }

    // ==================== Helper Methods ====================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status === 'pending';
    }

    public function canBeRefunded(): bool
    {
        return $this->is_refundable;
    }

    public function markAsCompleted(string $transactionId, ?array $response = null): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'gateway_transaction_id' => $transactionId,
            'gateway_response' => $response,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason, ?string $code = null, ?array $response = null): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'failure_code' => $code,
            'gateway_response' => $response,
        ]);
    }

    public function markAsRefunded(float $amount, string $reference): void
    {
        $newRefundedAmount = $this->refunded_amount + $amount;

        $this->update([
            'status' => $newRefundedAmount >= $this->amount ? 'refunded' : 'partially_refunded',
            'refunded_amount' => $newRefundedAmount,
            'refund_reference' => $reference,
            'refunded_at' => now(),
        ]);
    }

    // ==================== Static Methods ====================

    public static function generateReference(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}
