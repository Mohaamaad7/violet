<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboRuleUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_rule_id',
        'customer_id',
        'order_id',
    ];

    /**
     * Get the combo rule that owns the usage.
     */
    public function comboRule(): BelongsTo
    {
        return $this->belongsTo(ComboRule::class);
    }

    /**
     * Get the customer that used the rule.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the order associated with the usage.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
