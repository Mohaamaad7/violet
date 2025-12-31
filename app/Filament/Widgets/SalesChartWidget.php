<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Sales Chart Widget
 * 
 * Displays a line chart showing revenue trends over time.
 * Source: https://filamentphp.com/docs/4.x/widgets/charts
 */
class SalesChartWidget extends ChartWidget
{
    use ChecksWidgetVisibility;
    /**
     * Widget heading (non-static in ChartWidget)
     */
    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('admin.widgets.sales.heading');
    }

    /**
     * Widget sort order (static in Widget base class)
     */
    protected static ?int $sort = 3;

    /**
     * Chart color (amber - project primary color)
     * Source: https://filamentphp.com/docs/4.x/widgets/charts#customizing-the-chart-color
     */
    protected string $color = 'warning'; // warning = amber in Filament

    /**
     * Default filter value
     * Source: https://filamentphp.com/docs/4.x/widgets/charts#filtering-chart-data
     */
    public ?string $filter = '7days';

    /**
     * Get available filters
     * 
     * @return array<string, string>|null
     */
    protected function getFilters(): ?array
    {
        return [
            '7days' => __('admin.widgets.sales.filters.7days'),
            '30days' => __('admin.widgets.sales.filters.30days'),
        ];
    }

    /**
     * Get chart data
     * 
     * Returns data structure compatible with Chart.js line chart.
     * Source: https://filamentphp.com/docs/4.x/widgets/charts#introduction
     * Chart.js Line Chart: https://www.chartjs.org/docs/latest/charts/line
     * 
     * @return array
     */
    protected function getData(): array
    {
        $days = $this->filter === '30days' ? 30 : 7;

        // Generate array of dates from oldest to newest (today)
        $dates = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates->push(now()->subDays($i)->startOfDay());
        }

        // Get revenue data grouped by date
        $revenueData = Order::query()
            ->whereIn('status', [OrderStatus::DELIVERED])
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [
                now()->subDays($days - 1)->startOfDay(),
                now()->endOfDay(),
            ])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        // Map dates to revenue (0 if no sales that day)
        $chartData = $dates->map(function (Carbon $date) use ($revenueData) {
            $dateKey = $date->format('Y-m-d');
            return $revenueData->get($dateKey, 0);
        });

        // Format labels based on filter
        $currentLocale = app()->getLocale();
        $labels = $dates->map(function (Carbon $date) use ($days, $currentLocale) {
            if ($days === 7) {
                return $date->locale($currentLocale)->dayName;
            }
            return $date->locale($currentLocale)->format('j M');
        });

        return [
            'datasets' => [
                [
                    'label' => __('admin.widgets.sales.dataset_label'),
                    'data' => $chartData->toArray(),
                    'fill' => 'start', // Fill area under line
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    /**
     * Get chart type
     * 
     * @return string
     */
    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Get chart description
     * 
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->filter === '30days'
            ? __('admin.widgets.sales.desc_30days')
            : __('admin.widgets.sales.desc_7days');
    }

    /**
     * Get chart options
     * 
     * Customize Chart.js options for better appearance.
     * Source: https://filamentphp.com/docs/4.x/widgets/charts#setting-chart-configuration-options
     * Chart.js Options: https://www.chartjs.org/docs/latest/configuration
     * 
     * @return array|null
     */
    protected function getOptions(): ?array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar-EG' : 'en-US';
        $currencyShort = __('admin.currency.egp_short');

        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return value.toLocaleString("' . $locale . '") + " ' . $currencyShort . '"; }',
                    ],
                ],
            ],
        ];
    }
}
