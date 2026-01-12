<?php

namespace App\Filament\Resources\Newsletter\NewsletterSubscriptions\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class NewsletterSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات المشترك')
                    ->schema([
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'unsubscribed' => 'ألغى الاشتراك',
                                'bounced' => 'فشل التوصيل',
                            ])
                            ->required()
                            ->default('active'),

                        Select::make('source')
                            ->label('المصدر')
                            ->options([
                                'footer' => 'Footer',
                                'contact' => 'صفحة الاتصال',
                                'popup' => 'نافذة منبثقة',
                                'checkout' => 'صفحة الدفع',
                            ])
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('معلومات إضافية')
                    ->schema([
                        TextInput::make('ip_address')
                            ->label('عنوان IP')
                            ->maxLength(45)
                            ->disabled(),

                        DateTimePicker::make('subscribed_at')
                            ->label('تاريخ الاشتراك')
                            ->default(now()),

                        DateTimePicker::make('unsubscribed_at')
                            ->label('تاريخ إلغاء الاشتراك')
                            ->nullable(),

                        Textarea::make('unsubscribe_reason')
                            ->label('سبب إلغاء الاشتراك')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
