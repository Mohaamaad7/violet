<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboRuleCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_rule_id',
        'condition_type',
        'category_id',
        'product_id',
        'required_quantity',
    ];

    protected $hidden = [
        'required_quantity',
    ];

    protected $casts = [
        'condition_type' => 'string',
        'required_quantity' => 'integer',
    ];

    public function comboRule(): BelongsTo
    {
        return $this->belongsTo(ComboRule::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
