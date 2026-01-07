<?php

namespace App\Models;

use App\Traits\HasFullAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasFullAudit;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'long_description',
        'specifications',
        'how_to_use',
        'price',
        'sale_price',
        'cost_price',
        'stock',
        'low_stock_threshold',
        'weight',
        'brand',
        'barcode',
        'status',
        'is_featured',
        'views_count',
        'sales_count',
        'average_rating',
        'reviews_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'views_count' => 'integer',
        'sales_count' => 'integer',
        'average_rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'low_stock_threshold');
    }

    // Accessors
    public function getFinalPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->is_on_sale) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function getPrimaryImageAttribute()
    {
        // Try Spatie Media Library first
        $primaryMedia = $this->getMedia('product-images')
            ->filter(fn($media) => $media->getCustomProperty('is_primary') === true)
            ->first();

        if ($primaryMedia) {
            return $primaryMedia->getUrl();
        }

        // Fallback to first media
        $firstMedia = $this->getFirstMedia('product-images');
        if ($firstMedia) {
            return $firstMedia->getUrl();
        }

        // Fallback to old system (for backwards compatibility)
        $primary = $this->images()->where('is_primary', true)->first();
        if ($primary && $primary->image_path) {
            return asset('storage/' . $primary->image_path);
        }

        // Final fallback to placeholder
        return asset('images/default-product.svg');
    }

    public function getIsInStockAttribute()
    {
        return $this->stock > 0;
    }

    public function getStockStatusAttribute()
    {
        return $this->is_in_stock ? 'In Stock' : 'Out of Stock';
    }

    /**
     * Register media collections for Spatie Media Library
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('product-images')
            ->useDisk('public')
            ->registerMediaConversions(function () {
                // Small thumbnail for quick loading (wishlist, mini cart)
                // fit(Contain) places entire image inside canvas with white padding
                $this
                    ->addMediaConversion('thumbnail')
                    ->fit(\Spatie\Image\Enums\Fit::Contain, 150, 150)
                    ->sharpen(10)
                    ->keepOriginalImageFormat()
                    ->background('#ffffff');

                // Card-sized image for product listings
                // Uses Contain to show entire product without cropping
                $this
                    ->addMediaConversion('card')
                    ->fit(\Spatie\Image\Enums\Fit::Contain, 400, 400)
                    ->sharpen(10)
                    ->quality(90)
                    ->keepOriginalImageFormat()
                    ->background('#ffffff');

                // High-quality preview for product detail page zoom
                // Larger size for zoom quality, original is also preserved for max zoom
                $this
                    ->addMediaConversion('preview')
                    ->fit(\Spatie\Image\Enums\Fit::Contain, 1200, 1200)
                    ->sharpen(10)
                    ->quality(95)
                    ->keepOriginalImageFormat()
                    ->background('#ffffff');
            });
    }
}
