<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Role Widget Default Model
 * 
 * Stores default widget configurations for each role.
 * Users inherit these settings unless they have custom preferences.
 */
class RoleWidgetDefault extends Model
{
    protected $fillable = [
        'role_id',
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
     * The role this default belongs to
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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
     * Get visible defaults for a role
     */
    public function scopeVisibleForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId)
            ->where('is_visible', true)
            ->orderBy('order_position');
    }
}
