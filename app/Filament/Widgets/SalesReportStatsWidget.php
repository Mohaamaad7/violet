<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesReportStatsWidget extends BaseWidget
{
    // This widget is only used on the Sales Report page
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // إجمالي المبيعات (الطلبات المدفوعة)
        $totalSales = Order::where('payment_status', 'paid')->sum('total');

        // حساب التغيير عن أمس
        $todaySales = Order::where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->sum('total');

        $yesterdaySales = Order::where('payment_status', 'paid')
            ->whereDate('created_at', today()->subDay())
            ->sum('total');

        $salesChange = $yesterdaySales > 0
            ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100
            : 0;

        // عدد الطلبات المدفوعة
        $totalOrders = Order::where('payment_status', 'paid')->count();

        $todayOrders = Order::where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->count();

        $yesterdayOrders = Order::where('payment_status', 'paid')
            ->whereDate('created_at', today()->subDay())
            ->count();

        $ordersChange = $yesterdayOrders > 0
            ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100
            : 0;

        return [
            Stat::make('إجمالي المبيعات', number_format($totalSales, 2) . ' ج.م')
                ->description($this->getChangeDescription($salesChange, 'عن أمس'))
                ->descriptionIcon($salesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesChange >= 0 ? 'success' : 'danger')
                ->chart($this->getLast7DaysSales()),

            Stat::make('عدد الطلبات المدفوعة', $totalOrders)
                ->description($this->getChangeDescription($ordersChange, 'عن أمس'))
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'warning')
                ->chart($this->getLast7DaysOrders()),
        ];
    }

    protected function getLast7DaysSales(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $revenue = Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('total');
            $data[] = (float) $revenue;
        }
        return $data;
    }

    protected function getLast7DaysOrders(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    protected function getChangeDescription(float $percentage, string $suffix = ''): string
    {
        if ($percentage == 0) {
            return 'لا تغيير ' . $suffix;
        }

        $sign = $percentage > 0 ? '+' : '';
        return $sign . number_format($percentage, 1) . '% ' . $suffix;
    }
}
