<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Page;

class ProfilePage extends Page
{
    protected static ?int $navigationSort = 2;

    public function getView(): string
    {
        return 'filament.partners.pages.profile-page';
    }

    public function getLayout(): string
    {
        return 'components.layouts.partners';
    }

    public function getTitle(): string
    {
        return __('messages.partners.nav.profile');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.partners.nav.profile');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false; // سنخفيها من Navigation حالياً لأننا نستخدم Sidebar مخصص
    }
}
