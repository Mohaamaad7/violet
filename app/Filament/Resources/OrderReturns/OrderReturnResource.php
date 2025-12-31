<?php

namespace App\Filament\Resources\OrderReturns;

use App\Filament\Resources\Concerns\ChecksResourceAccess;
use App\Filament\Resources\OrderReturns\Pages\ListOrderReturns;
use App\Filament\Resources\OrderReturns\Pages\ViewOrderReturn;
use App\Filament\Resources\OrderReturns\Schemas\OrderReturnInfolist;
use App\Filament\Resources\OrderReturns\Tables\OrderReturnsTable;
use App\Models\OrderReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderReturnResource extends Resource
{
    use ChecksResourceAccess;

    protected static ?string $model = OrderReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?string $recordTitleAttribute = 'return_number';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('inventory.returns');
    }

    public static function getModelLabel(): string
    {
        return __('inventory.return');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inventory.returns');
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderReturnInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderReturns::route('/'),
            'view' => ViewOrderReturn::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Returns are created from Orders only
    }
}
