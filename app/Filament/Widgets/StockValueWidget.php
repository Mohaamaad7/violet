<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StockValueWidget extends StatsOverviewWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        // Total stock value (price * stock)
        $totalValue = Product::where('status', 'active')
            ->selectRaw('SUM(price * stock) as total')
            ->value('total') ?? 0;

        // Total cost value (cost_price * stock)
        $totalCost = Product::where('status', 'active')
            ->whereNotNull('cost_price')
            ->selectRaw('SUM(cost_price * stock) as total')
            ->value('total') ?? 0;

        // Potential profit
        $potentialProfit = $totalValue - $totalCost;

        // Total stock units
        $totalUnits = Product::where('status', 'active')
            ->sum('stock');

        return [
            Stat::make(__('inventory.total_stock_value'), 'EGP ' . number_format($totalValue, 2))
                ->description(__('inventory.current_stock_value'))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart([50000, 60000, 55000, 70000, 65000, 75000, $totalValue]),

            Stat::make(__('inventory.potential_profit'), 'EGP ' . number_format($potentialProfit, 2))
                ->description(__('inventory.if_all_sold'))
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color($potentialProfit > 0 ? 'success' : 'danger')
                ->chart([10000, 15000, 12000, 18000, 20000, 22000, $potentialProfit]),

            Stat::make(__('inventory.total_stock_units'), number_format($totalUnits))
                ->description(__('inventory.all_products'))
                ->descriptionIcon('heroicon-o-cube')
                ->color('info')
                ->chart([500, 600, 550, 700, 650, 750, $totalUnits]),
        ];
    }
}
