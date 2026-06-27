<?php

namespace App\Filament\Resources\ComboRules;

use App\Filament\Resources\ComboRules\Pages\CreateComboRule;
use App\Filament\Resources\ComboRules\Pages\EditComboRule;
use App\Filament\Resources\ComboRules\Pages\ListComboRules;
use App\Filament\Resources\ComboRules\Schemas\ComboRuleForm;
use App\Filament\Resources\ComboRules\Tables\ComboRulesTable;
use App\Models\ComboRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComboRuleResource extends Resource
{
    protected static ?string $model = ComboRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'عروض الكومبو';
    protected static ?string $modelLabel = 'عرض كومبو';
    protected static ?string $pluralModelLabel = 'عروض الكومبو';
    protected static \UnitEnum|string|null $navigationGroup = 'التسويق والعروض';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ComboRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComboRulesTable::configure($table);
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
            'index' => ListComboRules::route('/'),
            'create' => CreateComboRule::route('/create'),
            'edit' => EditComboRule::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
