<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CampaignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'status',
        'error_message',
        'sent_at',
        'opened_at',
        'clicked_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Get the campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * Get the subscriber
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscription::class, 'subscriber_id');
    }

    /**
     * Scope for sent emails
     */
    public function scopeSent(Builder $query): void
    {
        $query->where('status', 'sent');
    }

    /**
     * Scope for failed emails
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('status', 'failed');
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
