<?php

namespace App\Filament\Resources\StockCounts;

use App\Filament\Resources\StockCounts\Pages\CreateStockCount;
use App\Filament\Resources\StockCounts\Pages\ListStockCounts;
use App\Filament\Resources\StockCounts\Pages\ViewStockCount;
use App\Filament\Resources\StockCounts\Schemas\StockCountForm;
use App\Filament\Resources\StockCounts\Tables\StockCountsTable;
use App\Models\StockCount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockCountResource extends Resource
{
    protected static ?string $model = StockCount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.inventory');
    }

    public static function getNavigationLabel(): string
    {
        return __('inventory.stock_counts');
    }

    public static function getModelLabel(): string
    {
        return __('inventory.stock_count');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inventory.stock_counts');
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::pending()->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return StockCountForm::configure($schema)->columns(1);
    }

    public static function table(Table $table): Table
    {
        return StockCountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\StockCounts\RelationManagers\StockCountItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockCounts::route('/'),
            'create' => CreateStockCount::route('/create'),
            'view' => ViewStockCount::route('/{record}'),
        ];
    }
}
