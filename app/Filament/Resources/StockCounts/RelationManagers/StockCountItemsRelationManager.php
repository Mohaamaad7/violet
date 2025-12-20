<?php

namespace App\Filament\Resources\StockCounts\RelationManagers;

use App\Enums\StockCountStatus;
use App\Services\StockCountService;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class StockCountItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'أصناف الجرد';

    public function table(Table $table): Table
    {
        // Only editable during IN_PROGRESS status
        $isEditable = $this->getOwnerRecord()->canEditItems();

        $columns = [
            TextColumn::make('sku')
                ->label('SKU')
                ->searchable()
                ->sortable(),

            TextColumn::make('display_name')
                ->label('المنتج')
                ->limit(40)
                ->searchable(),

            TextColumn::make('system_quantity')
                ->label('كمية النظام')
                ->alignCenter()
                ->badge()
                ->color('gray'),
        ];

        // Editable column for draft/in_progress, read-only for others
        if ($isEditable) {
            $columns[] = TextInputColumn::make('counted_quantity')
                ->label('الكمية المعدودة')
                ->rules(['nullable', 'integer', 'min:0'])
                ->afterStateUpdated(function ($record, $state, $livewire) {
                    $service = app(StockCountService::class);
                    $service->updateCountItem($record->id, $state !== '' && $state !== null ? (int) $state : null);

                    // Dispatch event to parent page to refresh header actions
                    $livewire->dispatch('stock-count-item-updated');

                    Notification::make()
                        ->success()
                        ->title('تم تحديث الصنف')
                        ->duration(1500)
                        ->send();
                });
        } else {
            $columns[] = TextColumn::make('counted_quantity')
                ->label('الكمية المعدودة')
                ->alignCenter()
                ->badge()
                ->color('info');
        }

        $columns[] = TextColumn::make('difference')
            ->label('الفرق')
            ->alignCenter()
            ->badge()
            ->color(fn($state) => match (true) {
                $state === null => 'gray',
                $state < 0 => 'danger',
                $state > 0 => 'warning',
                default => 'success',
            })
            ->formatStateUsing(fn($state) => $state !== null ? ($state > 0 ? '+' . $state : $state) : '-');

        return $table
            ->columns($columns)
            ->paginated([10, 25, 50, 100])
            ->defaultSort('product_id');
    }
}
