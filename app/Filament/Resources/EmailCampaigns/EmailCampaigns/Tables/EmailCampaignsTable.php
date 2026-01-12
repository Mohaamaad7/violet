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
                    ->label(__('Campaign Title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'offers' => 'success',
                        'custom' => 'info',
                        'newsletter' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'offers' => __('Offers'),
                        'custom' => __('Custom'),
                        'newsletter' => __('Newsletter'),
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label(__('Status'))
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
                        'draft' => __('Draft'),
                        'scheduled' => __('Scheduled'),
                        'sending' => __('Sending'),
                        'sent' => __('Sent'),
                        'paused' => __('Paused'),
                        'cancelled' => __('Cancelled'),
                        default => $state,
                    }),

                TextColumn::make('send_to')
                    ->label(__('Audience'))
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'all' => __('All'),
                        'active_only' => __('Active Only'),
                        'recent' => __('Recent'),
                        'custom' => __('Custom'),
                        default => $state,
                    }),

                TextColumn::make('statistics')
                    ->label(__('Statistics'))
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
                            __('sent'),
                            $success,
                            $record->emails_failed + $record->emails_bounced,
                            $rate
                        );
                    }),

                TextColumn::make('scheduled_at')
                    ->label(__('Scheduled'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('sent_at')
                    ->label(__('Sent At'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->multiple()
                    ->options([
                        'draft' => __('Draft'),
                        'scheduled' => __('Scheduled'),
                        'sending' => __('Sending'),
                        'sent' => __('Sent'),
                        'paused' => __('Paused'),
                        'cancelled' => __('Cancelled'),
                    ]),

                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->multiple()
                    ->options([
                        'offers' => __('Offers'),
                        'custom' => __('Custom'),
                        'newsletter' => __('Newsletter'),
                    ]),

                SelectFilter::make('send_to')
                    ->label(__('Audience'))
                    ->multiple()
                    ->options([
                        'all' => __('All'),
                        'active_only' => __('Active Only'),
                        'recent' => __('Recent'),
                        'custom' => __('Custom'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                
                Action::make('send')
                    ->label(__('Send Now'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('Send Campaign'))
                    ->modalDescription(__('Are you sure you want to send this campaign? This action cannot be undone.'))
                    ->modalSubmitActionLabel(__('Yes, Send Now'))
                    ->visible(fn($record) => in_array($record->status, ['draft', 'scheduled', 'paused']))
                    ->action(function ($record) {
                        ProcessEmailCampaign::dispatch($record);
                        
                        Notification::make()
                            ->title(__('Campaign queued for sending'))
                            ->success()
                            ->send();
                    }),

                Action::make('pause')
                    ->label(__('Pause'))
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'sending')
                    ->action(function ($record) {
                        $record->update(['status' => 'paused']);
                        
                        Notification::make()
                            ->title(__('Campaign paused'))
                            ->success()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label(__('Cancel'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => in_array($record->status, ['draft', 'scheduled', 'paused']))
                    ->action(function ($record) {
                        $record->update(['status' => 'cancelled']);
                        
                        Notification::make()
                            ->title(__('Campaign cancelled'))
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
