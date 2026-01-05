<?php

namespace App\Filament\Resources\HelpEntries;

use App\Filament\Resources\Concerns\ChecksResourceAccess;
use App\Filament\Resources\HelpEntries\Pages\CreateHelpEntry;
use App\Filament\Resources\HelpEntries\Pages\EditHelpEntry;
use App\Filament\Resources\HelpEntries\Pages\ListHelpEntries;
use App\Filament\Resources\HelpEntries\Schemas\HelpEntryForm;
use App\Filament\Resources\HelpEntries\Tables\HelpEntriesTable;
use App\Models\HelpEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HelpEntryResource extends Resource
{
    use ChecksResourceAccess;

    protected static ?string $model = HelpEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $recordTitleAttribute = 'question';

    protected static ?int $navigationSort = 110;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.help_entries.title');
    }

    public static function getModelLabel(): string
    {
        return __('admin.help_entries.singular');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.help_entries.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return HelpEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpEntries::route('/'),
            'create' => CreateHelpEntry::route('/create'),
            'edit' => EditHelpEntry::route('/{record}/edit'),
        ];
    }
}
