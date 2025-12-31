<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Role Widget Default Model - Zero-Config Approach
 * 
 * Stores ONLY hidden widgets (overrides).
 * If a widget is NOT in this table, it's VISIBLE by default.
 * 
 * Use widget_class directly instead of widget_configuration_id.
 */
class RoleWidgetDefault extends Model
{
    protected $fillable = [
        'role_id',
        'widget_class', // The full class name, e.g., App\Filament\Widgets\TodayRevenueWidget
        'widget_configuration_id', // Legacy - kept for backward compatibility
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Legacy relationship - kept for backward compatibility
    public function widgetConfiguration(): BelongsTo
    {
        return $this->belongsTo(WidgetConfiguration::class);
    }

    // ==================== Scopes ====================

    /**
     * Get hidden widgets for a role
     */
    public function scopeHiddenForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId)
            ->where('is_visible', false);
    }

    /**
     * Check if a widget is hidden for a role
     */
    public static function isHidden(int $roleId, string $widgetClass): bool
    {
        return self::where('role_id', $roleId)
            ->where('widget_class', $widgetClass)
            ->where('is_visible', false)
            ->exists();
    }
}
