<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Page;
use App\Models\Influencer;
use App\Models\CommissionPayout;
use Illuminate\Support\Facades\Auth;

class PayoutsPage extends Page
{
    protected static ?int $navigationSort = 5;

    public function getView(): string
    {
        return 'filament.partners.pages.payouts-page';
    }

    public function getLayout(): string
    {
        return 'components.layouts.partners';
    }

    public function getTitle(): string
    {
        return __('messages.partners.nav.payouts');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.partners.nav.payouts');
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
            ->first();
    }

    /**
     * Get payouts history
     */
    public function getPayouts()
    {
        $influencer = $this->getInfluencer();
        if (!$influencer) {
            return collect([]);
        }

        return CommissionPayout::where('influencer_id', $influencer->id)
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
                'available_balance' => 0,
                'total_withdrawn' => 0,
                'pending_requests' => 0,
                'total_payouts' => 0,
            ];
        }

        $payouts = $this->getPayouts();

        return [
            'available_balance' => $influencer->balance ?? 0,
            'total_withdrawn' => $payouts->where('status', 'paid')->sum('amount'),
            'pending_requests' => $payouts->where('status', 'pending')->count(),
            'total_payouts' => $payouts->count(),
        ];
    }
}
