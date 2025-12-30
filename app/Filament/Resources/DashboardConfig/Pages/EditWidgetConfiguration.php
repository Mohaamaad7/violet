<?php

namespace App\Filament\Resources\DashboardConfig\Pages;

use App\Filament\Resources\DashboardConfig\WidgetConfigurationResource;
use Filament\Resources\Pages\EditRecord;

class EditWidgetConfiguration extends EditRecord
{
    protected static string $resource = WidgetConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
