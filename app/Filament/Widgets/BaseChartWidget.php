<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use Filament\Widgets\ChartWidget;

/**
 * Base Chart Widget Class
 * 
 * All new Chart widgets should extend this class.
 * This automatically applies role-based visibility control.
 * 
 * Usage:
 * class MyNewChartWidget extends BaseChartWidget
 * {
 *     protected function getData(): array
 *     {
 *         return [...];
 *     }
 *     
 *     protected function getType(): string
 *     {
 *         return 'line'; // or 'bar', 'pie', etc.
 *     }
 * }
 */
abstract class BaseChartWidget extends ChartWidget
{
    use ChecksWidgetVisibility;
}
