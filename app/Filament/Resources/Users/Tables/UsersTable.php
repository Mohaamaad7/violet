<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo_path')
                    ->label(__('admin.table.photo'))
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label(__('admin.table.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('admin.table.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('roles.name')
                    ->label(__('admin.table.role'))
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state): string => $state ?? __('admin.table.no_role')),
                
                TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => auth()->user()->can('update', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete users')),
                    RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()->can('edit users')),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete users')),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
