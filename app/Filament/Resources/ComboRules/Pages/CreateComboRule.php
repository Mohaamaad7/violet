<?php

namespace App\Filament\Resources\ComboRules\Pages;

use App\Filament\Resources\ComboRules\ComboRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComboRule extends CreateRecord
{
    protected static string $resource = ComboRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
