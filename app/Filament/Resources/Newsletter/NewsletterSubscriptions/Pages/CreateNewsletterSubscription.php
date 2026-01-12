<?php

namespace App\Filament\Resources\Newsletter\NewsletterSubscriptions\Pages;

use App\Filament\Resources\Newsletter\NewsletterSubscriptions\NewsletterSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterSubscription extends CreateRecord
{
    protected static string $resource = NewsletterSubscriptionResource::class;
}
