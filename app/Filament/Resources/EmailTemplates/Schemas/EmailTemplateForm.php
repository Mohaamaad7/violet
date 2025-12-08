<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    // Main Content - 2 columns
                    Section::make('معلومات القالب')
                        ->description('البيانات الأساسية للقالب')
                        ->icon('heroicon-o-document-text')
                        ->columnSpan(2)
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
                        ]),

                    // Sidebar - 1 column
                    Section::make('الإعدادات')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->columnSpan(1)
                        ->schema([
                            Toggle::make('is_active')
                                ->label('مفعّل')
                                ->default(true)
                                ->helperText('القوالب غير المفعلة لن تُرسل'),
                            
                            ColorPicker::make('primary_color')
                                ->label('اللون الأساسي')
                                ->default('#4F46E5'),
                            
                            ColorPicker::make('secondary_color')
                                ->label('اللون الثانوي')
                                ->default('#F59E0B'),
                            
                            TextInput::make('logo_path')
                                ->label('مسار الشعار')
                                ->placeholder('images/logo.png')
                                ->helperText('اختياري'),
                        ]),
                ]),

                // Subject Lines
                Section::make('عنوان الرسالة')
                    ->description('عنوان البريد الإلكتروني باللغتين')
                    ->icon('heroicon-o-chat-bubble-left-right')
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

                // MJML Content
                Section::make('محتوى القالب (MJML)')
                    ->description('اكتب كود MJML هنا - سيتم تحويله تلقائياً إلى HTML')
                    ->icon('heroicon-o-code-bracket')
                    ->collapsible()
                    ->schema([
                        Textarea::make('content_mjml')
                            ->label('كود MJML')
                            ->required()
                            ->rows(20)
                            ->columnSpanFull()
                            ->extraAttributes(['dir' => 'ltr', 'style' => 'font-family: monospace;']),
                    ]),

                // Available Variables
                Section::make('المتغيرات المتاحة')
                    ->description('المتغيرات التي يمكن استخدامها في هذا القالب')
                    ->icon('heroicon-o-variable')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TagsInput::make('available_variables')
                            ->label('المتغيرات')
                            ->placeholder('أضف متغير...')
                            ->helperText('مثال: order_number, user_name, order_total')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
