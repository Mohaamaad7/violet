<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Storage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الإعداد')
                    ->schema([
                        TextInput::make('key')
                            ->label('المفتاح')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn($record) => $record !== null)
                            ->helperText('المفتاح الفريد للإعداد (لا يمكن تعديله بعد الإنشاء)'),

                        TextInput::make('display_name')
                            ->label('الاسم المعروض')
                            ->required()
                            ->maxLength(255)
                            ->helperText('الاسم الذي يظهر في واجهة المستخدم'),

                        Select::make('group')
                            ->label('المجموعة')
                            ->options([
                                'general' => 'عام',
                                'returns' => 'المرتجعات',
                                'orders' => 'الطلبات',
                                'products' => 'المنتجات',
                                'payments' => 'الدفع',
                                'shipping' => 'الشحن',
                                'emails' => 'البريد الإلكتروني',
                                'notifications' => 'الإشعارات',
                                'seo' => 'تحسين محركات البحث',
                                'social' => 'وسائل التواصل',
                                'analytics' => 'التحليلات',
                                'security' => 'الأمان',
                            ])
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->default('general')
                            ->helperText('المجموعة التي ينتمي إليها الإعداد'),

                        Select::make('type')
                            ->label('نوع البيانات')
                            ->options([
                                'string' => 'نص',
                                'text' => 'نص طويل',
                                'integer' => 'رقم صحيح',
                                'decimal' => 'رقم عشري',
                                'boolean' => 'صح/خطأ',
                                'json' => 'JSON',
                                'array' => 'مصفوفة',
                                'image' => 'صورة',
                            ])
                            ->required()
                            ->native(false)
                            ->default('string')
                            ->reactive()
                            ->helperText('نوع البيانات المخزنة'),

                        FileUpload::make('image_value')
                            ->label('الصورة')
                            ->disk('public_dir')
                            ->directory('images/logos')
                            ->image()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->default([])
                            ->visible(fn($get) => $get('type') === 'image')
                            ->helperText('ارفع صورة اللوجو. الحد الأقصى: 2 ميجابايت')
                            ->columnSpanFull(),

                        Textarea::make('value')
                            ->label('القيمة')
                            ->rows(3)
                            ->maxLength(65535)
                            ->visible(fn($get) => $get('type') !== 'boolean' && $get('type') !== 'image')
                            ->helperText('قيمة الإعداد')
                            ->columnSpanFull(),

                        Toggle::make('value')
                            ->label('القيمة')
                            ->visible(fn($get) => $get('type') === 'boolean')
                            ->helperText('تفعيل/تعطيل')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->helperText('وصف توضيحي للإعداد'),

                        Toggle::make('is_public')
                            ->label('عام')
                            ->default(false)
                            ->helperText('هل يمكن الوصول لهذا الإعداد من الواجهة الأمامية؟'),
                    ])
                    ->columns(2),
            ]);
    }
}
