<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'order_id',
        'return_number',
        'type',
        'status',
        'reason',
        'customer_notes',
        'admin_notes',
        'refund_amount',
        'refund_status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'processed_by',
        'processed_at',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relations
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getCustomerNameAttribute(): string
    {
        return $this->order->customer_name ?? 'Unknown';
    }

    public function getCustomerEmailAttribute(): ?string
    {
        return $this->order->customer_email;
    }

    public function getCustomerPhoneAttribute(): ?string
    {
        return $this->order->customer_phone;
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'completed' => 'success',
            default => 'secondary',
        };
    }

    public function getTypeBadgeColorAttribute(): string
    {
        return match($this->type) {
            'rejection' => 'danger',
            'return_after_delivery' => 'warning',
            default => 'secondary',
        };
    }

    // Methods
    public static function generateReturnNumber(): string
    {
        $date = now()->format('Ymd');
        $lastReturn = self::whereDate('created_at', now())->latest()->first();
        
        if ($lastReturn) {
            $lastNumber = (int) substr($lastReturn->return_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "RET-{$date}-{$newNumber}";
    }
}
