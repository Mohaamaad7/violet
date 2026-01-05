<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HelpEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'category',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Category options for help entries
     */
    public const CATEGORIES = [
        'orders' => 'الطلبات',
        'products' => 'المنتجات',
        'marketing' => 'التسويق',
        'inventory' => 'المخزون',
        'sales' => 'المبيعات',
        'system' => 'النظام',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            if (empty($entry->slug)) {
                $entry->slug = Str::slug($entry->question);
            }
            // Ensure unique slug
            $originalSlug = $entry->slug;
            $counter = 1;
            while (static::where('slug', $entry->slug)->exists()) {
                $entry->slug = $originalSlug . '-' . $counter++;
            }
        });

        static::updating(function ($entry) {
            if ($entry->isDirty('question') && !$entry->isDirty('slug')) {
                $entry->slug = Str::slug($entry->question);
                // Ensure unique slug (excluding current record)
                $originalSlug = $entry->slug;
                $counter = 1;
                while (static::where('slug', $entry->slug)->where('id', '!=', $entry->id)->exists()) {
                    $entry->slug = $originalSlug . '-' . $counter++;
                }
            }
        });
    }

    /**
     * Scope for active entries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Scope for category filtering
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get translated category name
     */
    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
