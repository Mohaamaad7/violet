<?php

namespace App\Filament\Resources\Influencers\Tables;

use App\Models\CommissionPayout;
use App\Services\InfluencerService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class PayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('influencer.user.name')
                    ->label(trans_db('admin.payouts.fields.influencer'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('amount')
                    ->label(trans_db('admin.payouts.fields.amount'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('method')
                    ->label(trans_db('admin.payouts.fields.method'))
                    ->formatStateUsing(fn(?string $state): string => $state ? trans_db("admin.payouts.methods.{$state}") : '-')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label(trans_db('admin.payouts.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => trans_db("admin.payouts.status.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'paid' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('transaction_reference')
                    ->label(trans_db('admin.payouts.fields.transaction_ref'))
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(trans_db('admin.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans_db('admin.payouts.fields.status'))
                    ->options([
                        'pending' => trans_db('admin.payouts.status.pending'),
                        'approved' => trans_db('admin.payouts.status.approved'),
                        'rejected' => trans_db('admin.payouts.status.rejected'),
                        'paid' => trans_db('admin.payouts.status.paid'),
                    ]),

                SelectFilter::make('method')
                    ->label(trans_db('admin.payouts.fields.method'))
                    ->options([
                        'bank_transfer' => trans_db('admin.payouts.methods.bank_transfer'),
                        'cash' => trans_db('admin.payouts.methods.cash'),
                        'wallet' => trans_db('admin.payouts.methods.wallet'),
                    ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(trans_db('admin.action.export_excel')),
            ])
            ->actions([
                ViewAction::make(),

                // Approve Action
                Action::make('approve')
                    ->label(trans_db('admin.payouts.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn(CommissionPayout $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (CommissionPayout $record): void {
                        try {
                            app(InfluencerService::class)->approvePayout($record->id, auth()->id());
                            Notification::make()
                                ->title(trans_db('admin.payouts.notifications.approved'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(trans_db('admin.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Reject Action
                Action::make('reject')
                    ->label(trans_db('admin.payouts.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(CommissionPayout $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label(trans_db('admin.payouts.fields.rejection_reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (CommissionPayout $record, array $data): void {
                        try {
                            app(InfluencerService::class)->rejectPayout(
                                $record->id,
                                $data['rejection_reason'],
                                auth()->id()
                            );
                            Notification::make()
                                ->title(trans_db('admin.payouts.notifications.rejected'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(trans_db('admin.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Mark as Paid Action
                Action::make('mark_paid')
                    ->label(trans_db('admin.payouts.actions.mark_paid'))
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn(CommissionPayout $record): bool => $record->status === 'approved')
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('transaction_reference')
                            ->label(trans_db('admin.payouts.fields.transaction_ref'))
                            ->required(),
                    ])
                    ->action(function (CommissionPayout $record, array $data): void {
                        try {
                            app(InfluencerService::class)->processPayout(
                                $record->id,
                                $data['transaction_reference'],
                                auth()->id()
                            );
                            Notification::make()
                                ->title(trans_db('admin.payouts.notifications.paid'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(trans_db('admin.error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
