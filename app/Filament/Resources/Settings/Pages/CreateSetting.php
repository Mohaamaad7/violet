<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle image_value -> value mapping
        if (isset($data['image_value'])) {
            $data['value'] = is_array($data['image_value']) ? ($data['image_value'][0] ?? null) : $data['image_value'];
            unset($data['image_value']);
        }

        return $data;
    }
}
