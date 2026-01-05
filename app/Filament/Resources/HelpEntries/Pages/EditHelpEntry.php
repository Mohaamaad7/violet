<?php

namespace App\Filament\Resources\HelpEntries\Pages;

use App\Filament\Resources\HelpEntries\HelpEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpEntry extends EditRecord
{
    protected static string $resource = HelpEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
