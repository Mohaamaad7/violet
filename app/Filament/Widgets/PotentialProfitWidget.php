<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Potential Profit Widget
 * الربح المحتمل
 */
class PotentialProfitWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 9;

    protected ?string $pollingInterval = '300s'; // Every 5 minutes

    public function getHeading(): ?string
    {
        return __('admin.widgets.potential_profit.heading');
    }

    protected function getStats(): array
    {
        // Calculate potential profit = (selling price - cost price) * stock
        $products = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->whereNotNull('cost_price')
            ->get();

        $potentialProfit = 0;
        $totalCostValue = 0;
        $totalSaleValue = 0;

        foreach ($products as $product) {
            $costPrice = $product->cost_price ?? 0;
            $salePrice = $product->price ?? 0;
            $stock = $product->stock ?? 0;

            $totalCostValue += $costPrice * $stock;
            $totalSaleValue += $salePrice * $stock;
            $potentialProfit += ($salePrice - $costPrice) * $stock;
        }

        $profitMargin = $totalSaleValue > 0
            ? round(($potentialProfit / $totalSaleValue) * 100, 1)
            : 0;

        return [
            Stat::make(__('admin.widgets.potential_profit.title'), number_format($potentialProfit, 2) . ' ' . __('admin.currency.egp_short'))
                ->description($profitMargin . '% ' . __('admin.widgets.potential_profit.margin'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
