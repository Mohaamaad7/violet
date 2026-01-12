<?php

namespace App\Filament\Resources\Newsletter\NewsletterSubscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('newsletter.subscriber_info'))
                    ->schema([
                        TextInput::make('email')
                            ->label(__('newsletter.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('status')
                            ->label(__('newsletter.status'))
                            ->options([
                                'active' => __('newsletter.active'),
                                'unsubscribed' => __('newsletter.unsubscribed'),
                                'bounced' => __('newsletter.bounced'),
                            ])
                            ->required()
                            ->default('active'),

                        Select::make('source')
                            ->label(__('newsletter.source'))
                            ->options([
                                'footer' => __('newsletter.source_footer'),
                                'contact' => __('newsletter.source_contact'),
                                'popup' => __('newsletter.source_popup'),
                                'checkout' => __('newsletter.source_checkout'),
                            ])
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make(__('newsletter.additional_info'))
                    ->schema([
                        TextInput::make('ip_address')
                            ->label(__('newsletter.ip_address'))
                            ->maxLength(45)
                            ->disabled(),

                        DateTimePicker::make('subscribed_at')
                            ->label(__('newsletter.subscribed_at'))
                            ->default(now()),

                        DateTimePicker::make('unsubscribed_at')
                            ->label(__('newsletter.unsubscribed_at'))
                            ->nullable(),

                        Textarea::make('unsubscribe_reason')
                            ->label(__('newsletter.unsubscribe_reason'))
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
