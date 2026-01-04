<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Page;
use App\Models\Influencer;
use App\Models\InfluencerCommission;
use Illuminate\Support\Facades\Auth;

class CommissionsPage extends Page
{
    protected static ?int $navigationSort = 3;

    // Filter Properties
    public string $statusFilter = 'all';
    public string $dateFilter = 'all';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public int $perPage = 15;
    public int $currentPage = 1;

    public function getView(): string
    {
        return 'filament.partners.pages.commissions-page';
    }

    public function getLayout(): string
    {
        return 'components.layouts.partners';
    }

    public function getTitle(): string
    {
        return __('messages.partners.nav.commissions');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.partners.nav.commissions');
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
            ->with(['commissions', 'discountCodes'])
            ->first();
    }

    /**
     * Get commissions statistics
     */
    public function getStats(): array
    {
        $influencer = $this->getInfluencer();
        if (!$influencer) {
            return [
                'total_earned' => 0,
                'pending_balance' => 0,
                'paid_balance' => 0,
                'total_commissions' => 0,
            ];
        }

        $query = $this->applyFilters(InfluencerCommission::where('influencer_id', $influencer->id));

        return [
            'total_earned' => $query->clone()->sum('commission_amount'),
            'pending_balance' => $query->clone()->where('status', 'pending')->sum('commission_amount'),
            'paid_balance' => $query->clone()->where('status', 'paid')->sum('commission_amount'),
            'total_commissions' => $query->clone()->count(),
        ];
    }

    /**
     * Get paginated commissions
     */
    public function getCommissions()
    {
        $influencer = $this->getInfluencer();
        if (!$influencer) {
            return collect([]);
        }

        $query = InfluencerCommission::where('influencer_id', $influencer->id)
            ->with(['order', 'discountCode']);

        $query = $this->applyFilters($query);

        return $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage, ['*'], 'page', $this->currentPage);
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query)
    {
        // Status Filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Date Filter
        switch ($this->dateFilter) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
            case 'custom':
                if ($this->startDate) {
                    $query->whereDate('created_at', '>=', $this->startDate);
                }
                if ($this->endDate) {
                    $query->whereDate('created_at', '<=', $this->endDate);
                }
                break;
        }

        return $query;
    }

    /**
     * Update status filter
     */
    public function updateStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->currentPage = 1;
    }

    /**
     * Update date filter
     */
    public function updateDateFilter(string $date): void
    {
        $this->dateFilter = $date;
        $this->currentPage = 1;
    }

    /**
     * Go to next page
     */
    public function nextPage(): void
    {
        $this->currentPage++;
    }

    /**
     * Go to previous page
     */
    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }
}
