<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Widget Configuration Model
 * 
 * Stores all available widgets for the admin dashboard.
 * Each widget can be configured globally and then customized per role or user.
 */
class WidgetConfiguration extends Model
{
    protected $fillable = [
        'widget_class',
        'widget_name',
        'widget_group',
        'description',
        'is_active',
        'default_order',
        'default_column_span',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_order' => 'integer',
        'default_column_span' => 'integer',
    ];

    // ==================== Relationships ====================

    /**
     * User preferences for this widget
     */
    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserWidgetPreference::class);
    }

    /**
     * Role defaults for this widget
     */
    public function roleDefaults(): HasMany
    {
        return $this->hasMany(RoleWidgetDefault::class);
    }

    // ==================== Scopes ====================

    /**
     * Only active widgets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by group
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('widget_group', $group);
    }

    // ==================== Helpers ====================

    /**
     * Get widgets ordered by default position
     */
    public static function getOrderedActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->orderBy('default_order')
            ->get();
    }
}
