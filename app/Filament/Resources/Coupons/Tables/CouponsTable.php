<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\DiscountCode;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('admin.coupons.table.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage(__('admin.coupons.table.code_copied')),

                TextColumn::make('discount_type')
                    ->label(__('admin.coupons.table.discount_type'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'percentage' => __('admin.coupons.discount_types.percentage'),
                        'fixed' => __('admin.coupons.discount_types.fixed'),
                        'free_shipping' => __('admin.coupons.discount_types.free_shipping'),
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'success',
                        'free_shipping' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('discount_value')
                    ->label(__('admin.coupons.table.value'))
                    ->formatStateUsing(function (DiscountCode $record): string {
                        if ($record->discount_type === 'free_shipping') {
                            return __('admin.coupons.discount_types.free_shipping');
                        }
                        if ($record->discount_type === 'percentage') {
                            return $record->discount_value . '%';
                        }
                        return number_format($record->discount_value, 2) . ' ' . __('messages.currency.egp');
                    })
                    ->sortable(),

                TextColumn::make('usage_display')
                    ->label(__('admin.coupons.table.usage'))
                    ->getStateUsing(function (DiscountCode $record): string {
                        $used = $record->times_used;
                        $limit = $record->usage_limit;
                        return $limit ? "{$used}/{$limit}" : $used;
                    })
                    ->color(
                        fn(DiscountCode $record): string =>
                        $record->usage_limit && $record->times_used >= $record->usage_limit
                        ? 'danger'
                        : 'success'
                    ),

                TextColumn::make('expires_at')
                    ->label(__('admin.coupons.table.expires'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->color(
                        fn(DiscountCode $record): string =>
                        $record->expires_at && $record->expires_at->isPast()
                        ? 'danger'
                        : 'success'
                    )
                    ->placeholder(__('admin.coupons.table.no_expiry')),

                TextColumn::make('status')
                    ->label(__('admin.coupons.table.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'expired' => 'danger',
                        'scheduled' => 'info',
                        'exhausted' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label(__('admin.coupons.table.active'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('discount_type')
                    ->label(__('admin.coupons.table.discount_type'))
                    ->options([
                        'percentage' => __('admin.coupons.discount_types.percentage'),
                        'fixed' => __('admin.coupons.discount_types.fixed'),
                        'free_shipping' => __('admin.coupons.discount_types.free_shipping'),
                    ]),

                SelectFilter::make('type')
                    ->label(__('admin.coupons.form.type'))
                    ->options([
                        'general' => __('admin.coupons.types.general'),
                        'influencer' => __('admin.coupons.types.influencer'),
                        'campaign' => __('admin.coupons.types.campaign'),
                    ]),

                Filter::make('active')
                    ->label(__('admin.coupons.filters.active_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->toggle(),

                Filter::make('expired')
                    ->label(__('admin.coupons.filters.expired'))
                    ->query(fn(Builder $query): Builder => $query->expired())
                    ->toggle(),

                Filter::make('valid')
                    ->label(__('admin.coupons.filters.valid'))
                    ->query(fn(Builder $query): Builder => $query->valid())
                    ->toggle(),
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label(__('admin.action.export_excel'))
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->fromTable()
                            ->withFilename('coupons-' . now()->format('Y-m-d'))
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('admin.coupons.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label(__('admin.coupons.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
