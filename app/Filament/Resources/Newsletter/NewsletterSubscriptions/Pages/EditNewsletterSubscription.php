<?php

namespace App\Filament\Resources\Newsletter\NewsletterSubscriptions\Pages;

use App\Filament\Resources\Newsletter\NewsletterSubscriptions\NewsletterSubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterSubscription extends EditRecord
{
    protected static string $resource = NewsletterSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
