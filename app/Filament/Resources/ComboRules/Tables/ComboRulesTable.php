<?php

namespace App\Filament\Resources\ComboRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ComboRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم العرض')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->label('الخصم')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مفعل')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('يبدأ في')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('ينتهي في')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
