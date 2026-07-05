<?php

namespace App\Filament\Resources\ComboRules\Schemas;

use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ComboRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('معلومات العرض الأساسية')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم العرض (مثل: خصم العودة للمدارس)')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (! $get('slug')) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('رابط صفحة العرض (Slug)')
                            ->helperText('يُستخدم في رابط صفحة الهبوط: /combo/{slug}')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->unique(table: 'combo_rules', column: 'slug', ignoreRecord: true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('viewOfferPage')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->tooltip('عرض صفحة العرض')
                                    ->url(fn (?\App\Models\ComboRule $record) => $record?->slug ? route('combo.show', ['slug' => $record->slug]) : null)
                                    ->openUrlInNewTab()
                                    ->visible(fn (?\App\Models\ComboRule $record): bool => filled($record?->slug))
                            ),
                        FileUpload::make('image_path')
                            ->label('صورة العرض (اختياري)')
                            ->image()
                            ->disk('public')
                            ->directory('combo-rules')
                            ->maxSize(2048),
                        Textarea::make('description')
                            ->label('وصف العرض')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('مفعل')
                            ->default(true),
                        Toggle::make('show_on_homepage')
                            ->label('عرض في الصفحة الرئيسية')
                            ->default(false),
                        Select::make('discount_type')
                            ->label('نوع الخصم')
                            ->options([
                                'percentage' => 'نسبة مئوية',
                                'fixed_price' => 'سعر ثابت للكومبو',
                            ])
                            ->default('percentage')
                            ->required()
                            ->live(),
                        TextInput::make('discount_percentage')
                            ->label('نسبة الخصم (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(fn ($get) => $get('discount_type') === 'percentage')
                            ->visible(fn ($get) => $get('discount_type') === 'percentage'),
                        TextInput::make('fixed_price')
                            ->label('سعر الكومبو الثابت')
                            ->numeric()
                            ->minValue(0)
                            ->required(fn ($get) => $get('discount_type') === 'fixed_price')
                            ->visible(fn ($get) => $get('discount_type') === 'fixed_price'),
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
                                Radio::make('condition_type')
                                    ->label('نوع الشرط')
                                    ->options([
                                        'category' => 'قسم',
                                        'product' => 'منتج محدد',
                                    ])
                                    ->default('category')
                                    ->inline()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $state) {
                                        if ($state === 'product') {
                                            $set('category_id', null);
                                        } else {
                                            $set('product_id', null);
                                        }
                                    }),
                                Select::make('category_id')
                                    ->label('القسم')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(fn ($get) => $get('condition_type') === 'category')
                                    ->hidden(fn ($get) => $get('condition_type') !== 'category'),
                                Select::make('product_id')
                                    ->label('المنتج')
                                    ->live(onBlur: true)
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(fn ($get) => $get('condition_type') === 'product')
                                    ->hidden(fn ($get) => $get('condition_type') !== 'product'),
                                Placeholder::make('sale_warning')
                                    ->label('')
                                    ->content(function ($get) {
                                        $productId = $get('product_id');
                                        if (!$productId) return '';
                                        $product = \App\Models\Product::find($productId);
                                        if (!$product || !$product->is_on_sale) return '';
                                        $saved = ($product->price - $product->final_price) * (int) $get('required_quantity');
                                        return '<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 text-sm">
                                            ⚠️ <strong>تنبيه:</strong> هذا المنتج عليه خصم حاليًا.<br>
                                            سعر البيع الحالي: <strong>' . number_format($product->final_price, 2) . ' EGP</strong>
                                            (بدلاً من ' . number_format($product->price, 2) . ' EGP)<br>
                                            إجمالي التوفير للعميل في هذا الكومبو مقارنة بالسعر الفردي: <strong>' . number_format($saved, 2) . ' EGP</strong>
                                        </div>';
                                    })
                                    ->html()
                                    ->visible(function ($get) {
                                        $productId = $get('product_id');
                                        if (!$productId) return false;
                                        $product = \App\Models\Product::find($productId);
                                        return $product && $product->is_on_sale;
                                    }),
                                TextInput::make('required_quantity')
                                    ->label('الكمية المطلوبة')
                                    ->live()
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(99999),
                                Placeholder::make('original_total_display')
                                    ->label('إجمالي السعر الأصلي')
                                    ->content(function ($get) {
                                        $productId = $get('product_id');
                                        $quantity = $get('required_quantity');
                                        if (!$productId || !$quantity) {
                                            return '---';
                                        }
                                        $product = \App\Models\Product::find($productId);
                                        if (!$product) {
                                            return '---';
                                        }
                                        $unitPrice = $product->final_price;
                                        $total = $unitPrice * (int) $quantity;
                                        $result = number_format($total, 2) . ' EGP';
                                        if ($product->is_on_sale) {
                                            $result .= ' <span style="color: #dc2626; font-weight: 600;">(سعر القطعة بعد الخصم الأصلي: ' . number_format($unitPrice, 2) . ' EGP)</span>';
                                        }
                                        return $result;
                                    })
                                    ->html(),
                            ])
                            ->addActionLabel('إضافة شرط جديد')
                            ->columns(1)
                            ->required()
                            ->minItems(1)
                            ->rules([
                                fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                    if (is_array($value) && count($value) === 1) {
                                        $condition = reset($value);
                                        if (($condition['required_quantity'] ?? 0) <= 1) {
                                            $fail('عند إضافة شرط واحد فقط، يجب أن تكون الكمية المطلوبة أكبر من 1.');
                                        }
                                    }
                                },
                            ])
                    ])->columnSpan(1),
            ]);
    }
}
