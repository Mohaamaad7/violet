<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // 1. Template Information (عمود واحد)
                Section::make('معلومات القالب')
                    ->description('البيانات الأساسية للقالب')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم القالب')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => 
                                $set('slug', Str::slug($state))
                            ),
                        
                        TextInput::make('slug')
                            ->label('المعرف (Slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('معرف فريد للقالب، يُستخدم برمجياً'),
                        
                        Select::make('type')
                            ->label('النوع')
                            ->options(EmailTemplate::TYPES)
                            ->default('customer')
                            ->required()
                            ->native(false),
                        
                        Select::make('category')
                            ->label('التصنيف')
                            ->options(EmailTemplate::CATEGORIES)
                            ->default('notification')
                            ->required()
                            ->native(false),
                        
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('وصف مختصر للقالب والغرض منه'),
                        
                        Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true)
                            ->helperText('القوالب غير المفعلة لن تُرسل')
                            ->inline(false),
                        
                        TextInput::make('logo_path')
                            ->label('مسار الشعار')
                            ->placeholder('images/logo.png')
                            ->helperText('اختياري'),
                        
                        ColorPicker::make('primary_color')
                            ->label('اللون الأساسي')
                            ->default('#4F46E5'),
                        
                        ColorPicker::make('secondary_color')
                            ->label('اللون الثانوي')
                            ->default('#F59E0B'),
                    ]),

                // 2. Subject Lines (عمود واحد)
                Section::make('عنوان الرسالة')
                    ->description('عنوان البريد الإلكتروني باللغتين')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('subject_ar')
                            ->label('العنوان (عربي)')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('تم استلام طلبك #{{ order_number }}'),
                        
                        TextInput::make('subject_en')
                            ->label('العنوان (إنجليزي)')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('Your Order #{{ order_number }} Confirmed'),
                    ]),

                // 3. Content & Variables (عمودين 1:1)
                Grid::make(2)
                    ->schema([
                        // Right Column: HTML Editor
                        Section::make('محتوى الرسالة')
                            ->description('كود HTML للبريد الإلكتروني')
                            ->icon('heroicon-o-code-bracket')
                            ->collapsible()
                            ->schema([
                                Textarea::make('content_html')
                                    ->label('كود HTML')
                                    ->required()
                                    ->rows(25)
                                    ->extraAttributes([
                                        'dir' => 'ltr', 
                                        'style' => 'font-family: "Courier New", monospace; font-size: 13px; line-height: 1.5;'
                                    ])
                                    ->helperText('استخدم المتغيرات بصيغة: {{ variable_name }}'),
                            ]),
                        
                        // Left Column: Variables + Preview
                        Grid::make(1)
                            ->schema([
                                // Variables Section
                                Section::make('المتغيرات المتاحة')
                                    ->description('المتغيرات التي يمكن استخدامها في القالب')
                                    ->icon('heroicon-o-variable')
                                    ->collapsible()
                                    ->schema([
                                        TagsInput::make('available_variables')
                                            ->label('المتغيرات')
                                            ->placeholder('أضف متغير...')
                                            ->helperText('اضغط Enter لإضافة متغير جديد')
                                            ->suggestions([
                                                'order_number',
                                                'order_total',
                                                'order_date',
                                                'order_status',
                                                'user_name',
                                                'user_email',
                                                'user_phone',
                                                'product_name',
                                                'product_price',
                                                'shipping_name',
                                                'shipping_address',
                                                'shipping_city',
                                                'shipping_governorate',
                                                'track_url',
                                                'app_name',
                                                'app_url',
                                                'support_email',
                                                'current_year',
                                            ]),
                                    ]),
                                
                                // Preview Section
                                Section::make('معاينة القالب')
                                    ->description('شكل الرسالة بعد استبدال المتغيرات')
                                    ->icon('heroicon-o-eye')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('preview_placeholder')
                                            ->label(false)
                                            ->disabled()
                                            ->rows(20)
                                            ->placeholder('قم بحفظ القالب أولاً لرؤية المعاينة...')
                                            ->helperText('سيتم عرض القالب مع بيانات تجريبية')
                                            ->extraAttributes([
                                                'style' => 'background-color: #f9fafb; border: 1px dashed #d1d5db;'
                                            ])
                                            ->dehydrated(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
