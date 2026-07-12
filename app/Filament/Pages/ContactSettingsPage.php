<?php

namespace App\Filament\Pages;

use App\Settings\ContactSettings;
use BackedEnum;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components;
use Filament\Support\Icons\Heroicon;
use Spatie\ResponseCache\Facades\ResponseCache;

class ContactSettingsPage extends SettingsPage
{
    protected static string $settings = ContactSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    public static function getNavigationLabel(): string
    {
        return __('admin.contact_settings', ['default' => 'Contact Settings']);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.settings', ['default' => 'Settings']);
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public function getTitle(): string
    {
        return __('admin.contact_settings', ['default' => 'Contact Settings']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make(__('admin.contact_info', ['default' => 'Contact Information']))
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label(__('admin.phone', ['default' => 'Phone']))
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label(__('admin.email', ['default' => 'Email']))
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->label(__('admin.address', ['default' => 'Address']))
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\TextInput::make('working_hours')
                            ->label(__('admin.working_hours', ['default' => 'Working Hours']))
                            ->placeholder('e.g. Sun - Thu: 9 AM - 5 PM')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('show_map')
                            ->label(__('admin.show_map', ['default' => 'Show Map on Contact Page']))
                            ->helperText(__('admin.show_map_helper', ['default' => 'Toggle the embedded map visibility on the contact page.'])),
                    ])
                    ->columns(2),

                Components\Section::make(__('admin.social_links', ['default' => 'Social Media Links']))
                    ->schema([
                        Forms\Components\Repeater::make('social_links')
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('platform')
                                    ->label(__('admin.platform', ['default' => 'Platform']))
                                    ->options([
                                        'facebook' => 'Facebook',
                                        'instagram' => 'Instagram',
                                        'twitter' => 'X (Twitter)',
                                        'tiktok' => 'TikTok',
                                        'youtube' => 'YouTube',
                                        'whatsapp' => 'WhatsApp',
                                        'telegram' => 'Telegram',
                                        'snapchat' => 'Snapchat',
                                        'linkedin' => 'LinkedIn',
                                        'pinterest' => 'Pinterest',
                                    ])
                                    ->required()
                                    ->searchable(),

                                Forms\Components\TextInput::make('url')
                                    ->label(__('admin.url', ['default' => 'URL']))
                                    ->url()
                                    ->required()
                                    ->maxLength(500)
                                    ->placeholder('https://...'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel(__('admin.add_social_link', ['default' => 'Add Social Link']))
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['platform'] ?? null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Clear the response cache after saving contact settings.
     * This ensures the frontend (Header, Footer, Contact Page) immediately
     * reflects the updated data without stale cached responses.
     */
    protected function afterSave(): void
    {
        ResponseCache::clear();
    }
}
