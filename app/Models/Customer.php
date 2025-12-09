<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_photo_path',
        'status',
        'locale',
        'total_orders',
        'total_spent',
        'last_order_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_order_at' => 'datetime',
            'total_spent' => 'decimal:2',
        ];
    }

    // ==========================================
    // Relations
    // ==========================================

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class);
    }

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    // ==========================================
    // Helpers
    // ==========================================

    /**
     * Update order statistics for this customer
     */
    public function updateOrderStats(): void
    {
        $this->update([
            'total_orders' => $this->orders()->count(),
            'total_spent' => $this->orders()->where('payment_status', 'paid')->sum('total'),
            'last_order_at' => $this->orders()->latest()->value('created_at'),
        ]);
    }

    /**
     * Get the customer's default shipping address
     */
    public function defaultShippingAddress()
    {
        return $this->shippingAddresses()->where('is_default', true)->first()
            ?? $this->shippingAddresses()->latest()->first();
    }

    /**
     * Check if customer is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if customer is blocked
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }
}
