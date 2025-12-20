<?php

namespace App\Filament\Resources\OrderReturns\Tables;

use App\Services\ReturnService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('return_number')
                    ->label('رقم المرتجع')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),

                TextColumn::make('order.order_number')
                    ->label('رقم الطلب')
                    ->url(fn($record) => $record->order ? route('filament.admin.resources.orders.view', $record->order) : null)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.customer_name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn($state) => $state?->color() ?? 'gray')
                    ->formatStateUsing(fn($state) => $state ? match ($state->value) {
                        0 => '🔴 رفض استلام',
                        1 => '🟡 استرجاع بعد التسليم',
                        default => $state->label(),
                    } : '-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => $state?->color() ?? 'gray')
                    ->formatStateUsing(fn($state) => $state ? match ($state->value) {
                        0 => '⏳ قيد المراجعة',
                        1 => '✅ تمت الموافقة',
                        2 => '❌ مرفوض',
                        3 => '✅ مكتمل',
                        default => $state->label(),
                    } : '-')
                    ->sortable(),

                TextColumn::make('refund_amount')
                    ->label('مبلغ الاسترداد')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('السبب')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->reason)
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('refund_status')
                    ->label('حالة الاسترداد')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'معلق',
                        'completed' => 'تم',
                        default => $state,
                    })
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make('approvedBy.name')
                    ->label('تمت المراجعة بواسطة')
                    ->default('-')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        0 => '⏳ قيد المراجعة',
                        1 => '✅ تمت الموافقة',
                        2 => '❌ مرفوض',
                        3 => '✅ مكتمل',
                    ])
                    ->multiple()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        0 => '🔴 رفض استلام',
                        1 => '🟡 استرجاع بعد التسليم',
                    ])
                    ->multiple()
                    ->preload(),

                SelectFilter::make('refund_status')
                    ->label('حالة الاسترداد')
                    ->options([
                        'pending' => 'معلق',
                        'completed' => 'تم',
                    ])
                    ->multiple(),

                Filter::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->form([
                        DatePicker::make('from')
                            ->label('من'),
                        DatePicker::make('until')
                            ->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'من: ' . $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'إلى: ' . $data['until'];
                        }
                        return $indicators;
                    }),
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->fromTable()
                            ->withFilename('order-returns-' . now()->format('Y-m-d'))
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('عرض'),

                Action::make('approve')
                    ->label('موافقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Model $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('الموافقة على طلب المرتجع')
                    ->modalDescription('هل أنت متأكد من الموافقة على هذا الطلب؟')
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('ملاحظات المسؤول')
                            ->placeholder('أي ملاحظات إضافية...')
                            ->rows(3),
                        Checkbox::make('notify_customer')
                            ->label('إرسال إشعار للعميل')
                            ->default(true),
                    ])
                    ->action(function (Model $record, array $data) {
                        app(ReturnService::class)->approveReturn(
                            $record->id,
                            auth()->id(),
                            $data['admin_notes'] ?? null
                        );

                        // TODO: Send notification if $data['notify_customer'] is true
                    })
                    ->successNotificationTitle('تمت الموافقة على طلب المرتجع بنجاح'),

                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Model $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('رفض طلب المرتجع')
                    ->modalDescription('يرجى تحديد سبب الرفض')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->required()
                            ->placeholder('اذكر سبب رفض طلب المرتجع...')
                            ->rows(3)
                            ->maxLength(500),
                        Checkbox::make('notify_customer')
                            ->label('إرسال إشعار للعميل')
                            ->default(true),
                    ])
                    ->action(function (Model $record, array $data) {
                        app(ReturnService::class)->rejectReturn(
                            $record->id,
                            auth()->id(),
                            $data['rejection_reason']
                        );

                        // TODO: Send notification if $data['notify_customer'] is true
                    })
                    ->successNotificationTitle('تم رفض طلب المرتجع'),

                Action::make('process')
                    ->label('معالجة')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->visible(fn(Model $record) => $record->status === 'approved')
                    ->url(fn(Model $record) => route('filament.admin.resources.order-returns.view', $record)),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد طلبات مرتجعات')
            ->emptyStateDescription('طلبات المرتجعات ستظهر هنا عند إنشائها من صفحة الطلبات')
            ->emptyStateIcon('heroicon-o-arrow-uturn-left');
    }
}
