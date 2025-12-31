<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use Filament\Widgets\TableWidget;

/**
 * Base Table Widget Class
 * 
 * All new Table widgets should extend this class.
 * This automatically applies role-based visibility control.
 * 
 * Usage:
 * class MyNewTableWidget extends BaseTableWidget
 * {
 *     public function table(Table $table): Table
 *     {
 *         return $table->query(...)->columns([...]);
 *     }
 * }
 */
abstract class BaseTableWidget extends TableWidget
{
    use ChecksWidgetVisibility;
}
