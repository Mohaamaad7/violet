<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_id',
        'discount_code_id',
        'shipping_address_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_governorate',
        'guest_city',
        'guest_address',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'tax_amount',
        'total',
        'notes',
        'admin_notes',
        'payment_transaction_id',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'return_status',
        'rejected_at',
        'rejection_reason',
        'stock_deducted_at',
        'stock_restored_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rejected_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
        'stock_restored_at' => 'datetime',
    ];

    // Relations

    /**
     * Customer who placed the order (new relation)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Staff user who processed/handled the order (kept for backwards compatibility)
     * @deprecated Use customer() for the actual customer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get customer name (from customer or guest fields)
     */
    public function getCustomerNameAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->name;
        }
        return $this->guest_name ?? 'ضيف';
    }

    /**
     * Get customer email (from customer or guest fields)
     */
    public function getCustomerEmailAttribute(): ?string
    {
        if ($this->customer) {
            return $this->customer->email;
        }
        return $this->guest_email;
    }

    /**
     * Get customer phone (from customer or guest fields)
     */
    public function getCustomerPhoneAttribute(): ?string
    {
        if ($this->customer) {
            return $this->customer->phone;
        }
        return $this->guest_phone;
    }

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(InfluencerCommission::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }
}
