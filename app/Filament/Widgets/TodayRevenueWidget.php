<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Today's Revenue Widget
 * إيرادات اليوم
 */
class TodayRevenueWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.today_revenue.heading');
    }

    protected function getStats(): array
    {
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereIn('status', [OrderStatus::DELIVERED])
            ->where('payment_status', 'paid')
            ->sum('total');

        $yesterdayRevenue = Order::whereDate('created_at', today()->subDay())
            ->whereIn('status', [OrderStatus::DELIVERED])
            ->where('payment_status', 'paid')
            ->sum('total');

        $percentageChange = $yesterdayRevenue > 0
            ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100
            : 0;

        return [
            Stat::make(__('admin.widgets.today_revenue.title'), number_format($todayRevenue, 2) . ' ' . __('admin.currency.egp_short'))
                ->description($this->getChangeDescription($percentageChange))
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'danger')
                ->chart($this->getLast7DaysRevenue()),
        ];
    }

    protected function getLast7DaysRevenue(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->whereIn('status', [OrderStatus::DELIVERED])
                ->where('payment_status', 'paid')
                ->sum('total');
            $data[] = (float) $revenue;
        }
        return $data;
    }

    protected function getChangeDescription(float $percentage): string
    {
        if ($percentage == 0) {
            return __('admin.widgets.stats.no_change') . ' ' . __('admin.widgets.stats.vs_yesterday');
        }
        $sign = $percentage > 0 ? '+' : '';
        return $sign . number_format($percentage, 1) . '% ' . __('admin.widgets.stats.vs_yesterday');
    }
}
