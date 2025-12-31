<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ChecksWidgetVisibility;
use Filament\Widgets\Widget;

/**
 * Base Widget Class
 * 
 * All new Filament Widgets should extend this class instead of Widget.
 * This automatically applies role-based visibility control.
 * 
 * Usage:
 * class MyNewWidget extends BaseWidget
 * {
 *     // Your widget code
 * }
 */
abstract class BaseWidget extends Widget
{
    use ChecksWidgetVisibility;
}
