<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ============================================
                // LEFT COLUMN: Product Images & Basic Info
                // ============================================
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        // Product Images Section
                        Section::make(__('admin.products.images'))
                            ->icon('heroicon-o-photo')
                            ->collapsible()
                            ->schema([
                                SpatieMediaLibraryImageEntry::make('media')
                                    ->label('')
                                    ->collection('product-images')
                                    ->conversion('preview')
                                    ->circular(false)
                                    ->stacked(false)
                                    ->limit(5)
                                    ->limitedRemainingText()
                                    ->imageHeight(200)
                                    ->extraImgAttributes(['class' => 'rounded-lg shadow-sm']),
                            ]),
                            
                        // Quick Stats
                        Section::make(__('admin.products.statistics'))
                            ->icon('heroicon-o-chart-bar')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('views_count')
                                        ->label(__('admin.products.views'))
                                        ->numeric()
                                        ->icon('heroicon-o-eye')
                                        ->color('gray'),
                                    TextEntry::make('sales_count')
                                        ->label(__('admin.products.sales'))
                                        ->numeric()
                                        ->icon('heroicon-o-shopping-cart')
                                        ->color('success'),
                                ]),
                            ]),
                    ]),

                // ============================================
                // RIGHT COLUMN: Main Product Details
                // ============================================
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        // Basic Information Section
                        Section::make(__('admin.products.basic_info'))
                            ->icon('heroicon-o-information-circle')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('admin.products.name'))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),
                                    
                                TextEntry::make('category.name')
                                    ->label(__('admin.products.category'))
                                    ->badge()
                                    ->color('violet'),
                                    
                                TextEntry::make('brand')
                                    ->label(__('admin.products.brand'))
                                    ->placeholder(__('admin.common.not_specified'))
                                    ->icon('heroicon-o-building-storefront'),
                                    
                                TextEntry::make('sku')
                                    ->label(__('admin.products.sku'))
                                    ->copyable()
                                    ->copyMessage(__('admin.common.copied'))
                                    ->fontFamily('mono')
                                    ->badge()
                                    ->color('gray'),
                                    
                                TextEntry::make('barcode')
                                    ->label(__('admin.products.barcode'))
                                    ->placeholder(__('admin.common.not_specified'))
                                    ->copyable()
                                    ->fontFamily('mono'),
                                    
                                TextEntry::make('slug')
                                    ->label(__('admin.products.slug'))
                                    ->icon('heroicon-o-link')
                                    ->copyable()
                                    ->color('gray'),
                                    
                                TextEntry::make('status')
                                    ->label(__('admin.products.status'))
                                    ->badge(),
                            ]),

                        // Pricing Section
                        Section::make(__('admin.products.pricing'))
                            ->icon('heroicon-o-currency-dollar')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('price')
                                    ->label(__('admin.products.price'))
                                    ->money('SAR', locale: 'ar')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                                    
                                TextEntry::make('sale_price')
                                    ->label(__('admin.products.sale_price'))
                                    ->money('SAR', locale: 'ar')
                                    ->placeholder(__('admin.common.no_sale'))
                                    ->color('danger'),
                                    
                                TextEntry::make('cost_price')
                                    ->label(__('admin.products.cost_price'))
                                    ->money('SAR', locale: 'ar')
                                    ->placeholder(__('admin.common.not_specified'))
                                    ->color('gray'),
                            ]),

                        // Inventory Section
                        Section::make(__('admin.products.inventory'))
                            ->icon('heroicon-o-cube')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('stock')
                                    ->label(__('admin.products.stock'))
                                    ->numeric()
                                    ->badge()
                                    ->color(fn (Product $record): string => 
                                        $record->stock <= 0 ? 'danger' : 
                                        ($record->stock <= ($record->low_stock_threshold ?? 5) ? 'warning' : 'success')
                                    ),
                                    
                                TextEntry::make('low_stock_threshold')
                                    ->label(__('admin.products.low_stock_threshold'))
                                    ->numeric()
                                    ->placeholder('5')
                                    ->icon('heroicon-o-exclamation-triangle'),
                                    
                                TextEntry::make('weight')
                                    ->label(__('admin.products.weight'))
                                    ->numeric()
                                    ->suffix(' kg')
                                    ->placeholder(__('admin.common.not_specified')),
                            ]),

                        // Short Description
                        Section::make(__('admin.products.short_description'))
                            ->icon('heroicon-o-document-text')
                            ->collapsible()
                            ->schema([
                                TextEntry::make('short_description')
                                    ->label('')
                                    ->placeholder(__('admin.common.no_description'))
                                    ->prose()
                                    ->markdown()
                                    ->columnSpanFull(),
                            ]),

                        // Full Description (HTML Rendered)
                        Section::make(__('admin.products.description'))
                            ->icon('heroicon-o-document')
                            ->collapsible()
                            ->schema([
                                TextEntry::make('description')
                                    ->label('')
                                    ->placeholder(__('admin.common.no_description'))
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),

                        // Visibility & Features
                        Section::make(__('admin.products.visibility'))
                            ->icon('heroicon-o-eye')
                            ->columns(3)
                            ->collapsed()
                            ->collapsible()
                            ->schema([
                                IconEntry::make('is_featured')
                                    ->label(__('admin.products.is_featured'))
                                    ->boolean()
                                    ->trueIcon('heroicon-o-star')
                                    ->falseIcon('heroicon-o-x-mark')
                                    ->trueColor('warning')
                                    ->falseColor('gray'),
                                    
                                IconEntry::make('is_active')
                                    ->label(__('admin.products.is_active'))
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ]),

                        // SEO Section
                        Section::make(__('admin.products.seo'))
                            ->icon('heroicon-o-magnifying-glass')
                            ->collapsed()
                            ->collapsible()
                            ->columns(1)
                            ->schema([
                                TextEntry::make('meta_title')
                                    ->label(__('admin.products.meta_title'))
                                    ->placeholder(__('admin.common.not_specified'))
                                    ->icon('heroicon-o-tag'),
                                    
                                TextEntry::make('meta_description')
                                    ->label(__('admin.products.meta_description'))
                                    ->placeholder(__('admin.common.not_specified'))
                                    ->columnSpanFull(),
                                    
                                TextEntry::make('meta_keywords')
                                    ->label(__('admin.products.meta_keywords'))
                                    ->placeholder(__('admin.common.not_specified'))
                                    ->columnSpanFull(),
                            ]),

                        // Timestamps
                        Section::make(__('admin.common.timestamps'))
                            ->icon('heroicon-o-clock')
                            ->collapsed()
                            ->collapsible()
                            ->columns(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('admin.common.created_at'))
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-o-calendar'),
                                    
                                TextEntry::make('updated_at')
                                    ->label(__('admin.common.updated_at'))
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-o-arrow-path'),
                                    
                                TextEntry::make('deleted_at')
                                    ->label(__('admin.common.deleted_at'))
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->visible(fn (Product $record): bool => $record->trashed()),
                            ]),
                    ]),
            ]);
    }
}
