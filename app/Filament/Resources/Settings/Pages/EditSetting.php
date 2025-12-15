<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Populate image_value from value for image type
        if (isset($data['type']) && $data['type'] === 'image' && isset($data['value'])) {
            $data['image_value'] = is_array($data['value']) ? $data['value'] : [$data['value']];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle image_value -> value mapping for image type
        if (isset($data['image_value'])) {
            $path = is_array($data['image_value']) ? ($data['image_value'][0] ?? null) : $data['image_value'];

            // Delete old file if exists and different
            $oldPath = $this->record->getRawOriginal('value');
            if ($oldPath && $path !== $oldPath && str_starts_with($oldPath, 'images/logos/')) {
                \Illuminate\Support\Facades\Storage::disk('public_dir')->delete($oldPath);
            }

            $data['value'] = $path;
            unset($data['image_value']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
