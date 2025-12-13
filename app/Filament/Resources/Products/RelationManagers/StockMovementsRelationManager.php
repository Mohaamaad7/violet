<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    public function form(Schema $schema): Schema
    {
        // Read-only - no form needed
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label(__('inventory.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'restock' => 'success',
                        'sale' => 'info',
                        'return' => 'warning',
                        'adjustment' => 'gray',
                        'expired' => 'danger',
                        'damaged' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('inventory.' . $state)),
                
                TextColumn::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->formatStateUsing(fn (int $state): string => $state >= 0 ? "+{$state}" : (string) $state)
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold'),
                
                TextColumn::make('stock_before')
                    ->label(__('inventory.stock_before'))
                    ->numeric(),
                
                TextColumn::make('stock_after')
                    ->label(__('inventory.stock_after'))
                    ->numeric()
                    ->weight('bold'),
                
                TextColumn::make('batch.batch_number')
                    ->label(__('Batch'))
                    ->searchable()
                    ->placeholder('—')
                    ->hidden(), // Hide batch column since we removed batches
                
                TextColumn::make('reference_type')
                    ->label(__('inventory.reference'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->description(fn ($record): string => $record->reference_id ? "#{$record->reference_id}" : ''),
                
                TextColumn::make('createdBy.name')
                    ->label(__('inventory.user'))
                    ->placeholder(__('inventory.system')),
                
                TextColumn::make('notes')
                    ->label(__('inventory.notes'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->placeholder('—'),
                
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'restock' => __('Restock'),
                        'sale' => __('Sale'),
                        'return' => __('Return'),
                        'adjustment' => __('Adjustment'),
                        'expired' => __('Expired'),
                        'damaged' => __('Damaged'),
                    ]),
                
                Filter::make('positive')
                    ->label(__('Additions Only'))
                    ->query(fn (Builder $query) => $query->where('quantity', '>', 0)),
                
                Filter::make('negative')
                    ->label(__('Deductions Only'))
                    ->query(fn (Builder $query) => $query->where('quantity', '<', 0)),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                // Read-only - no create action
            ])
            ->recordActions([
                // Read-only - no edit/delete actions
            ])
            ->emptyStateHeading(__('No stock movements yet'))
            ->emptyStateDescription(__('Stock movements will appear here when inventory changes occur.'));
    }
    
    public function isReadOnly(): bool
    {
        return true;
    }
}
