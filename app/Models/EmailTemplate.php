<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'category',
        'description',
        'subject_ar',
        'subject_en',
        'content_mjml',
        'available_variables',
        'primary_color',
        'secondary_color',
        'logo_path',
        'is_active',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Template types
     */
    public const TYPES = [
        'customer' => 'للعملاء',
        'admin' => 'للإدارة',
        'system' => 'للنظام',
    ];

    /**
     * Template categories
     */
    public const CATEGORIES = [
        'order' => 'الطلبات',
        'auth' => 'التسجيل والمصادقة',
        'notification' => 'الإشعارات',
        'marketing' => 'التسويق',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (EmailTemplate $template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Get email logs for this template.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Get subject based on locale.
     */
    public function getSubject(string $locale = 'ar'): string
    {
        return $locale === 'ar' ? $this->subject_ar : $this->subject_en;
    }

    /**
     * Scope for active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by category.
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Find template by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
