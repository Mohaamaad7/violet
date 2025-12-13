<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'quantity_initial',
        'quantity_current',
        'manufacturing_date',
        'expiry_date',
        'supplier',
        'notes',
        'status',
    ];

    protected $casts = [
        'quantity_initial' => 'integer',
        'quantity_current' => 'integer',
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Relations
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, int $days = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->whereDate('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', now());
            });
    }

    // Accessors
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    public function getAlertLevelAttribute(): string
    {
        $days = $this->days_until_expiry;
        
        if ($days === null) {
            return 'none';
        }
        
        if ($days < 0) {
            return 'expired';
        }
        
        if ($days < 7) {
            return 'critical';
        }
        
        if ($days < 30) {
            return 'warning';
        }
        
        return 'ok';
    }
}
