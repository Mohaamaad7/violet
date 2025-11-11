<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.name')
                    ->label('Category'),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('sku')
                    ->label('SKU'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('short_description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('sale_price')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('cost_price')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('stock')
                    ->numeric(),
                TextEntry::make('low_stock_threshold')
                    ->numeric(),
                TextEntry::make('weight')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('brand')
                    ->placeholder('-'),
                TextEntry::make('barcode')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                IconEntry::make('is_featured')
                    ->boolean(),
                TextEntry::make('views_count')
                    ->numeric(),
                TextEntry::make('sales_count')
                    ->numeric(),
                TextEntry::make('meta_title')
                    ->placeholder('-'),
                TextEntry::make('meta_description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('meta_keywords')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Product $record): bool => $record->trashed()),
            ]);
    }
}
