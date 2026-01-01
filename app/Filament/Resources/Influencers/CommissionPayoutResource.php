<?php

namespace App\Filament\Resources\Influencers;

use App\Filament\Resources\Concerns\ChecksResourceAccess;
use App\Filament\Resources\Influencers\Pages\CreatePayout;
use App\Filament\Resources\Influencers\Pages\ListPayouts;
use App\Filament\Resources\Influencers\Pages\ViewPayout;
use App\Filament\Resources\Influencers\Schemas\PayoutForm;
use App\Filament\Resources\Influencers\Tables\PayoutsTable;
use App\Models\CommissionPayout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommissionPayoutResource extends Resource
{
    use ChecksResourceAccess;

    protected static ?string $model = CommissionPayout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return trans_db('admin.nav.influencers');
    }

    public static function getNavigationLabel(): string
    {
        return trans_db('admin.payouts.title');
    }

    public static function getModelLabel(): string
    {
        return trans_db('admin.payouts.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return trans_db('admin.payouts.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        return $pendingCount > 0 ? 'warning' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {
        return PayoutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayoutsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayouts::route('/'),
            'create' => CreatePayout::route('/create'),
            'view' => ViewPayout::route('/{record}'),
        ];
    }
}
