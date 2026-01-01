<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfluencerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
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
        'portfolio',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'content_type' => 'array',
        'instagram_followers' => 'integer',
        'facebook_followers' => 'integer',
        'tiktok_followers' => 'integer',
        'youtube_followers' => 'integer',
        'twitter_followers' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
