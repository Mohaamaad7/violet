<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Current Orders Widget
 * الطلبات الحالية (قيد التنفيذ)
 */
class CurrentOrdersWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 8;

    protected ?string $pollingInterval = '30s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.current_orders.heading');
    }

    protected function getStats(): array
    {
        // Orders in progress (not delivered, not cancelled)
        $pendingOrders = Order::where('status', OrderStatus::PENDING)->count();
        $processingOrders = Order::where('status', OrderStatus::PROCESSING)->count();
        $shippedOrders = Order::where('status', OrderStatus::SHIPPED)->count();

        $totalCurrent = $pendingOrders + $processingOrders + $shippedOrders;

        return [
            Stat::make(__('admin.widgets.current_orders.title'), $totalCurrent)
                ->description(
                    $pendingOrders . ' ' . __('admin.widgets.current_orders.pending') . ' | ' .
                    $processingOrders . ' ' . __('admin.widgets.current_orders.processing') . ' | ' .
                    $shippedOrders . ' ' . __('admin.widgets.current_orders.shipped')
                )
                ->descriptionIcon('heroicon-m-truck')
                ->color('info')
                ->url(route('filament.admin.resources.orders.index')),
        ];
    }
}
