<?php

namespace App\Filament\Widgets;

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
    /**
     * Widget heading (non-static in ChartWidget)
     */
    protected ?string $heading = 'مبيعات';

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
            '7days' => 'آخر 7 أيام',
            '30days' => 'آخر 30 يوم',
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
            ->whereIn('status', ['delivered', 'completed'])
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
        $labels = $dates->map(function (Carbon $date) use ($days) {
            if ($days === 7) {
                // Show day name for 7 days (e.g., "السبت")
                return $date->locale('ar')->dayName;
            } else {
                // Show date for 30 days (e.g., "1 نوفمبر")
                return $date->locale('ar')->format('j M');
            }
        });

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات',
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
            ? 'إجمالي الإيرادات من الطلبات المكتملة والمدفوعة خلال آخر 30 يوم'
            : 'إجمالي الإيرادات من الطلبات المكتملة والمدفوعة خلال آخر 7 أيام';
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
                        'callback' => 'function(value) { return value.toLocaleString("ar-EG") + " ج.م"; }',
                    ],
                ],
            ],
        ];
    }
}
