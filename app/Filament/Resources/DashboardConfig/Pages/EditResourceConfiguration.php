<?php

namespace App\Filament\Resources\DashboardConfig\Pages;

use App\Filament\Resources\DashboardConfig\ResourceConfigurationResource;
use Filament\Resources\Pages\EditRecord;

class EditResourceConfiguration extends EditRecord
{
    protected static string $resource = ResourceConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
