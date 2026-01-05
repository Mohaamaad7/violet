<?php

namespace App\Filament\Resources\HelpEntries\Pages;

use App\Filament\Resources\HelpEntries\HelpEntryResource;
use Filament\Resources\Pages\ListRecords;

class ListHelpEntries extends ListRecords
{
    protected static string $resource = HelpEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
