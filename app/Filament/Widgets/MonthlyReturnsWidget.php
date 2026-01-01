<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\OrderReturn;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Monthly Returns Widget
 * مرتجعات هذا الشهر
 */
class MonthlyReturnsWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 7;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.monthly_returns.heading');
    }

    protected function getStats(): array
    {
        $monthlyReturns = OrderReturn::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthReturns = OrderReturn::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $percentageChange = $lastMonthReturns > 0
            ? (($monthlyReturns - $lastMonthReturns) / $lastMonthReturns) * 100
            : 0;

        $description = $percentageChange == 0
            ? __('admin.widgets.stats.no_change')
            : ($percentageChange > 0 ? '+' : '') . number_format($percentageChange, 1) . '%';

        return [
            Stat::make(__('admin.widgets.monthly_returns.title'), $monthlyReturns)
                ->description($description . ' ' . __('admin.widgets.monthly_returns.vs_last_month'))
                ->descriptionIcon($percentageChange <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($percentageChange <= 0 ? 'success' : 'warning'),
        ];
    }
}
