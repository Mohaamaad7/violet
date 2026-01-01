<?php

namespace App\Filament\Resources\Influencers;

use App\Filament\Resources\Concerns\ChecksResourceAccess;
use App\Filament\Resources\Influencers\Pages\ListApplications;
use App\Filament\Resources\Influencers\Pages\ViewApplication;
use App\Filament\Resources\Influencers\Schemas\ApplicationForm;
use App\Filament\Resources\Influencers\Tables\ApplicationsTable;
use App\Models\InfluencerApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InfluencerApplicationResource extends Resource
{
    use ChecksResourceAccess;

    protected static ?string $model = InfluencerApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return trans_db('admin.nav.influencers');
    }

    public static function getNavigationLabel(): string
    {
        return trans_db('admin.applications.title');
    }

    public static function getModelLabel(): string
    {
        return trans_db('admin.applications.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return trans_db('admin.applications.plural');
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
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'view' => ViewApplication::route('/{record}'),
        ];
    }
}
