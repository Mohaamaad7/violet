<?php

namespace App\Filament\Resources\DashboardConfig\Pages;

use App\Filament\Resources\DashboardConfig\NavigationGroupConfigurationResource;
use Filament\Resources\Pages\EditRecord;

class EditNavigationGroupConfiguration extends EditRecord
{
    protected static string $resource = NavigationGroupConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
