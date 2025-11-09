<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'governorate',
        'city',
        'area',
        'street_address',
        'building_number',
        'floor',
        'apartment',
        'landmark',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_address,
            $this->building_number ? "Building {$this->building_number}" : null,
            $this->floor ? "Floor {$this->floor}" : null,
            $this->apartment ? "Apt {$this->apartment}" : null,
            $this->area,
            $this->city,
            $this->governorate,
        ]);

        return implode(', ', $parts);
    }
}
