<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('inventory.type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'restock' => 'success',
                        'sale' => 'info',
                        'return' => 'warning',
                        'expired' => 'danger',
                        'damaged' => 'danger',
                        'adjustment' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => __('inventory.' . $state))
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label(__('inventory.product'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => ($record->type === 'restock' || $record->type === 'return' ? '+' : '') . $state)
                    ->color(fn($record) => match ($record->type) {
                        'restock', 'return' => 'success',
                        'sale', 'expired', 'damaged' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('stock_before')
                    ->label(__('inventory.stock_before'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stock_after')
                    ->label(__('inventory.stock_after'))
                    ->numeric()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('reference_type')
                    ->label(__('inventory.reference'))
                    ->formatStateUsing(fn($state, $record) => $state ? class_basename($state) . ' #' . $record->reference_id : '-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('createdBy.name')
                    ->label(__('inventory.user'))
                    ->default(__('inventory.system'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('notes')
                    ->label(__('inventory.notes'))
                    ->limit(50)
                    ->tooltip(fn($record) => $record->notes)
                    ->searchable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('created_at')
                    ->label(__('inventory.date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('inventory.type'))
                    ->options([
                        'restock' => __('inventory.restock'),
                        'sale' => __('inventory.sale'),
                        'return' => __('inventory.return'),
                        'expired' => __('inventory.expired'),
                        'damaged' => __('inventory.damaged'),
                        'adjustment' => __('inventory.adjustment'),
                    ])
                    ->multiple(),

                SelectFilter::make('product_id')
                    ->label(__('inventory.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->fromTable()
                            ->withFilename('stock-movements-' . now()->format('Y-m-d'))
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('inventory.no_stock_movements_yet'))
            ->emptyStateDescription(__('inventory.stock_movements_description'));
    }
}
