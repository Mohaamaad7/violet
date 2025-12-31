<?php

namespace App\Filament\Widgets;

use App\Enums\ReturnStatus;
use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\OrderReturn;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingReturnsWidget extends StatsOverviewWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $pendingReturns = OrderReturn::where('status', ReturnStatus::PENDING)->count();
        $approvedReturns = OrderReturn::where('status', ReturnStatus::APPROVED)->count();
        $totalReturnsThisMonth = OrderReturn::whereMonth('created_at', now()->month)->count();

        return [
            Stat::make(__('inventory.pending_returns'), $pendingReturns)
                ->description(__('inventory.awaiting_approval'))
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingReturns > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.order-returns.index', ['tableFilters[status][values][0]' => 'pending']))
                ->chart([2, 3, $pendingReturns, $pendingReturns, 1, 2, $pendingReturns]),

            Stat::make(__('inventory.approved_returns'), $approvedReturns)
                ->description(__('inventory.ready_to_process'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info')
                ->url(route('filament.admin.resources.order-returns.index', ['tableFilters[status][values][0]' => 'approved'])),

            Stat::make(__('inventory.returns_this_month'), $totalReturnsThisMonth)
                ->description(__('inventory.all_statuses'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('gray')
                ->chart([5, 8, 12, 15, 18, 20, $totalReturnsThisMonth]),
        ];
    }
}
