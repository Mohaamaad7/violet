<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'subject',
        'preview_text',
        'content_html',
        'content_json',
        'status',
        'send_to',
        'custom_filters',
        'recipients_count',
        'emails_sent',
        'emails_failed',
        'emails_bounced',
        'emails_opened',
        'emails_clicked',
        'scheduled_at',
        'started_at',
        'completed_at',
        'send_rate_limit',
        'created_by',
    ];

    protected $casts = [
        'custom_filters' => 'array',
        'content_json' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the offers attached to this campaign (for type='offers')
     */
    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(
            DiscountCode::class,
            'campaign_offers',
            'campaign_id',
            'offer_id'
        )
        ->withPivot('display_order')
        ->withTimestamps()
        ->orderBy('campaign_offers.display_order');
    }

    /**
     * Get all logs for this campaign
     */
    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class, 'campaign_id');
    }

    /**
     * Get the user who created this campaign
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for draft campaigns
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    /**
     * Scope for sent campaigns
     */
    public function scopeSent(Builder $query): void
    {
        $query->where('status', 'sent');
    }

    /**
     * Scope for active campaigns (sending or sent)
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ['sending', 'sent']);
    }

    /**
     * Check if campaign is in draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if campaign is sending
     */
    public function isSending(): bool
    {
        return $this->status === 'sending';
    }

    /**
     * Check if campaign is sent
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Calculate success rate
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->recipients_count === 0) {
            return 0;
        }
        
        return round(($this->emails_sent / $this->recipients_count) * 100, 2);
    }

    /**
     * Calculate failure rate
     */
    public function getFailureRateAttribute(): float
    {
        if ($this->recipients_count === 0) {
            return 0;
        }
        
        return round(($this->emails_failed / $this->recipients_count) * 100, 2);
    }
}
