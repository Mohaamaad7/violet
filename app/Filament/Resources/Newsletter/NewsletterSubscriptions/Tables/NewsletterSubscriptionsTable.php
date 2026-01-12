<?php

namespace App\Filament\Resources\Newsletter\NewsletterSubscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsletterSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label(__('newsletter.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('status')
                    ->label(__('newsletter.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'unsubscribed' => 'danger',
                        'bounced' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('newsletter.active'),
                        'unsubscribed' => __('newsletter.unsubscribed'),
                        'bounced' => __('newsletter.bounced'),
                        default => $state,
                    }),

                TextColumn::make('source')
                    ->label(__('newsletter.source'))
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'footer' => __('newsletter.source_footer'),
                        'contact' => __('newsletter.source_contact'),
                        'popup' => __('newsletter.source_popup'),
                        'checkout' => __('newsletter.source_checkout'),
                        default => $state ?? '-',
                    }),

                TextColumn::make('subscribed_at')
                    ->label(__('newsletter.subscribed_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('campaignLogs_count')
                    ->counts('campaignLogs')
                    ->label(__('newsletter.emails_count'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('newsletter.created'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('newsletter.status'))
                    ->options([
                        'active' => __('newsletter.active'),
                        'unsubscribed' => __('newsletter.unsubscribed'),
                        'bounced' => __('newsletter.bounced'),
                    ]),

                SelectFilter::make('source')
                    ->label(__('newsletter.source'))
                    ->options([
                        'footer' => __('newsletter.source_footer'),
                        'contact' => __('newsletter.source_contact'),
                        'popup' => __('newsletter.source_popup'),
                        'checkout' => __('newsletter.source_checkout'),
                    ]),

                Filter::make('subscribed_last_30_days')
                    ->label(__('newsletter.recent_30_days'))
                    ->query(fn (Builder $query) => $query->where('subscribed_at', '>=', now()->subDays(30))),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('subscribed_at', 'desc');
    }
}
