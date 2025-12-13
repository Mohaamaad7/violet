<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class StockMovementsChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '7';

    public function getHeading(): ?string
    {
        return __('inventory.stock_movements_chart');
    }

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $startDate = now()->subDays($days)->startOfDay();

        // Get movements grouped by type and date
        $movements = StockMovement::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, type, SUM(ABS(quantity)) as total')
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        // Prepare date labels
        $labels = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels->push(now()->subDays($i)->format('M d'));
        }

        // Prepare datasets for each type
        $types = ['restock', 'sale', 'return', 'adjustment'];
        $datasets = [];

        foreach ($types as $type) {
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $value = $movements->where('date', $date)
                    ->where('type', $type)
                    ->first()?->total ?? 0;
                $data[] = $value;
            }

            $datasets[] = [
                'label' => __('inventory.' . $type),
                'data' => $data,
                'borderColor' => match($type) {
                    'restock' => 'rgb(34, 197, 94)',
                    'sale' => 'rgb(59, 130, 246)',
                    'return' => 'rgb(234, 179, 8)',
                    'adjustment' => 'rgb(156, 163, 175)',
                },
                'backgroundColor' => match($type) {
                    'restock' => 'rgba(34, 197, 94, 0.1)',
                    'sale' => 'rgba(59, 130, 246, 0.1)',
                    'return' => 'rgba(234, 179, 8, 0.1)',
                    'adjustment' => 'rgba(156, 163, 175, 0.1)',
                },
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => __('inventory.last_7_days'),
            '14' => __('inventory.last_14_days'),
            '30' => __('inventory.last_30_days'),
        ];
    }
}
