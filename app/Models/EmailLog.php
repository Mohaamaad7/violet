<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_template_id',
        'related_type',
        'related_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'locale',
        'status',
        'queued_at',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'failed_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Available statuses
     */
    public const STATUSES = [
        'pending' => 'في الانتظار',
        'queued' => 'في الطابور',
        'sent' => 'تم الإرسال',
        'delivered' => 'تم التسليم',
        'opened' => 'تم الفتح',
        'clicked' => 'تم النقر',
        'failed' => 'فشل',
        'bounced' => 'ارتداد',
    ];

    /**
     * Get the email template.
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Get the related model (Order, User, etc.).
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark as queued.
     */
    public function markAsQueued(): self
    {
        $this->update([
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark as sent.
     */
    public function markAsSent(): self
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark as delivered.
     */
    public function markAsDelivered(): self
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark as opened.
     */
    public function markAsOpened(): self
    {
        $this->update([
            'status' => 'opened',
            'opened_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark as clicked.
     */
    public function markAsClicked(): self
    {
        $this->update([
            'status' => 'clicked',
            'clicked_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark as failed.
     */
    public function markAsFailed(string $errorMessage): self
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
        ]);

        return $this;
    }

    /**
     * Mark as bounced.
     */
    public function markAsBounced(string $reason = null): self
    {
        $this->update([
            'status' => 'bounced',
            'failed_at' => now(),
            'error_message' => $reason,
        ]);

        return $this;
    }

    /**
     * Scope for pending emails.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for sent emails.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed emails.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'bounced']);
    }

    /**
     * Scope by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
