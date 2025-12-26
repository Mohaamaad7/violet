<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->description('بيانات الدولة الأساسية')
                    ->schema([
                        TextInput::make('name_ar')
                            ->label('الاسم بالعربية')
                            ->required()
                            ->maxLength(100),
                        
                        TextInput::make('name_en')
                            ->label('الاسم بالإنجليزية')
                            ->required()
                            ->maxLength(100),
                        
                        TextInput::make('code')
                            ->label('رمز الدولة')
                            ->helperText('كود ISO من حرفين (مثال: EG)')
                            ->required()
                            ->maxLength(2)
                            ->unique(ignoreRecord: true),
                    ])->columns(3),

                Section::make('الاتصال والعملة')
                    ->description('معلومات الاتصال والعملة')
                    ->schema([
                        TextInput::make('phone_code')
                            ->label('كود الهاتف')
                            ->helperText('مثال: +20')
                            ->tel()
                            ->required()
                            ->maxLength(10)
                            ->prefix('+'),
                        
                        TextInput::make('currency_code')
                            ->label('رمز العملة')
                            ->helperText('كود ISO من 3 أحرف (مثال: EGP)')
                            ->required()
                            ->maxLength(3)
                            ->default('EGP'),
                    ])->columns(2),

                Section::make('الإعدادات')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                        
                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])->columns(2),
            ]);
    }
}
