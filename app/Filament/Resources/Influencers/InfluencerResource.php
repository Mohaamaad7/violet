<?php

namespace App\Filament\Resources\Influencers;

use App\Filament\Resources\Concerns\ChecksResourceAccess;
use App\Filament\Resources\Influencers\Pages\CreateInfluencer;
use App\Filament\Resources\Influencers\Pages\EditInfluencer;
use App\Filament\Resources\Influencers\Pages\ListInfluencers;
use App\Filament\Resources\Influencers\Pages\ViewInfluencer;
use App\Filament\Resources\Influencers\Schemas\InfluencerForm;
use App\Filament\Resources\Influencers\Tables\InfluencersTable;
use App\Models\Influencer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InfluencerResource extends Resource
{
    use ChecksResourceAccess;

    protected static ?string $model = Influencer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return trans_db('admin.nav.influencers');
    }

    public static function getNavigationLabel(): string
    {
        return trans_db('admin.influencers.title');
    }

    public static function getModelLabel(): string
    {
        return trans_db('admin.influencers.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return trans_db('admin.influencers.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return InfluencerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfluencersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInfluencers::route('/'),
            'create' => CreateInfluencer::route('/create'),
            'view' => ViewInfluencer::route('/{record}'),
            'edit' => EditInfluencer::route('/{record}/edit'),
        ];
    }
}

