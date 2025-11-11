<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TranslationResource\Pages;
use App\Models\Translation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class TranslationResource extends Resource
{
    protected static ?string $model = Translation::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-language';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'System';
    }

    public static function getNavigationSort(): ?int
    {
        return 50;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('key')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Select::make('locale')
                ->options([
                    'ar' => 'Arabic',
                    'en' => 'English',
                ])
                ->required(),
            TextInput::make('group')
                ->maxLength(255)
                ->label('Group (optional)'),
            Toggle::make('is_active')
                ->default(true),
            Textarea::make('value')
                ->rows(6)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->searchable()->sortable()->wrap(),
                TextColumn::make('locale')->sortable()->badge(),
                TextColumn::make('group')->toggleable(isToggledHiddenByDefault: true)->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->options([
                        'ar' => 'Arabic',
                        'en' => 'English',
                    ]),
                TernaryFilter::make('is_active'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTranslations::route('/'),
        ];
    }
}
