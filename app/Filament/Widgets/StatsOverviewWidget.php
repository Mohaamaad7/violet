<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    /**
     * Widget refresh interval in seconds (not static in base class)
     */
    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.stats.heading');
    }

    /**
     * Get the stats cards for the widget
     */
    protected function getStats(): array
    {
        return [
            $this->getTodayRevenueCard(),
            $this->getNewOrdersTodayCard(),
            $this->getTotalCustomersCard(),
            $this->getProductsInStockCard(),
        ];
    }

    /**
     * Card 1: Today's Revenue
     * Shows total revenue for delivered orders today
     */
    protected function getTodayRevenueCard(): Stat
    {
        $todayRevenue = Order::whereDate('created_at', today())
            ->whereIn('status', ['delivered', 'completed'])
            ->where('payment_status', 'paid')
            ->sum('total');

        // Calculate percentage change from yesterday
        $yesterdayRevenue = Order::whereDate('created_at', today()->subDay())
            ->whereIn('status', ['delivered', 'completed'])
            ->where('payment_status', 'paid')
            ->sum('total');

        $percentageChange = $yesterdayRevenue > 0
            ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100
            : 0;

        return Stat::make(__('admin.widgets.stats.today_revenue'), number_format($todayRevenue, 2) . ' ' . __('admin.currency.egp_short'))
            ->description($this->getChangeDescription($percentageChange, __('admin.widgets.stats.vs_yesterday')))
            ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($percentageChange >= 0 ? 'success' : 'danger')
            ->chart($this->getLast7DaysRevenue());
    }

    /**
     * Card 2: New Orders Today
     * Shows count of all orders received today (regardless of status)
     * Clickable to navigate to filtered orders list
     */
    protected function getNewOrdersTodayCard(): Stat
    {
        // Count ALL orders created today (not just pending)
        $newOrdersCount = Order::whereDate('created_at', today())->count();

        // Calculate percentage change from yesterday
        $yesterdayNewOrders = Order::whereDate('created_at', today()->subDay())->count();

        $percentageChange = $yesterdayNewOrders > 0
            ? (($newOrdersCount - $yesterdayNewOrders) / $yesterdayNewOrders) * 100
            : 0;

        return Stat::make(__('admin.widgets.stats.new_orders_today'), $newOrdersCount)
            ->description($this->getChangeDescription($percentageChange, __('admin.widgets.stats.vs_yesterday')))
            ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($percentageChange >= 0 ? 'success' : 'warning')
            ->url(route('filament.admin.resources.orders.index'))
            ->chart($this->getLast7DaysNewOrders());
    }

    /**
     * Card 3: Total Customers
     * Shows total count of registered customers (from customers table)
     */
    protected function getTotalCustomersCard(): Stat
    {
        // Now using Customer model instead of User
        $totalCustomers = Customer::count();

        // Count new customers today
        $newCustomersToday = Customer::whereDate('created_at', today())->count();

        // Count new customers this week
        $newCustomersThisWeek = Customer::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        return Stat::make(__('admin.widgets.stats.total_customers'), number_format($totalCustomers))
            ->description($newCustomersThisWeek . ' ' . __('admin.widgets.stats.new_customers_this_week'))
            ->descriptionIcon('heroicon-m-user-plus')
            ->color('primary')
            ->chart($this->getLast7DaysCustomers());
    }

    /**
     * Card 4: Products in Stock
     * Shows count of active products with stock > 0
     */
    protected function getProductsInStockCard(): Stat
    {
        $productsInStock = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->count();

        // Count low stock products (stock <= low_stock_threshold)
        $lowStockProducts = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->count();

        // Count out of stock products
        $outOfStockProducts = Product::where('status', 'active')
            ->where('stock', '=', 0)
            ->count();

        $description = $lowStockProducts > 0
            ? $lowStockProducts . ' ' . __('admin.widgets.stats.low_stock_products')
            : __('admin.widgets.stats.all_in_stock');

        return Stat::make(__('admin.widgets.stats.products_in_stock'), number_format($productsInStock))
            ->description($description)
            ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
            ->color($lowStockProducts > 0 ? 'warning' : 'success')
            ->extraAttributes([
                'class' => 'cursor-pointer',
            ]);
    }

    /**
     * Get chart data for last 7 days revenue
     */
    protected function getLast7DaysRevenue(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->whereIn('status', ['delivered', 'completed'])
                ->where('payment_status', 'paid')
                ->sum('total');
            $data[] = (float) $revenue;
        }
        return $data;
    }

    /**
     * Get chart data for last 7 days new orders
     */
    protected function getLast7DaysNewOrders(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            // Count ALL orders created on this date (not just pending)
            $count = Order::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

    /**
     * Get chart data for last 7 days new customers
     */
    protected function getLast7DaysCustomers(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            // Now using Customer model instead of User
            $count = Customer::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

    /**
     * Get formatted change description
     */
    protected function getChangeDescription(float $percentage, string $suffix = ''): string
    {
        if ($percentage == 0) {
            return __('admin.widgets.stats.no_change') . ' ' . $suffix;
        }

        $sign = $percentage > 0 ? '+' : '';
        return $sign . number_format($percentage, 1) . '% ' . $suffix;
    }
}
