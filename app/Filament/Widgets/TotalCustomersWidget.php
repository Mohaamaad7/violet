<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Total Customers Widget
 * إجمالي العملاء
 */
class TotalCustomersWidget extends BaseWidget
{
    use ChecksWidgetVisibility;

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('admin.widgets.total_customers.heading');
    }

    protected function getStats(): array
    {
        $totalCustomers = Customer::count();

        $newCustomersThisWeek = Customer::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();

        return [
            Stat::make(__('admin.widgets.total_customers.title'), number_format($totalCustomers))
                ->description($newCustomersThisWeek . ' ' . __('admin.widgets.total_customers.new_this_week'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary')
                ->chart($this->getLast7DaysCustomers()),
        ];
    }

    protected function getLast7DaysCustomers(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = Customer::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }
}
