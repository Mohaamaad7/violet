<?php

namespace App\Filament\Partners\Pages;

use App\Models\DiscountCode;
use App\Models\Influencer;
use App\Models\InfluencerCommission;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class InfluencerDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.partners.pages.influencer-dashboard';

    /**
     * Page title
     */
    public function getTitle(): string|Htmlable
    {
        return __('messages.partners.dashboard.title');
    }

    /**
     * Get the influencer record for current user
     */
    public function getInfluencer(): ?Influencer
    {
        return Influencer::where('user_id', auth()->id())
            ->with(['discountCodes', 'commissions'])
            ->first();
    }

    /**
     * Get influencer's discount codes
     */
    public function getDiscountCodes(): \Illuminate\Database\Eloquent\Collection
    {
        $influencer = $this->getInfluencer();
        return $influencer ? $influencer->discountCodes()->where('is_active', true)->get() : collect([]);
    }

    /**
     * Get recent commissions
     */
    public function getRecentCommissions(): \Illuminate\Database\Eloquent\Collection
    {
        $influencer = $this->getInfluencer();
        if (!$influencer) {
            return collect([]);
        }

        return InfluencerCommission::where('influencer_id', $influencer->id)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get statistics for dashboard
     */
    public function getStats(): array
    {
        $influencer = $this->getInfluencer();

        if (!$influencer) {
            return [
                'balance' => 0,
                'total_earned' => 0,
                'total_paid' => 0,
                'total_orders' => 0,
                'total_sales' => 0,
                'pending_commission' => 0,
            ];
        }

        return [
            'balance' => $influencer->balance ?? 0,
            'total_earned' => $influencer->total_commission_earned ?? 0,
            'total_paid' => $influencer->total_commission_paid ?? 0,
            'total_orders' => $influencer->commissions()->count(),
            'total_sales' => $influencer->total_sales ?? 0,
            'pending_commission' => $influencer->commissions()
                ->where('status', 'pending')
                ->sum('commission_amount'),
        ];
    }

    /**
     * Get user name for greeting
     */
    public function getUserName(): string
    {
        return auth()->user()?->name ?? __('messages.partners.dashboard.partner');
    }
}
