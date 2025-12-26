<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->description('بيانات المدينة الأساسية')
                    ->schema([
                        Select::make('governorate_id')
                            ->label('المحافظة')
                            ->relationship('governorate', 'name_ar')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        TextInput::make('name_ar')
                            ->label('الاسم بالعربية')
                            ->required()
                            ->maxLength(100),
                        
                        TextInput::make('name_en')
                            ->label('الاسم بالإنجليزية')
                            ->required()
                            ->maxLength(100),
                    ])->columns(3),

                Section::make('إعدادات الشحن (اختياري)')
                    ->description('يمكن تحديد تكلفة شحن مخصصة للمدينة. إذا تُركت فارغة، سيتم استخدام تكلفة المحافظة.')
                    ->schema([
                        TextInput::make('shipping_cost')
                            ->label('تكلفة شحن مخصصة')
                            ->helperText('اتركها فارغة لاستخدام تكلفة المحافظة الافتراضية')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('ج.م')
                            ->step(0.01),
                    ])->columns(1),

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
