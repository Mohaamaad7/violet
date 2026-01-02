<?php

namespace App\Filament\Partners\Pages;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    /**
     * Custom heading for partners login
     */
    public function getHeading(): string|Htmlable
    {
        return __('messages.partners.login.heading');
    }

    /**
     * Custom subheading
     */
    public function getSubheading(): string|Htmlable|null
    {
        return __('messages.partners.login.subheading');
    }

    /**
     * Customize the email field
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('messages.partners.login.email'))
            ->email()
            ->required()
            ->autocomplete('email')
            ->autofocus()
            ->placeholder('influencer@example.com');
    }

    /**
     * Customize the password field
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('messages.partners.login.password'))
            ->password()
            ->required()
            ->revealable()
            ->autocomplete('current-password');
    }
}
