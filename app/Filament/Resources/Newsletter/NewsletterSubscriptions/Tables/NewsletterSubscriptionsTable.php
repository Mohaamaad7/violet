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
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'unsubscribed' => 'danger',
                        'bounced' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'unsubscribed' => 'ألغى الاشتراك',
                        'bounced' => 'فشل التوصيل',
                        default => $state,
                    }),

                TextColumn::make('source')
                    ->label('المصدر')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'footer' => 'Footer',
                        'contact' => 'صفحة الاتصال',
                        'popup' => 'نافذة منبثقة',
                        'checkout' => 'صفحة الدفع',
                        default => $state ?? '-',
                    }),

                TextColumn::make('subscribed_at')
                    ->label('تاريخ الاشتراك')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('campaignLogs_count')
                    ->counts('campaignLogs')
                    ->label('عدد الرسائل')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'unsubscribed' => 'ألغى الاشتراك',
                        'bounced' => 'فشل التوصيل',
                    ]),

                SelectFilter::make('source')
                    ->label('المصدر')
                    ->options([
                        'footer' => 'Footer',
                        'contact' => 'صفحة الاتصال',
                        'popup' => 'نافذة منبثقة',
                        'checkout' => 'صفحة الدفع',
                    ]),

                Filter::make('subscribed_last_30_days')
                    ->label('آخر 30 يوم')
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
