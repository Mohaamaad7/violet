<?php

namespace App\Filament\Resources\Influencers\Tables;

use App\Models\Influencer;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class InfluencersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(trans_db('admin.influencers.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label(trans_db('admin.table.email'))
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope')
                    ->toggleable(),

                TextColumn::make('commission_rate')
                    ->label(trans_db('admin.influencers.fields.commission_rate'))
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('total_sales')
                    ->label(trans_db('admin.influencers.fields.total_sales'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label(trans_db('admin.influencers.fields.balance'))
                    ->money('EGP')
                    ->sortable()
                    ->color(fn(Influencer $record): string => $record->balance > 0 ? 'success' : 'gray'),

                TextColumn::make('discount_codes_count')
                    ->label(trans_db('admin.influencers.sections.discount_codes'))
                    ->counts('discountCodes')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('status')
                    ->label(trans_db('admin.influencers.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => trans_db("admin.influencers.status.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label(trans_db('admin.created_at'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans_db('admin.influencers.fields.status'))
                    ->options([
                        'active' => trans_db('admin.influencers.status.active'),
                        'inactive' => trans_db('admin.influencers.status.inactive'),
                        'suspended' => trans_db('admin.influencers.status.suspended'),
                    ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(trans_db('admin.action.export_excel')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),

                // Activate Action
                Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Influencer $record): bool => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->action(function (Influencer $record): void {
                        $record->update(['status' => 'active']);
                        Notification::make()
                            ->title(trans_db('admin.influencers.status.active'))
                            ->success()
                            ->send();
                    }),

                // Suspend Action
                Action::make('suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn(Influencer $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (Influencer $record): void {
                        $record->update(['status' => 'suspended']);
                        Notification::make()
                            ->title(trans_db('admin.influencers.status.suspended'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
