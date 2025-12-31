<?php

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Concerns\ChecksResourceAccess;
use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Pages\ViewCoupon;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Models\DiscountCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CouponResource extends Resource
{
    use ChecksResourceAccess;

    protected static ?string $model = DiscountCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $recordTitleAttribute = 'code';

    protected static UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.coupons.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.coupons.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.coupons.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'view' => ViewCoupon::route('/{record}'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
