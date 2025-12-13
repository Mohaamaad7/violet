<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class LowStockAlertWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get products where stock > 0 AND stock <= low_stock_threshold (Low Stock)
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('stock', '>', 0)
            ->where('status', 'active')
            ->count();

        // Get critical stock (stock = 0) (Out of Stock)
        $outOfStock = Product::where('stock', 0)
            ->where('status', 'active')
            ->count();

        return [
            Stat::make(__('inventory.low_stock_products'), $lowStockProducts)
                ->description(__('inventory.products_below_threshold'))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.low-stock-products.index'))
                ->chart([7, 4, 5, 2, 3, $lowStockProducts, $lowStockProducts]),

            Stat::make(__('inventory.out_of_stock'), $outOfStock)
                ->description(__('inventory.products_need_restock'))
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($outOfStock > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.out-of-stock-products.index'))
                ->chart([3, 2, 1, 2, 1, $outOfStock, $outOfStock]),
        ];
    }
}
