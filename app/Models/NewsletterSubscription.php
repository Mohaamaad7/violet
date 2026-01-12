<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'customer_id',
        'status',
        'source',
        'ip_address',
        'user_agent',
        'unsubscribe_token',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_reason',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate unsubscribe token on creation
        static::creating(function ($subscription) {
            if (empty($subscription->unsubscribe_token)) {
                $subscription->unsubscribe_token = Str::random(64);
            }
            
            if (empty($subscription->subscribed_at)) {
                $subscription->subscribed_at = now();
            }
        });
    }

    /**
     * Get the customer that owns the subscription
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all campaign logs for this subscriber
     */
    public function campaignLogs(): HasMany
    {
        return $this->hasMany(CampaignLog::class, 'subscriber_id');
    }

    /**
     * Scope for active subscribers only
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope for subscribers in last N days
     */
    public function scopeRecentSubscribers(Builder $query, int $days = 30): void
    {
        $query->where('subscribed_at', '>=', now()->subDays($days));
    }

    /**
     * Unsubscribe this subscriber
     */
    public function unsubscribe(?string $reason = null): void
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
            'unsubscribe_reason' => $reason,
        ]);
    }

    /**
     * Mark as bounced
     */
    public function markAsBounced(): void
    {
        $this->update(['status' => 'bounced']);
    }

    /**
     * Check if subscriber is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
