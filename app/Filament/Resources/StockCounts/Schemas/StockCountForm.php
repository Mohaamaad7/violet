<?php

namespace App\Filament\Resources\StockCounts\Schemas;

use App\Enums\StockCountScope;
use App\Enums\StockCountType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockCountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('inventory.stock_count_info'))
                    ->schema([
                        // المستودع
                        Select::make('warehouse_id')
                            ->label(__('inventory.warehouse'))
                            ->options(Warehouse::active()->pluck('name', 'id'))
                            ->required()
                            ->default(Warehouse::getDefault()?->id)
                            ->searchable()
                            ->columnSpanFull(),

                        // نوع الجرد
                        Select::make('type')
                            ->label(__('inventory.count_type'))
                            ->options(StockCountType::options())
                            ->required()
                            ->default(StockCountType::FULL->value)
                            ->live()
                            ->helperText('جرد كامل = كل الأصناف | جرد جزئي = اختيار فئة أو منتجات محددة')
                            ->columnSpanFull(),

                        // نطاق الجرد (يظهر فقط للجرد الجزئي)
                        Select::make('scope')
                            ->label(__('inventory.count_scope'))
                            ->options([
                                StockCountScope::CATEGORY->value => 'جرد فئة معينة',
                                StockCountScope::PRODUCTS->value => 'اختيار منتجات محددة',
                            ])
                            ->default(StockCountScope::CATEGORY->value)
                            ->live()
                            ->visible(function (callable $get) {
                                return $get('type') === StockCountType::PARTIAL->value;
                            })
                            ->helperText('اختر طريقة تحديد الأصناف للجرد')
                            ->columnSpanFull(),

                        // اختيار الفئات أو المنتجات (حقل واحد بخيارات ديناميكية)
                        Select::make('scope_ids')
                            ->label(function (callable $get) {
                                return $get('scope') === StockCountScope::PRODUCTS->value
                                    ? 'اختر المنتجات'
                                    : 'اختر الفئات';
                            })
                            ->options(function (callable $get) {
                                if ($get('scope') === StockCountScope::PRODUCTS->value) {
                                    return Product::active()->pluck('name', 'id');
                                }
                                return Category::active()->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->visible(function (callable $get) {
                                return $get('type') === StockCountType::PARTIAL->value;
                            })
                            ->required(function (callable $get) {
                                return $get('type') === StockCountType::PARTIAL->value;
                            })
                            ->helperText(function (callable $get) {
                                return $get('scope') === StockCountScope::PRODUCTS->value
                                    ? 'اختر المنتجات المحددة التي تريد جردها'
                                    : 'اختر الفئات التي تريد جردها - سيتم جرد كل المنتجات داخل هذه الفئات';
                            })
                            ->columnSpanFull(),

                        // ملاحظات
                        Textarea::make('notes')
                            ->label(__('admin.notes'))
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
