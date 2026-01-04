<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Page;

class CommissionsPage extends Page
{
    protected static ?int $navigationSort = 3;

    public function getView(): string
    {
        return 'filament.partners.pages.commissions-page';
    }

    public function getLayout(): string
    {
        return 'components.layouts.partners';
    }

    public function getTitle(): string
    {
        return __('messages.partners.nav.commissions');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.partners.nav.commissions');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
