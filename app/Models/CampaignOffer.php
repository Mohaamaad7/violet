<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'offer_id',
        'display_order',
    ];

    /**
     * Get the campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * Get the offer (discount code)
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class, 'offer_id');
    }
}
