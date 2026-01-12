<?php

namespace App\Filament\Resources\EmailCampaigns\EmailCampaigns\Tables;

use App\Jobs\ProcessEmailCampaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('newsletter.campaign_title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label(__('newsletter.campaign_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'offers' => 'success',
                        'custom' => 'info',
                        'newsletter' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'offers' => __('newsletter.offers'),
                        'custom' => __('newsletter.custom'),
                        'newsletter' => __('newsletter.newsletter'),
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label(__('newsletter.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'sending' => 'info',
                        'sent' => 'success',
                        'paused' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => __('newsletter.draft'),
                        'scheduled' => __('newsletter.scheduled'),
                        'sending' => __('newsletter.sending'),
                        'sent' => __('newsletter.sent'),
                        'paused' => __('newsletter.paused'),
                        'cancelled' => __('newsletter.cancelled'),
                        default => $state,
                    }),

                TextColumn::make('send_to')
                    ->label(__('newsletter.audience'))
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'all' => __('newsletter.all'),
                        'active_only' => __('newsletter.active_only'),
                        'recent' => __('newsletter.recent'),
                        'custom' => __('newsletter.custom'),
                        default => $state,
                    }),

                TextColumn::make('statistics')
                    ->label(__('newsletter.statistics'))
                    ->html()
                    ->formatStateUsing(function ($record) {
                        if ($record->emails_sent === 0) {
                            return '<span class="text-gray-500">—</span>';
                        }

                        $success = $record->emails_sent - $record->emails_failed - $record->emails_bounced;
                        $rate = $record->emails_sent > 0 
                            ? round(($success / $record->emails_sent) * 100, 1) 
                            : 0;

                        return sprintf(
                            '<div class="text-sm">
                                <div><strong>%d</strong> %s</div>
                                <div class="text-green-600">✓ %d | <span class="text-red-600">✗ %d</span></div>
                                <div class="text-gray-600">%s%%</div>
                            </div>',
                            $record->emails_sent,
                            __('newsletter.sent'),
                            $success,
                            $record->emails_failed + $record->emails_bounced,
                            $rate
                        );
                    }),

                TextColumn::make('scheduled_at')
                    ->label(__('newsletter.scheduled'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('sent_at')
                    ->label(__('newsletter.sent_at'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label(__('newsletter.created'))
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('newsletter.status'))
                    ->multiple()
                    ->options([
                        'draft' => __('newsletter.draft'),
                        'scheduled' => __('newsletter.scheduled'),
                        'sending' => __('newsletter.sending'),
                        'sent' => __('newsletter.sent'),
                        'paused' => __('newsletter.paused'),
                        'cancelled' => __('newsletter.cancelled'),
                    ]),

                SelectFilter::make('type')
                    ->label(__('newsletter.campaign_type'))
                    ->multiple()
                    ->options([
                        'offers' => __('newsletter.offers'),
                        'custom' => __('newsletter.custom'),
                        'newsletter' => __('newsletter.newsletter'),
                    ]),

                SelectFilter::make('send_to')
                    ->label(__('newsletter.audience'))
                    ->multiple()
                    ->options([
                        'all' => __('newsletter.all'),
                        'active_only' => __('newsletter.active_only'),
                        'recent' => __('newsletter.recent'),
                        'custom' => __('newsletter.custom'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                
                Action::make('send')
                    ->label(__('newsletter.send_now'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('newsletter.send_campaign'))
                    ->modalDescription(__('newsletter.send_confirm'))
                    ->modalSubmitActionLabel(__('newsletter.yes_send_now'))
                    ->visible(fn($record) => in_array($record->status, ['draft', 'scheduled', 'paused']))
                    ->action(function ($record) {
                        ProcessEmailCampaign::dispatch($record);
                        
                        Notification::make()
                            ->title(__('newsletter.campaign_queued'))
                            ->success()
                            ->send();
                    }),

                Action::make('pause')
                    ->label(__('newsletter.pause'))
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'sending')
                    ->action(function ($record) {
                        $record->update(['status' => 'paused']);
                        
                        Notification::make()
                            ->title(__('newsletter.campaign_paused'))
                            ->success()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label(__('newsletter.cancel'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => in_array($record->status, ['draft', 'scheduled', 'paused']))
                    ->action(function ($record) {
                        $record->update(['status' => 'cancelled']);
                        
                        Notification::make()
                            ->title(__('newsletter.campaign_cancelled'))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
