<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Out of Stock Products Widget
 * نفذ من المخزون
 */
class OutOfStockWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 5;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.out_of_stock.heading');
    }

    protected function getStats(): array
    {
        $outOfStockCount = Product::where('status', 'active')
            ->where('stock', '<=', 0)
            ->count();

        $totalProducts = Product::where('status', 'active')->count();

        $percentage = $totalProducts > 0
            ? round(($outOfStockCount / $totalProducts) * 100, 1)
            : 0;

        return [
            Stat::make(__('admin.widgets.out_of_stock.title'), $outOfStockCount)
                ->description($percentage . '% ' . __('admin.widgets.out_of_stock.of_total'))
                ->descriptionIcon($outOfStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($outOfStockCount > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.out-of-stock-products.index')),
        ];
    }
}
