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
        return __('admin.nav.system');
    }

    public static function getNavigationSort(): ?int
    {
        return 50;
    }
    
    public static function getNavigationLabel(): string
    {
        return __('admin.translations.title');
    }
    
    public static function getModelLabel(): string
    {
        return __('admin.translations.singular');
    }
    
    public static function getPluralLabel(): string
    {
        return __('admin.translations.plural');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('key')
                ->required()
                ->maxLength(255)
                ->disabled(fn ($context) => $context === 'edit')
                ->dehydrated()
                ->unique(table: Translation::class, column: 'key', ignoreRecord: true, modifyRuleUsing: function ($rule, $context) {
                    if ($context === 'edit') {
                        return $rule->where(function ($query) {
                            return $query->whereRaw('1 = 0'); // Disable validation in edit mode
                        });
                    }
                    return $rule;
                }),
            Select::make('locale')
                ->options([
                    'ar' => 'Arabic',
                    'en' => 'English',
                ])
                ->required()
                ->disabled(fn ($context) => $context === 'edit')
                ->dehydrated(),
            TextInput::make('group')
                ->maxLength(255)
                ->label('Group (optional)')
                ->disabled(fn ($context) => $context === 'edit')
                ->dehydrated(),
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
                TextColumn::make('value')
                    ->label('Translation')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->value),
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
                EditAction::make()
                    ->visible(fn ($record) => auth()->user()->can('update', $record)),
                DeleteAction::make()
                    ->visible(fn ($record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('super-admin')),
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
