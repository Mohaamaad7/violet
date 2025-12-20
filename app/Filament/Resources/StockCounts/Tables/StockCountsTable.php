<?php

namespace App\Filament\Resources\StockCounts\Tables;

use App\Enums\StockCountStatus;
use App\Enums\StockCountType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class StockCountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('inventory.count_code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('warehouse.name')
                    ->label(__('inventory.warehouse'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('inventory.count_type'))
                    ->formatStateUsing(fn($state) => $state?->label())
                    ->badge()
                    ->color(fn($state) => $state === StockCountType::FULL ? 'info' : 'warning'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => $state?->label())
                    ->badge()
                    ->color(fn($state) => $state?->color()),

                TextColumn::make('total_items')
                    ->label(__('inventory.total_items'))
                    ->alignCenter(),

                TextColumn::make('progress')
                    ->label(__('inventory.progress'))
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'gray',
                    })
                    ->alignCenter(),

                TextColumn::make('createdBy.name')
                    ->label(__('admin.created_by'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label(__('inventory.completed_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(StockCountStatus::options()),

                SelectFilter::make('type')
                    ->label(__('inventory.count_type'))
                    ->options(StockCountType::options()),

                SelectFilter::make('warehouse_id')
                    ->label(__('inventory.warehouse'))
                    ->relationship('warehouse', 'name'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('stock-counts-' . now()->format('Y-m-d'))
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
