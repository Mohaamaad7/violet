<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Influencer extends Model
{
    protected $fillable = [
        'user_id',
        'primary_platform',
        'handle',
        'instagram_url',
        'facebook_url',
        'tiktok_url',
        'youtube_url',
        'twitter_url',
        'instagram_followers',
        'facebook_followers',
        'tiktok_followers',
        'youtube_followers',
        'twitter_followers',
        'content_type',
        'commission_rate',
        'total_sales',
        'total_commission_earned',
        'total_commission_paid',
        'balance',
        'status',
    ];

    protected $casts = [
        'content_type' => 'array',
        'commission_rate' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'total_commission_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'instagram_followers' => 'integer',
        'facebook_followers' => 'integer',
        'tiktok_followers' => 'integer',
        'youtube_followers' => 'integer',
        'twitter_followers' => 'integer',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discountCodes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(InfluencerCommission::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $this->where('status', 'active');
    }
}
