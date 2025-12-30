<?php

namespace App\Filament\Resources\DashboardConfig\Pages;

use App\Filament\Resources\DashboardConfig\NavigationGroupConfigurationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNavigationGroupConfiguration extends CreateRecord
{
    protected static string $resource = NavigationGroupConfigurationResource::class;
}
