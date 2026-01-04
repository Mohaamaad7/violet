<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Page;
use App\Models\Influencer;
use App\Models\DiscountCode;
use Illuminate\Support\Facades\Auth;

class DiscountCodesPage extends Page
{
    protected static ?int $navigationSort = 4;

    public function getView(): string
    {
        return 'filament.partners.pages.discount-codes-page';
    }

    public function getLayout(): string
    {
        return 'components.layouts.partners';
    }

    public function getTitle(): string
    {
        return __('messages.partners.nav.discount_codes');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.partners.nav.discount_codes');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * Get current influencer
     */
    protected function getInfluencer(): ?Influencer
    {
        return Influencer::where('user_id', Auth::id())
            ->with(['discountCodes'])
            ->first();
    }

    /**
     * Get discount codes
     */
    public function getDiscountCodes()
    {
        $influencer = $this->getInfluencer();
        if (!$influencer) {
            return collect([]);
        }

        return $influencer->discountCodes()
            ->withCount(['orders', 'usages'])
            ->withSum('usages', 'discount_amount')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $influencer = $this->getInfluencer();
        if (!$influencer) {
            return [
                'total_codes' => 0,
                'active_codes' => 0,
                'total_uses' => 0,
                'total_discount_given' => 0,
            ];
        }

        $codes = $this->getDiscountCodes();

        return [
            'total_codes' => $codes->count(),
            'active_codes' => $codes->where('is_active', true)->count(),
            'total_uses' => $codes->sum('times_used'),
            'total_discount_given' => $codes->sum('usages_sum_discount_amount') ?? 0,
        ];
    }
}
