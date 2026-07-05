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
                TextColumn::make('discount_type')
                    ->label('الخصم')
                    ->formatStateUsing(function ($record) {
                        if ($record->discount_type === 'percentage') {
                            return $record->discount_percentage . '%';
                        }
                        return $record->fixed_price . ' ج.م (ثابت)';
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مفعل')
                    ->boolean()
                    ->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('show_on_homepage')
                    ->label('الرئيسية')
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
                \Filament\Tables\Columns\TextColumn::make('view_storefront')
                    ->label('عرض في المتجر')
                    ->formatStateUsing(fn () => '')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn ($record) => $record->slug ? route('combo.show', ['slug' => $record->slug]) : null)
                    ->openUrlInNewTab()
                    ->extraAttributes(['class' => 'text-primary-600 hover:text-primary-500'])
                    ->visible(fn ($record) => filled($record?->slug)),
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
