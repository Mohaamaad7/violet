<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use Filament\Widgets\StatsOverviewWidget;

/**
 * Base Stats Widget Class
 * 
 * All new Stats widgets should extend this class.
 * This automatically applies role-based visibility control.
 * 
 * Usage:
 * class MyNewStatsWidget extends BaseStatsWidget
 * {
 *     protected function getStats(): array
 *     {
 *         return [...];
 *     }
 * }
 */
abstract class BaseStatsWidget extends StatsOverviewWidget
{
    use ChecksWidgetVisibility;
}
