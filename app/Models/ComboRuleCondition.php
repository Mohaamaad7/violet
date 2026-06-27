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
        'category_id',
        'required_quantity',
    ];

    protected $casts = [
        'required_quantity' => 'integer',
    ];

    /**
     * Get the combo rule that owns the condition.
     */
    public function comboRule(): BelongsTo
    {
        return $this->belongsTo(ComboRule::class);
    }

    /**
     * Get the category associated with the condition.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
