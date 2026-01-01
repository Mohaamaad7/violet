<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Total Stock Units Widget
 * اجمالي وحدات المخزون
 */
class TotalStockUnitsWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 10;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.total_stock_units.heading');
    }

    protected function getStats(): array
    {
        $totalUnits = Product::where('status', 'active')->sum('stock');

        $totalProducts = Product::where('status', 'active')->count();

        $avgUnitsPerProduct = $totalProducts > 0
            ? round($totalUnits / $totalProducts, 1)
            : 0;

        return [
            Stat::make(__('admin.widgets.total_stock_units.title'), number_format($totalUnits))
                ->description($avgUnitsPerProduct . ' ' . __('admin.widgets.total_stock_units.avg_per_product'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
        ];
    }
}
