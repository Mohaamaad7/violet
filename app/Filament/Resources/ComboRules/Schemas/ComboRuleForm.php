<?php

namespace App\Filament\Resources\ComboRules\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class ComboRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Section::make('معلومات العرض الأساسية')
                        ->schema([
                            TextInput::make('name')
                                ->label('اسم العرض (الكومبو)')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label('وصف العرض')
                                ->maxLength(65535)
                                ->columnSpanFull(),
                            Toggle::make('is_active')
                                ->label('مفعل')
                                ->default(true),
                            TextInput::make('discount_percentage')
                                ->label('نسبة الخصم (%)')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(100),
                            TextInput::make('max_uses_per_user')
                                ->label('الحد الأقصى للاستخدام لكل مستخدم')
                                ->numeric()
                                ->placeholder('اتركه فارغاً للاستخدام غير المحدود'),
                            TextInput::make('priority')
                                ->label('الأولوية')
                                ->numeric()
                                ->default(0)
                                ->helperText('الرقم الأعلى يعني أولوية أعلى عند تطبيق العروض'),
                            DateTimePicker::make('starts_at')
                                ->label('تاريخ بداية العرض'),
                            DateTimePicker::make('ends_at')
                                ->label('تاريخ نهاية العرض'),
                        ])->columnSpan(2),
                        
                    Section::make('شروط العرض')
                        ->schema([
                            Repeater::make('conditions')
                                ->relationship('conditions')
                                ->label('أصناف العرض')
                                ->schema([
                                    Select::make('category_id')
                                        ->label('القسم')
                                        ->relationship('category', 'name_ar')
                                        ->required()
                                        ->searchable(),
                                    TextInput::make('required_quantity')
                                        ->label('الكمية المطلوبة')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1),
                                ])
                                ->addActionLabel('إضافة شرط جديد')
                                ->columns(1)
                                ->required()
                                ->minItems(2)
                        ])->columnSpan(1),
                ])
            ]);
    }
}
