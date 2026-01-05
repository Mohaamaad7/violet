<?php

namespace App\Filament\Resources\HelpEntries\Tables;

use App\Models\HelpEntry;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HelpEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label(__('admin.help_entries.table.question'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('category')
                    ->label(__('admin.help_entries.table.category'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => HelpEntry::CATEGORIES[$state] ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        'orders' => 'info',
                        'products' => 'success',
                        'marketing' => 'warning',
                        'inventory' => 'danger',
                        'sales' => 'primary',
                        'system' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('admin.help_entries.table.sort_order'))
                    ->sortable()
                    ->alignCenter(),

                ToggleColumn::make('is_active')
                    ->label(__('admin.help_entries.table.is_active'))
                    ->disabled(fn($record) => !auth()->user()->can('update', $record)),

                TextColumn::make('updated_at')
                    ->label(__('admin.help_entries.table.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('admin.help_entries.table.category'))
                    ->options(HelpEntry::CATEGORIES),

                TernaryFilter::make('is_active')
                    ->label(__('admin.help_entries.table.is_active'))
                    ->placeholder(__('admin.filters.all'))
                    ->trueLabel(__('admin.filters.active'))
                    ->falseLabel(__('admin.filters.inactive')),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->visible(fn($record) => auth()->user()->can('update', $record)),
                Actions\DeleteAction::make()
                    ->visible(fn($record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc');
    }
}
