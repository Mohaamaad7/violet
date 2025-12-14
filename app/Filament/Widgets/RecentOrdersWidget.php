<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
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
    protected static ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('admin.widgets.recent_orders.heading');
    }

    /**
     * Widget sort order (displayed after stats cards)
     */
    protected static ?int $sort = 2;

    /**
     * Number of rows to display per page
     */
    protected int|string|array $defaultPaginationPageOption = 10;

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
                    ->label(__('admin.widgets.recent_orders.order_number'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage(__('admin.widgets.recent_orders.copied')),

                // Column 2: Customer Name
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.widgets.recent_orders.customer'))
                    ->searchable()
                    ->sortable(),

                // Column 3: Status (Colored Badge)
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.widgets.recent_orders.status'))
                    ->badge()
                    ->color(fn($state): string => $state instanceof OrderStatus ? $state->color() : 'gray')
                    ->formatStateUsing(fn($state): string => $state instanceof OrderStatus ? $state->label() : $state),

                // Column 4: Total
                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.widgets.recent_orders.total'))
                    ->money('EGP')
                    ->sortable()
                    ->weight('bold'),
            ])
            ->actions([
                // Row action to view order details
                Action::make('view')
                    ->label(__('admin.action.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn(Order $record): string => route('filament.admin.resources.orders.view', ['record' => $record->id]))
                    ->color('primary'),
            ])
            ->headerActions([
                // Header action to view all orders
                Action::make('viewAll')
                    ->label(__('admin.widgets.recent_orders.view_all'))
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
