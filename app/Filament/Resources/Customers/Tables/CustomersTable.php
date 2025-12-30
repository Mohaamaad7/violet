<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\Customer;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CustomersTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo_path')
                    ->label(trans_db('admin.customers.fields.profile_photo'))
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(40),

                TextColumn::make('name')
                    ->label(trans_db('admin.customers.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label(trans_db('admin.customers.fields.email'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->copyable(),

                TextColumn::make('phone')
                    ->label(trans_db('admin.customers.fields.phone'))
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->placeholder(trans_db('messages.not_available')),

                TextColumn::make('total_orders')
                    ->label(trans_db('admin.customers.fields.total_orders'))
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('total_spent')
                    ->label(trans_db('admin.customers.fields.total_spent'))
                    ->money('EGP')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('last_order_at')
                    ->label(trans_db('admin.customers.fields.last_order_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder(trans_db('messages.no_orders_yet')),

                TextColumn::make('status')
                    ->label(trans_db('admin.customers.fields.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'blocked' => 'danger',
                        'inactive' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state): string => trans_db("admin.customers.status.{$state}"))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(trans_db('admin.customers.fields.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans_db('admin.customers.fields.status'))
                    ->options([
                        'active' => trans_db('admin.customers.status.active'),
                        'blocked' => trans_db('admin.customers.status.blocked'),
                        'inactive' => trans_db('admin.customers.status.inactive'),
                    ])
                    ->default('active'),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label(trans_db('admin.filters.date_from')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label(trans_db('admin.filters.date_to')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Filter::make('total_orders')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_orders')
                            ->label(trans_db('admin.customers.filters.min_orders'))
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('max_orders')
                            ->label(trans_db('admin.customers.filters.max_orders'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_orders'],
                                fn(Builder $query, $value): Builder => $query->where('total_orders', '>=', $value),
                            )
                            ->when(
                                $data['max_orders'],
                                fn(Builder $query, $value): Builder => $query->where('total_orders', '<=', $value),
                            );
                    }),

                Filter::make('total_spent')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_spent')
                            ->label(trans_db('admin.customers.filters.min_spent'))
                            ->numeric()
                            ->prefix('EGP'),
                        \Filament\Forms\Components\TextInput::make('max_spent')
                            ->label(trans_db('admin.customers.filters.max_spent'))
                            ->numeric()
                            ->prefix('EGP'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_spent'],
                                fn(Builder $query, $value): Builder => $query->where('total_spent', '>=', $value),
                            )
                            ->when(
                                $data['max_spent'],
                                fn(Builder $query, $value): Builder => $query->where('total_spent', '<=', $value),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(trans_db('admin.customers.actions.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'active']);
                        }),

                    BulkAction::make('block')
                        ->label(trans_db('admin.customers.actions.block_selected'))
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'blocked']);
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}
