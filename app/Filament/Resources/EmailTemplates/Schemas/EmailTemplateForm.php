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
            ->components([
                // Consolidated Template Information Card
                Section::make('معلومات القالب')
                    ->description('البيانات الأساسية للقالب والإعدادات')
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
                        
                        // Settings moved below description
                        Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true)
                            ->helperText('القوالب غير المفعلة لن تُرسل')
                            ->columnSpan(1),
                        
                        TextInput::make('logo_path')
                            ->label('مسار الشعار')
                            ->placeholder('images/logo.png')
                            ->helperText('اختياري')
                            ->columnSpan(1),
                        
                        ColorPicker::make('primary_color')
                            ->label('اللون الأساسي')
                            ->default('#4F46E5')
                            ->columnSpan(1),
                        
                        ColorPicker::make('secondary_color')
                            ->label('اللون الثانوي')
                            ->default('#F59E0B')
                            ->columnSpan(1),
                    ]),

                // Subject Lines
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

                // HTML Content with Available Variables
                Section::make('محتوى القالب (HTML)')
                    ->description('محتوى HTML للبريد الإلكتروني')
                    ->icon('heroicon-o-code-bracket')
                    ->collapsible()
                    ->schema([
                        Textarea::make('content_html')
                            ->label('كود HTML')
                            ->required()
                            ->rows(20)
                            ->columnSpanFull()
                            ->extraAttributes(['dir' => 'ltr', 'style' => 'font-family: monospace; font-size: 12px;'])
                            ->helperText('استخدم المتغيرات بصيغة: {{ variable_name }}'),
                        
                        // Available Variables moved inside HTML Content card
                        TagsInput::make('available_variables')
                            ->label('المتغيرات المتاحة')
                            ->placeholder('أضف متغير...')
                            ->helperText('المتغيرات التي يمكن استخدامها: order_number, user_name, product_name, order_total, إلخ...')
                            ->columnSpanFull()
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
                                'track_url',
                                'app_name',
                                'app_url',
                                'current_year',
                            ]),
                    ]),
            ]);
    }
}
