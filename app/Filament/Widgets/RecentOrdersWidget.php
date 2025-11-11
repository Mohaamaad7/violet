<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    /**
     * Widget heading
     */
    protected static ?string $heading = 'آخر الطلبات';

    /**
     * Widget sort order (displayed after stats cards)
     */
    protected static ?int $sort = 2;

    /**
     * Number of rows to display per page
     */
    protected int | string | array $defaultPaginationPageOption = 10;

    /**
     * Configure the table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Fetch 10 most recent orders
                Order::query()
                    ->with(['user', 'items'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                // Column 1: Order ID (Code)
                Tables\Columns\TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الطلب'),

                // Column 2: Customer Name
                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),

                // Column 3: Status (Colored Badge)
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد التجهيز',
                        'shipped' => 'تم الشحن',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                        default => $state,
                    }),

                // Column 4: Total
                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold'),
            ])
            ->actions([
                // Row action to view order details
                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', ['record' => $record->id]))
                    ->color('primary'),
            ])
            ->headerActions([
                // Header action to view all orders
                Action::make('viewAll')
                    ->label('عرض جميع الطلبات')
                    ->icon('heroicon-o-arrow-right')
                    ->url(route('filament.admin.resources.orders.index'))
                    ->color('primary'),
            ])
            // Disable filters (read-only table)
            ->filters([])
            // Disable bulk actions (read-only table)
            ->bulkActions([])
            // Disable pagination (show all 10 rows)
            ->paginated(false);
    }
}
