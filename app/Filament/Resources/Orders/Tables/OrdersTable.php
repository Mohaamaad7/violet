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
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الطلب')
                    ->icon('heroicon-o-hashtag'),

                // اسم العميل
                TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->user?->email)
                    ->icon('heroicon-o-user'),

                // الإجمالي
                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->color('success'),

                // حالة الطلب (Status Badge)
                TextColumn::make('status')
                    ->label('حالة الطلب')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'جديد',
                        'processing' => 'قيد المعالجة',
                        'shipped' => 'تم الشحن',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'processing' => 'heroicon-o-arrow-path',
                        'shipped' => 'heroicon-o-truck',
                        'delivered' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                // حالة الدفع (Payment Status Badge)
                TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'غير مدفوع',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مُسترد',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'gray',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'unpaid' => 'heroicon-o-clock',
                        'paid' => 'heroicon-o-check-badge',
                        'failed' => 'heroicon-o-x-circle',
                        'refunded' => 'heroicon-o-arrow-uturn-left',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                // طريقة الدفع
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cod' => 'عند الاستلام',
                        'card' => 'بطاقة',
                        'instapay' => 'InstaPay',
                        default => $state,
                    })
                    ->color('info')
                    ->toggleable(),

                // تاريخ الإنشاء
                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans())
                    ->toggleable(),
            ])
            ->filters([
                // فلتر حالة الطلب (Multi-Select)
                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->multiple()
                    ->options([
                        'pending' => 'جديد',
                        'processing' => 'قيد المعالجة',
                        'shipped' => 'تم الشحن',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    ])
                    ->placeholder('كل الحالات'),

                // فلتر حالة الدفع
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->multiple()
                    ->options([
                        'unpaid' => 'غير مدفوع',
                        'paid' => 'مدفوع',
                        'failed' => 'فشل',
                        'refunded' => 'مُسترد',
                    ])
                    ->placeholder('كل حالات الدفع'),

                // فلتر طريقة الدفع
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cod' => 'عند الاستلام',
                        'card' => 'بطاقة',
                        'instapay' => 'InstaPay',
                    ])
                    ->placeholder('كل الطرق'),

                // فلتر بالتاريخ (Date Range)
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('من تاريخ')
                            ->placeholder('اختر التاريخ'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('إلى تاريخ')
                            ->placeholder('اختر التاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'من: ' . \Carbon\Carbon::parse($data['created_from'])->format('d/m/Y');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'إلى: ' . \Carbon\Carbon::parse($data['created_until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

                // فلتر بالعميل (البحث بالاسم أو الإيميل)
                Filter::make('customer')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('customer_search')
                            ->label('بحث عن عميل')
                            ->placeholder('اسم أو إيميل العميل'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['customer_search'],
                            fn (Builder $query, $search): Builder => $query->whereHas('user', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            }),
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['customer_search']) {
                            return null;
                        }

                        return 'العميل: ' . $data['customer_search'];
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                ViewAction::make()
                    ->label('عرض التفاصيل'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
