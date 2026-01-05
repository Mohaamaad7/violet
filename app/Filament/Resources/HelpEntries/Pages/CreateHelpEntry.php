<?php

namespace App\Filament\Resources\HelpEntries\Pages;

use App\Filament\Resources\HelpEntries\HelpEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHelpEntry extends CreateRecord
{
    protected static string $resource = HelpEntryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
