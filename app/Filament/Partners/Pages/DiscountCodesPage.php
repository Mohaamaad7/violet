<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Page;

class DiscountCodesPage extends Page
{
    protected static ?int $navigationSort = 4;

    public function getView(): string
    {
        return 'filament.partners.pages.discount-codes-page';
    }

    public function getTitle(): string
    {
        return __('messages.partners.nav.discount_codes');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.partners.nav.discount_codes');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
