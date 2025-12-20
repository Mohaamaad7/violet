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
                    ->label(__('admin.table.image'))
                    ->disk('public')
                    ->height(50),

                TextColumn::make('title')
                    ->label(__('admin.table.title'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('admin.table.no_title')),

                TextColumn::make('position')
                    ->label(__('admin.table.position'))
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'homepage_top' => __('admin.banners.position.homepage_top'),
                        'homepage_middle' => __('admin.banners.position.homepage_middle'),
                        'homepage_bottom' => __('admin.banners.position.homepage_bottom'),
                        'sidebar_top' => __('admin.banners.position.sidebar_top'),
                        'sidebar_middle' => __('admin.banners.position.sidebar_middle'),
                        'sidebar_bottom' => __('admin.banners.position.sidebar_bottom'),
                        'category_page' => __('admin.banners.position.category_page'),
                        'product_page' => __('admin.banners.position.product_page'),
                        default => $state,
                    })
                    ->color('info'),

                TextColumn::make('link_url')
                    ->label(__('admin.table.link'))
                    ->limit(30)
                    ->toggleable()
                    ->placeholder(__('admin.table.no_link')),

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
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->fromTable()
                            ->withFilename('banners-' . now()->format('Y-m-d'))
                    ]),
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
