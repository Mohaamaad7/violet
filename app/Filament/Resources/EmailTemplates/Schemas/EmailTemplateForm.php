<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Tiptap\Editor as TiptapEditor;

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
                        // Right Column: Dual-Mode Editor (Visual/HTML)
                        Section::make('محتوى الرسالة')
                            ->description('استخدم محرر WYSIWYG أو كود HTML مباشرة')
                            ->icon('heroicon-o-code-bracket')
                            ->collapsible()
                            ->schema([
                                // Toggle Switch for Visual/HTML Mode
                                Toggle::make('_editor_mode_visual')
                                    ->label('وضع التحرير المرئي (WYSIWYG)')
                                    ->helperText('غيّر بين المحرر المرئي ووضع HTML')
                                    ->inline(false)
                                    ->default(true)
                                    ->live()
                                    ->dehydrated(false), // Don't save this to database
                                
                                // TipTap to HTML Converter Script
                                ViewField::make('tiptap_converter')
                                    ->label(false)
                                    ->view('filament.email-templates.tiptap-to-html')
                                    ->dehydrated(false),
                                
                                // Visual Editor (RichEditor)
                                RichEditor::make('content_html')
                                    ->label('المحرر المرئي')
                                    ->required()
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        ['bold', 'italic', 'underline', 'strike', 'link'],
                                        ['textColor', 'highlight', 'clearFormatting'],
                                        ['h1', 'h2', 'h3'],
                                        ['alignStart', 'alignCenter', 'alignEnd'],
                                        ['bulletList', 'orderedList', 'blockquote', 'table'],
                                        ['undo', 'redo'],
                                    ])
                                    ->textColors([
                                        '#000000' => 'أسود',
                                        '#ef4444' => 'أحمر',
                                        '#f97316' => 'برتقالي',
                                        '#eab308' => 'أصفر',
                                        '#22c55e' => 'أخضر',
                                        '#3b82f6' => 'أزرق',
                                        '#6366f1' => 'نيلي',
                                        '#a855f7' => 'بنفسجي',
                                        '#ec4899' => 'زهري',
                                        '#6b7280' => 'رمادي',
                                        '#ffffff' => 'أبيض',
                                    ])
                                    ->dehydrateStateUsing(function ($state) {
                                        // Convert TipTap JSON to HTML when saving using TipTap PHP
                                        if (is_array($state) && isset($state['type'])) {
                                            try {
                                                return (new TiptapEditor())
                                                    ->setContent($state)
                                                    ->getHTML();
                                            } catch (\Exception $e) {
                                                // Fallback to JSON if conversion fails
                                                return json_encode($state);
                                            }
                                        }
                                        return is_string($state) ? $state : '';
                                    })
                                    ->helperText('انقر على المتغيرات من القائمة اليسرى لإدراجها')
                                    ->visible(fn ($get) => $get('_editor_mode_visual') === true),
                                
                                // HTML Source Editor (Textarea)
                                Textarea::make('content_html')
                                    ->label('كود HTML')
                                    ->required()
                                    ->rows(25)
                                    ->extraAttributes([
                                        'dir' => 'ltr', 
                                        'style' => 'font-family: "Courier New", monospace; font-size: 13px; line-height: 1.5;'
                                    ])
                                    ->formatStateUsing(function ($state) {
                                        // If it's already HTML string, return as-is
                                        if (is_string($state) && (str_starts_with(trim($state), '<') || empty($state))) {
                                            return $state;
                                        }
                                        
                                        // If it's TipTap JSON (array/object), convert to HTML using TipTap PHP
                                        if (is_array($state) && isset($state['type'])) {
                                            try {
                                                return (new TiptapEditor())
                                                    ->setContent($state)
                                                    ->getHTML();
                                            } catch (\Exception $e) {
                                                // Fallback to JSON if conversion fails
                                                return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                            }
                                        }
                                        
                                        return is_string($state) ? $state : '';
                                    })
                                    ->helperText('استخدم المتغيرات بصيغة: {{ variable_name }}')
                                    ->visible(fn ($get) => $get('_editor_mode_visual') === false),
                            ]),
                        
                        // Left Column: Variables + Preview
                        Grid::make(1)
                            ->schema([
                                // Variables Section
                                Section::make('المتغيرات المتاحة')
                                    ->description('انقر على أي متغير لإدراجه في المحرر')
                                    ->icon('heroicon-o-variable')
                                    ->collapsible()
                                    ->schema([
                                        ViewField::make('variable_buttons')
                                            ->label(false)
                                            ->view('filament.email-templates.variable-buttons')
                                            ->dehydrated(false),
                                        
                                        TagsInput::make('available_variables')
                                            ->label('إدارة المتغيرات')
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
                                
                                // Live Preview Section
                                Section::make('معاينة مباشرة')
                                    ->description('المعاينة تتحدث تلقائياً أثناء الكتابة')
                                    ->icon('heroicon-o-eye')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        ViewField::make('live_preview')
                                            ->label(false)
                                            ->view('filament.email-templates.live-preview')
                                            ->dehydrated(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
