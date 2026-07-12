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

    public static bool $formActionsAreSticky = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    public static function getNavigationLabel(): string
    {
        return 'إعدادات الاتصال';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة المحتوى';
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public function getTitle(): string
    {
        return 'إعدادات الاتصال';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('معلومات الاتصال')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\TextInput::make('working_hours')
                            ->label('ساعات العمل')
                            ->placeholder('e.g. Sun - Thu: 9 AM - 5 PM')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('show_map')
                            ->label('إظهار الخريطة في صفحة اتصل بنا')
                            ->helperText('تفعيل أو تعطيل ظهور الخريطة في صفحة اتصل بنا'),
                    ])
                    ->columns(2),

                Components\Section::make('روابط التواصل الاجتماعي')
                    ->schema([
                        Forms\Components\Repeater::make('social_links')
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('platform')
                                    ->label('المنصة')
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
                                    ->label('الرابط')
                                    ->url()
                                    ->required()
                                    ->maxLength(500)
                                    ->placeholder('https://...'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('إضافة رابط')
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
