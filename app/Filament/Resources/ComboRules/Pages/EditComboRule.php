<?php

namespace App\Filament\Resources\ComboRules\Pages;

use App\Filament\Resources\ComboRules\ComboRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditComboRule extends EditRecord
{
    protected static string $resource = ComboRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
