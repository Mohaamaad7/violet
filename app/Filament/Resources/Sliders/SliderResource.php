<?php

namespace App\Filament\Resources\Sliders;

use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Filament\Resources\Sliders\Schemas\SliderForm;
use App\Filament\Resources\Sliders\Tables\SlidersTable;
use App\Models\Slider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';
    
    protected static ?string $navigationLabel = null;
    
    protected static ?string $modelLabel = null;
    
    protected static ?string $pluralModelLabel = null;
    
    protected static UnitEnum|string|null $navigationGroup = null;
    
    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.content');
    }
    
    public static function getNavigationLabel(): string
    {
        return __('admin.sliders.title');
    }
    
    public static function getModelLabel(): string
    {
        return __('admin.sliders.singular');
    }
    
    public static function getPluralLabel(): string
    {
        return __('admin.sliders.plural');
    }
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SliderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlidersTable::configure($table);
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
            'index' => ListSliders::route('/'),
            'create' => CreateSlider::route('/create'),
            'edit' => EditSlider::route('/{record}/edit'),
        ];
    }
}
