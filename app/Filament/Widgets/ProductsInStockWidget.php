<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Products In Stock Widget
 * منتجات متاحة
 */
class ProductsInStockWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 4;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.products_in_stock.heading');
    }

    protected function getStats(): array
    {
        $productsInStock = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->count();

        $lowStockProducts = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->count();

        $description = $lowStockProducts > 0
            ? $lowStockProducts . ' ' . __('admin.widgets.products_in_stock.low_stock')
            : __('admin.widgets.products_in_stock.all_in_stock');

        return [
            Stat::make(__('admin.widgets.products_in_stock.title'), number_format($productsInStock))
                ->description($description)
                ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
        ];
    }
}
