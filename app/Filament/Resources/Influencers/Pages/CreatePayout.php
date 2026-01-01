<?php

namespace App\Filament\Resources\Influencers\Pages;

use App\Filament\Resources\Influencers\CommissionPayoutResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayout extends CreateRecord
{
    protected static string $resource = CommissionPayoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'pending';
        return $data;
    }
}
