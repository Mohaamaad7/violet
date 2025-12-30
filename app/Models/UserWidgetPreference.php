<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Widget Preference Model
 * 
 * Stores user-specific widget preferences that override role defaults.
 */
class UserWidgetPreference extends Model
{
    protected $fillable = [
        'user_id',
        'widget_configuration_id',
        'is_visible',
        'order_position',
        'column_span',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order_position' => 'integer',
        'column_span' => 'integer',
    ];

    // ==================== Relationships ====================

    /**
     * The user who owns this preference
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The widget configuration
     */
    public function widgetConfiguration(): BelongsTo
    {
        return $this->belongsTo(WidgetConfiguration::class);
    }

    // ==================== Scopes ====================

    /**
     * Get visible preferences for a user
     */
    public function scopeVisibleForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->where('is_visible', true)
            ->orderBy('order_position');
    }
}
