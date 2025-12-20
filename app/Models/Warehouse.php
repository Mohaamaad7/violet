<?php

namespace App\Models;

use App\Traits\HasFullAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory, HasFullAudit;

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'address',
        'phone',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ==========================================
    // Boot Methods
    // ==========================================

    protected static function boot()
    {
        parent::boot();

        // Auto-generate code on create
        static::creating(function ($warehouse) {
            $warehouse->code = static::generateCode($warehouse->parent_id);
        });
    }

    // ==========================================
    // Relations
    // ==========================================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'parent_id');
    }

    public function stockCounts(): HasMany
    {
        return $this->hasMany(StockCount::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // ==========================================
    // Static Helpers
    // ==========================================

    /**
     * Get the default warehouse
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Set this warehouse as default (and unset others)
     */
    public function setAsDefault(): void
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    /**
     * Generate hierarchical code for warehouse
     * Format: WH-{parent_id}-{self_id} or WH-{id} for root
     */
    public static function generateCode(?int $parentId): string
    {
        // Get next ID (approximate)
        $nextId = static::max('id') + 1;

        if ($parentId) {
            // Get parent's hierarchy path
            $parent = static::find($parentId);
            if ($parent) {
                // Remove "WH-" prefix and append new ID
                $parentPath = str_replace('WH-', '', $parent->code);
                return 'WH-' . $parentPath . '-' . $nextId;
            }
        }

        // Root warehouse
        return 'WH-' . $nextId;
    }

    // ==========================================
    // Accessors
    // ==========================================

    /**
     * Get full hierarchical name (Parent > Child > Grandchild)
     */
    public function getFullNameAttribute(): string
    {
        $names = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $names);
    }

    /**
     * Get depth level in hierarchy
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Check if this is a root warehouse
     */
    public function getIsRootAttribute(): bool
    {
        return is_null($this->parent_id);
    }
}
