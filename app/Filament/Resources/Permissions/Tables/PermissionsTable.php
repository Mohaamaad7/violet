<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable(),

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
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->fromTable()
                            ->withFilename('permissions-' . now()->format('Y-m-d'))
                    ]),
            ])
            ->recordActions([
                // No actions - read-only
            ])
            ->toolbarActions([
                // No bulk actions - read-only
            ])
            ->defaultSort('name', 'asc');
    }
}
