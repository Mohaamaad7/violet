<?php

namespace App\Filament\Resources\DashboardConfig;

use App\Filament\Resources\DashboardConfig\Pages\ListResourceConfigurations;
use App\Filament\Resources\DashboardConfig\Pages\EditResourceConfiguration;
use App\Models\ResourceConfiguration;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResourceConfigurationResource extends Resource
{
    protected static ?string $model = ResourceConfiguration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'resource_name';

    protected static ?int $navigationSort = 101;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.dashboard_config.resources');
    }

    public static function getModelLabel(): string
    {
        return __('admin.dashboard_config.resource');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.dashboard_config.resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.dashboard_config.resource_info'))
                    ->schema([
                        TextInput::make('resource_name')
                            ->label(__('admin.table.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('resource_class')
                            ->label(__('admin.dashboard_config.class'))
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('navigation_group')
                            ->label(__('admin.dashboard_config.nav_group'))
                            ->options(function () {
                                return \App\Models\NavigationGroupConfiguration::pluck('group_label_en', 'group_key');
                            })
                            ->searchable(),

                        TextInput::make('icon')
                            ->label(__('admin.dashboard_config.icon'))
                            ->placeholder('heroicon-o-...')
                            ->helperText('Heroicon name'),
                    ])
                    ->columns(2),

                Section::make(__('admin.dashboard_config.display_settings'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('admin.table.active'))
                            ->default(true),

                        TextInput::make('default_navigation_sort')
                            ->label(__('admin.dashboard_config.nav_sort'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('resource_name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('navigation_group')
                    ->label(__('admin.dashboard_config.nav_group'))
                    ->badge()
                    ->color('info')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label(__('admin.table.active'))
                    ->boolean(),

                TextColumn::make('default_navigation_sort')
                    ->label(__('admin.dashboard_config.nav_sort'))
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('roleAccess')
                    ->label(__('admin.dashboard_config.roles_with_access'))
                    ->counts('roleAccess')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('navigation_group')
            ->filters([
                SelectFilter::make('navigation_group')
                    ->label(__('admin.dashboard_config.nav_group'))
                    ->options(function () {
                        return \App\Models\NavigationGroupConfiguration::pluck('group_label_en', 'group_key');
                    }),

                SelectFilter::make('is_active')
                    ->label(__('admin.table.active'))
                    ->options([
                        '1' => __('admin.status_values.active'),
                        '0' => __('admin.status_values.inactive'),
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResourceConfigurations::route('/'),
            'edit' => EditResourceConfiguration::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Resources are auto-discovered
    }
}
