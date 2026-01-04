<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Page;

class PayoutsPage extends Page
{
    protected static ?int $navigationSort = 5;

    public function getView(): string
    {
        return 'filament.partners.pages.payouts-page';
    }

    public function getTitle(): string
    {
        return __('messages.partners.nav.payouts');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.partners.nav.payouts');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
