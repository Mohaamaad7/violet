<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\ResponseCache\Facades\ResponseCache;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'link_url',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }

    protected static function booted()
    {
        static::saved(function ($slider) {
            ResponseCache::clear();
            \Illuminate\Support\Facades\Log::info("ResponseCache cleared because slider ID {$slider->id} was saved.");
        });

        static::deleted(function ($slider) {
            ResponseCache::clear();
            \Illuminate\Support\Facades\Log::info("ResponseCache cleared because slider ID {$slider->id} was deleted.");
        });
    }
}
