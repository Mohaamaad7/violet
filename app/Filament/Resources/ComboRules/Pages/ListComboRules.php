<?php

namespace App\Filament\Resources\ComboRules\Pages;

use App\Filament\Resources\ComboRules\ComboRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComboRules extends ListRecords
{
    protected static string $resource = ComboRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
