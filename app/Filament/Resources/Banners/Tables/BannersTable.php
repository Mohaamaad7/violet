<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->height(50),
                
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No title'),
                
                TextColumn::make('position')
                    ->label('Position')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'homepage_top' => 'Homepage - Top',
                        'homepage_middle' => 'Homepage - Middle',
                        'homepage_bottom' => 'Homepage - Bottom',
                        'sidebar_top' => 'Sidebar - Top',
                        'sidebar_middle' => 'Sidebar - Middle',
                        'sidebar_bottom' => 'Sidebar - Bottom',
                        'category_page' => 'Category Page',
                        'product_page' => 'Product Page',
                        default => $state,
                    })
                    ->color('info'),
                
                TextColumn::make('link_url')
                    ->label('Link')
                    ->limit(30)
                    ->toggleable()
                    ->placeholder('No link'),
                
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
                
                TextColumn::make('created_at')
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
            ->defaultSort('position', 'asc');
    }
}
