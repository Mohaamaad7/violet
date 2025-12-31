<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * New Orders Today Widget
 * طلبات جديدة اليوم
 */
class NewOrdersTodayWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.new_orders_today.heading');
    }

    protected function getStats(): array
    {
        $newOrdersCount = Order::whereDate('created_at', today())->count();

        $yesterdayNewOrders = Order::whereDate('created_at', today()->subDay())->count();

        $percentageChange = $yesterdayNewOrders > 0
            ? (($newOrdersCount - $yesterdayNewOrders) / $yesterdayNewOrders) * 100
            : 0;

        return [
            Stat::make(__('admin.widgets.new_orders_today.title'), $newOrdersCount)
                ->description($this->getChangeDescription($percentageChange))
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'warning')
                ->url(route('filament.admin.resources.orders.index'))
                ->chart($this->getLast7DaysNewOrders()),
        ];
    }

    protected function getLast7DaysNewOrders(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = Order::whereDate('created_at', $date)->count();
            $data[] = $count;
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
