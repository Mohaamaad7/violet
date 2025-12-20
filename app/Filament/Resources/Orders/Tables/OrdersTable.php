<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // رقم الطلب
                TextColumn::make('order_number')
                    ->label(__('admin.table.order_number'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->copyMessage(__('admin.widgets.recent_orders.copied'))
                    ->icon('heroicon-o-hashtag'),

                // اسم العميل
                TextColumn::make('customer_name')
                    ->label(__('admin.table.customer'))
                    ->searchable(['customer.name', 'guest_name', 'customer.email', 'guest_email'])
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('guest_name', $direction)
                            ->orderBy('customer_id', $direction);
                    })
                    ->formatStateUsing(function ($record) {
                        $isGuest = !$record->customer_id;
                        $name = $isGuest ? $record->guest_name : $record->customer?->name;
                        $email = $isGuest ? $record->guest_email : $record->customer?->email;

                        return $name ?? $email ?? __('admin.common.unknown');
                    })
                    ->description(function ($record) {
                        $isGuest = !$record->customer_id;
                        return $isGuest ? $record->guest_email : $record->customer?->email;
                    })
                    ->icon(fn($record) => $record->customer_id ? 'heroicon-s-check-badge' : null)
                    ->iconColor(fn($record) => $record->customer_id ? 'info' : null),

                // الإجمالي
                TextColumn::make('total')
                    ->label(__('admin.table.total'))
                    ->money('EGP')
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->color('success'),

                // حالة الطلب (Status Badge)
                TextColumn::make('status')
                    ->label(__('admin.table.order_status'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state?->label() ?? '-')
                    ->color(fn($state) => $state?->color() ?? 'gray')
                    ->icon(fn($state) => $state?->icon() ?? 'heroicon-o-question-mark-circle'),

                // حالة الدفع (Payment Status Badge)
                TextColumn::make('payment_status')
                    ->label(__('admin.table.payment_status'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'unpaid' => __('admin.orders.payment.unpaid'),
                        'paid' => __('admin.orders.payment.paid'),
                        'failed' => __('admin.orders.payment.failed'),
                        'refunded' => __('admin.orders.payment.refunded'),
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'gray',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'unpaid' => 'heroicon-o-clock',
                        'paid' => 'heroicon-o-check-badge',
                        'failed' => 'heroicon-o-x-circle',
                        'refunded' => 'heroicon-o-arrow-uturn-left',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                // طريقة الدفع
                TextColumn::make('payment_method')
                    ->label(__('admin.table.payment_method'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cod' => __('admin.orders.method.cod'),
                        'card' => __('admin.orders.method.card'),
                        'instapay' => 'InstaPay',
                        default => $state,
                    })
                    ->color('info')
                    ->toggleable(),

                // حالة المرتجع (Return Status Badge)
                TextColumn::make('return_status')
                    ->label('المرتجع')
                    ->badge()
                    ->sortable()
                    ->placeholder('-')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'none' => '',
                        'requested' => '📝 طلب مرتجع',
                        'approved' => '✅ موافق عليه',
                        'completed' => '🔄 مكتمل',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'none' => 'gray',
                        'requested' => 'warning',
                        'approved' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): ?string => match ($state) {
                        'none' => null,
                        'requested' => 'heroicon-o-arrow-uturn-left',
                        'approved' => 'heroicon-o-check-circle',
                        'completed' => 'heroicon-o-check-badge',
                        default => 'heroicon-o-arrow-uturn-left',
                    })
                    ->tooltip(fn($record) => match ($record->return_status) {
                        'none' => null,
                        'requested' => 'يوجد طلب مرتجع قيد المراجعة',
                        'approved' => 'تمت الموافقة على المرتجع',
                        'completed' => 'تم اكتمال المرتجع',
                        default => null,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                // تاريخ الإنشاء
                TextColumn::make('created_at')
                    ->label(__('admin.table.order_date'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->description(fn($record) => $record->created_at->diffForHumans())
                    ->toggleable(),
            ])
            ->filters([
                // فلتر حالة الطلب (Multi-Select)
                SelectFilter::make('status')
                    ->label(__('admin.table.order_status'))
                    ->multiple()
                    ->options([
                        0 => __('admin.orders.status.pending'),
                        1 => __('admin.orders.status.processing'),
                        2 => __('admin.orders.status.shipped'),
                        3 => __('admin.orders.status.delivered'),
                        4 => __('admin.orders.status.cancelled'),
                    ])
                    ->placeholder(__('admin.filter.all'))
                    ->columnSpan(1),

                // فلتر حالة الدفع
                SelectFilter::make('payment_status')
                    ->label(__('admin.table.payment_status'))
                    ->multiple()
                    ->options([
                        'unpaid' => __('admin.orders.payment.unpaid'),
                        'paid' => __('admin.orders.payment.paid'),
                        'failed' => __('admin.orders.payment.failed'),
                        'refunded' => __('admin.orders.payment.refunded'),
                    ])
                    ->placeholder(__('admin.filter.all'))
                    ->columnSpan(1),

                // فلتر طريقة الدفع
                SelectFilter::make('payment_method')
                    ->label(__('admin.table.payment_method'))
                    ->options([
                        'cod' => __('admin.orders.method.cod'),
                        'card' => __('admin.orders.method.card'),
                        'instapay' => __('admin.orders.method.instapay'),
                    ])
                    ->placeholder(__('admin.filter.all'))
                    ->columnSpan(1),

                // فلتر بالتاريخ (Date Range)
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.filters.date_from'))
                            ->placeholder(__('admin.filters.select_date')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.filters.date_to'))
                            ->placeholder(__('admin.filters.select_date')),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = __('admin.filters.date_from') . ': ' . \Carbon\Carbon::parse($data['created_from'])->format('d/m/Y');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = __('admin.filters.date_to') . ': ' . \Carbon\Carbon::parse($data['created_until'])->format('d/m/Y');
                        }

                        return $indicators;
                    })
                    ->columnSpan(2),

                // فلتر بالعميل (البحث بالاسم أو الإيميل)
                Filter::make('customer')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('customer_search')
                            ->label(__('admin.filters.customer_search'))
                            ->placeholder(__('admin.filters.customer_search_placeholder')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['customer_search'],
                            fn(Builder $query, $search): Builder => $query
                                ->where(function ($q) use ($search) {
                                    $q->whereHas('customer', function ($customerQuery) use ($search) {
                                        $customerQuery->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");
                                    })
                                        ->orWhere('guest_name', 'like', "%{$search}%")
                                        ->orWhere('guest_email', 'like', "%{$search}%");
                                })
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['customer_search']) {
                            return null;
                        }

                        return __('admin.table.customer') . ': ' . $data['customer_search'];
                    })
                    ->columnSpan(1),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(6)
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('تصدير Excel')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->fromTable()
                            ->withFilename('orders-' . now()->format('Y-m-d'))
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label(__('admin.action.view_details'))
                    ->visible(fn($record) => auth()->user()->can('view', $record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('admin.action.delete_selected'))
                        ->visible(fn() => auth()->user()->can('delete orders')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
