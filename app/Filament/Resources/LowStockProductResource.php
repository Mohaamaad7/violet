<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LowStockProductResource\Pages;
use App\Models\Product;
use App\Services\StockMovementService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LowStockProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-exclamation-triangle';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationLabel(): string
    {
        return __('inventory.low_stock_products');
    }

    public static function getModelLabel(): string
    {
        return __('inventory.low_stock_products');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inventory.low_stock_products');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.inventory');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('stock', '>', 0)
            ->where('status', 'active')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();
        return $count > 0 ? 'warning' : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('stock', '>', 0)
            ->where('status', 'active')
            ->orderBy('stock', 'asc');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primary_image')
                    ->label(__('admin.table.image'))
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png'))
                    ->size(50),

                TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->url(fn(Product $record): string => route('filament.admin.resources.products.edit', $record)),

                TextColumn::make('sku')
                    ->label(__('admin.table.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('stock')
                    ->label(__('inventory.current_stock'))
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('low_stock_threshold')
                    ->label(__('inventory.low_stock_threshold'))
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('price')
                    ->label(__('admin.table.price'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label(__('admin.table.category'))
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('stock', 'asc')
            ->recordActions([
                Action::make('add_stock')
                    ->label(__('inventory.add_stock'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('inventory.quantity'))
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Select::make('type')
                            ->label(__('inventory.movement_type'))
                            ->options([
                                'restock' => __('inventory.restock'),
                                'adjustment' => __('inventory.adjustment'),
                            ])
                            ->default('restock')
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('inventory.notes'))
                            ->placeholder(__('inventory.notes_placeholder'))
                            ->maxLength(500),
                    ])
                    ->action(function (Product $record, array $data): void {
                        app(StockMovementService::class)->addStock(
                            $record,
                            $data['quantity'],
                            $data['type'],
                            $data['notes'] ?? null,
                            auth()->id()
                        );

                        Notification::make()
                            ->success()
                            ->title(__('inventory.stock_updated_successfully'))
                            ->body(__('inventory.new_stock_is') . ': ' . $record->fresh()->stock)
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Empty - no bulk actions for this resource
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLowStockProducts::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
