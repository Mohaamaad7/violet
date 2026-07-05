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
                            
                        Repeater::make('tiers')
                            ->label('مستويات الأسعار (Tiers)')
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('quantity')
                                    ->label('الكمية (عدد القطع)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
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
                                    ->label('السعر الإجمالي للكمية (ج.م)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(fn ($get) => $get('discount_type') === 'fixed_price')
                                    ->visible(fn ($get) => $get('discount_type') === 'fixed_price'),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->rules([
                                fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                    if (!is_array($value) || empty($value)) return;
                                    
                                    $quantities = [];
                                    $lastQuantity = 0;
                                    $lastUnitPrice = PHP_FLOAT_MAX;
                                    
                                    // Sort by quantity to validate ascending order
                                    usort($value, fn($a, $b) => ($a['quantity'] ?? 0) <=> ($b['quantity'] ?? 0));
                                    
                                    foreach ($value as $index => $tier) {
                                        $qty = (int)($tier['quantity'] ?? 0);
                                        
                                        if (in_array($qty, $quantities)) {
                                            $fail("لا يمكن تكرار نفس الكمية ({$qty}) في أكثر من مستوى.");
                                        }
                                        $quantities[] = $qty;
                                        
                                        if ($qty <= $lastQuantity) {
                                            // Shouldn't happen since we sorted, but good for logical check
                                        }
                                        $lastQuantity = $qty;
                                        
                                        // Validate decreasing unit price for fixed_price tiers
                                        if (($tier['discount_type'] ?? '') === 'fixed_price') {
                                            $price = (float)($tier['fixed_price'] ?? 0);
                                            if ($qty > 0) {
                                                $unitPrice = $price / $qty;
                                                if ($unitPrice >= $lastUnitPrice && $index > 0) {
                                                    $fail("يجب أن يكون سعر القطعة الواحدة أرخص كلما زادت الكمية. سعر القطعة في الكمية {$qty} هو {$unitPrice} ج.م");
                                                }
                                                $lastUnitPrice = $unitPrice;
                                            }
                                        }
                                    }
                                },
                            ]),

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
                    
                Section::make('شروط العرض (الفئة أو المنتج)')
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
                            ])
                            ->addActionLabel('إضافة شرط جديد')
                            ->columns(1)
                            ->required()
                            ->minItems(1)
                    ])->columnSpan(1),
            ]);
    }
}
