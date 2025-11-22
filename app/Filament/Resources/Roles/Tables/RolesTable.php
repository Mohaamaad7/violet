<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('permissions_count')
                    ->label(__('admin.table.permissions_count'))
                    ->counts('permissions')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                    
                TextColumn::make('guard_name')
                    ->label(__('admin.table.guard'))
                    ->badge()
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => auth()->user()->can('update', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete roles')),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
