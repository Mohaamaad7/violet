<?php

namespace App\Filament\Resources\Governorates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GovernorateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->description('بيانات المحافظة الأساسية')
                    ->schema([
                        Select::make('country_id')
                            ->label('الدولة')
                            ->relationship('country', 'name_ar')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => \App\Models\Country::where('code', 'EG')->first()?->id),
                        
                        TextInput::make('name_ar')
                            ->label('الاسم بالعربية')
                            ->required()
                            ->maxLength(100),
                        
                        TextInput::make('name_en')
                            ->label('الاسم بالإنجليزية')
                            ->required()
                            ->maxLength(100),
                    ])->columns(3),

                Section::make('إعدادات الشحن')
                    ->description('تكلفة الشحن ووقت التوصيل')
                    ->schema([
                        TextInput::make('shipping_cost')
                            ->label('تكلفة الشحن')
                            ->helperText('السعر الافتراضي للشحن لهذه المحافظة')
                            ->required()
                            ->numeric()
                            ->default(50.00)
                            ->minValue(0)
                            ->suffix('ج.م')
                            ->step(0.01),
                        
                        TextInput::make('delivery_days')
                            ->label('أيام التوصيل المتوقعة')
                            ->helperText('عدد الأيام اللازمة للتوصيل')
                            ->required()
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->maxValue(30)
                            ->suffix('يوم'),
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
