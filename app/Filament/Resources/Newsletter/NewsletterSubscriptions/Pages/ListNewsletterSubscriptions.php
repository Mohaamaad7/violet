<?php

namespace App\Filament\Resources\Newsletter\NewsletterSubscriptions\Pages;

use App\Filament\Resources\Newsletter\NewsletterSubscriptions\NewsletterSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterSubscriptions extends ListRecords
{
    protected static string $resource = NewsletterSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
