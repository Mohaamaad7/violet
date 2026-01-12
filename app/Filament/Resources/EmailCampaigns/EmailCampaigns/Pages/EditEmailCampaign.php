<?php

namespace App\Filament\Resources\EmailCampaigns\EmailCampaigns\Pages;

use App\Filament\Resources\EmailCampaigns\EmailCampaigns\EmailCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailCampaign extends EditRecord
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
