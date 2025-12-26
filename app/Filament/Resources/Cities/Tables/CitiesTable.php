<?php

namespace App\Filament\Resources\Cities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('governorate.country.name_ar')
                    ->label('الدولة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                
                TextColumn::make('governorate.name_ar')
                    ->label('المحافظة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('name_en')
                    ->label('الاسم بالإنجليزية')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('shipping_cost')
                    ->label('تكلفة الشحن')
                    ->money('EGP')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->placeholder(fn ($record) => $record->governorate->shipping_cost . ' ج.م (افتراضي)')
                    ->description(fn ($record) => !$record->shipping_cost ? 'يستخدم تكلفة المحافظة' : 'تكلفة مخصصة'),
                
                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                SelectFilter::make('governorate_id')
                    ->label('المحافظة')
                    ->relationship('governorate', 'name_ar')
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                
                TernaryFilter::make('has_custom_shipping')
                    ->label('تكلفة الشحن')
                    ->placeholder('الكل')
                    ->trueLabel('تكلفة مخصصة')
                    ->falseLabel('تكلفة افتراضية')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('shipping_cost'),
                        false: fn ($query) => $query->whereNull('shipping_cost'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
