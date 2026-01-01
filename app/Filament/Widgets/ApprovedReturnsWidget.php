<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\OrderReturn;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Approved Returns Widget
 * المرتجعات الموافق عليها
 */
class ApprovedReturnsWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 6;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.approved_returns.heading');
    }

    protected function getStats(): array
    {
        $approvedCount = OrderReturn::where('status', 'approved')->count();

        $approvedToday = OrderReturn::where('status', 'approved')
            ->whereDate('updated_at', today())
            ->count();

        return [
            Stat::make(__('admin.widgets.approved_returns.title'), $approvedCount)
                ->description($approvedToday . ' ' . __('admin.widgets.approved_returns.today'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
