<?php

namespace App\Models;

use App\Models\Concerns\HasCatalogTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\ResponseCache\Facades\ResponseCache;

class Category extends Model implements HasMedia
{
    use HasCatalogTranslations;
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'icon',
        'order',
        'show_in_header',
        'show_on_home',
        'sort_order',
        'home_sort_order',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'show_in_header' => 'boolean',
        'show_on_home' => 'boolean',
        'sort_order' => 'integer',
        'home_sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => ResponseCache::clear());
        static::deleted(fn () => ResponseCache::clear());
        static::restored(fn () => ResponseCache::clear());
    }

    // Relations
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParent($query)
    {
        return $query->whereNull('parent_id');
    }

    // Helper Methods

    /**
     * Get total active products count including all children categories recursively
     */
    public function getTotalActiveProductsCount(): int
    {
        // Count products directly in this category
        $count = $this->getAttribute('active_products_count')
            ?? $this->products()->where('status', 'active')->count();

        // Recursively count products from all children
        foreach ($this->children as $child) {
            $count += $child->getTotalActiveProductsCount();
        }

        return $count;
    }

    /**
     * Get all descendant category IDs (children, grandchildren, etc.)
     */
    public function getDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getDescendantIds());
        }

        return $ids;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('category-images')
            ->singleFile() // Categories only need one main image
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(200)
                    ->height(200)
                    ->sharpen(10);

                $this->addMediaConversion('card')
                    ->width(400)
                    ->height(400)
                    ->sharpen(10)
                    ->quality(90);
            });
    }
}
