<?php

namespace App\Filament\Resources\Sliders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SlidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label(__('admin.table.image'))
                    ->disk('public')
                    ->height(50),
                
                TextColumn::make('title')
                    ->label(__('admin.table.title'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->subtitle),
                
                TextColumn::make('link_url')
                    ->label(__('admin.table.link'))
                    ->limit(30)
                    ->toggleable()
                    ->placeholder(__('admin.table.no_link')),
                
                TextColumn::make('order')
                    ->label(__('admin.table.order'))
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                ToggleColumn::make('is_active')
                    ->label(__('admin.table.active'))
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
    }
}
