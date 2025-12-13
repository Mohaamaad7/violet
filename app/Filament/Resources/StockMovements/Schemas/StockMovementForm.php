<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('batch_id')
                    ->relationship('batch', 'id')
                    ->default(null),
                Select::make('type')
                    ->options([
            'restock' => 'Restock',
            'sale' => 'Sale',
            'return' => 'Return',
            'adjustment' => 'Adjustment',
            'expired' => 'Expired',
            'damaged' => 'Damaged',
        ])
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('stock_before')
                    ->required()
                    ->numeric(),
                TextInput::make('stock_after')
                    ->required()
                    ->numeric(),
                TextInput::make('reference_type')
                    ->default(null),
                TextInput::make('reference_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
