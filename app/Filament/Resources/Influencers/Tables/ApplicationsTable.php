<?php

namespace App\Filament\Resources\Influencers\Tables;

use App\Models\InfluencerApplication;
use App\Services\InfluencerService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(trans_db('admin.applications.fields.full_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label(trans_db('admin.applications.fields.email'))
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('phone')
                    ->label(trans_db('admin.applications.fields.phone'))
                    ->icon('heroicon-o-phone'),

                TextColumn::make('total_followers')
                    ->label(trans_db('admin.applications.fields.total_followers'))
                    ->state(
                        fn(InfluencerApplication $record): int =>
                        ($record->instagram_followers ?? 0) +
                        ($record->facebook_followers ?? 0) +
                        ($record->tiktok_followers ?? 0) +
                        ($record->youtube_followers ?? 0) +
                        ($record->twitter_followers ?? 0)
                    )
                    ->numeric()
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderByRaw(
                            'COALESCE(instagram_followers, 0) + COALESCE(facebook_followers, 0) + COALESCE(tiktok_followers, 0) + COALESCE(youtube_followers, 0) + COALESCE(twitter_followers, 0) ' . $direction
                        );
                    }),

                TextColumn::make('status')
                    ->label(trans_db('admin.applications.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => trans_db("admin.applications.status.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label(trans_db('admin.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans_db('admin.applications.fields.status'))
                    ->options([
                        'pending' => trans_db('admin.applications.status.pending'),
                        'approved' => trans_db('admin.applications.status.approved'),
                        'rejected' => trans_db('admin.applications.status.rejected'),
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
                    ->label(trans_db('admin.applications.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(InfluencerApplication $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading(trans_db('admin.applications.modals.approve_heading'))
                    ->modalDescription(trans_db('admin.applications.modals.approve_description'))
                    ->form([
                        TextInput::make('commission_rate')
                            ->label(trans_db('admin.applications.fields.commission_rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(10)
                            ->suffix('%')
                            ->required(),
                    ])
                    ->action(function (InfluencerApplication $record, array $data): void {
                        try {
                            DB::transaction(function () use ($record, $data) {
                                app(InfluencerService::class)->approveApplication(
                                    $record->id,
                                    (float) $data['commission_rate'],
                                    auth()->id()
                                );
                            });

                            Notification::make()
                                ->title(trans_db('admin.applications.notifications.approved'))
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
                    ->label(trans_db('admin.applications.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(InfluencerApplication $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading(trans_db('admin.applications.modals.reject_heading'))
                    ->modalDescription(trans_db('admin.applications.modals.reject_description'))
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label(trans_db('admin.applications.fields.rejection_reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (InfluencerApplication $record, array $data): void {
                        try {
                            app(InfluencerService::class)->rejectApplication(
                                $record->id,
                                $data['rejection_reason'],
                                auth()->id()
                            );

                            Notification::make()
                                ->title(trans_db('admin.applications.notifications.rejected'))
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
