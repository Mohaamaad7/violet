<?php

namespace App\Filament\Resources\DashboardConfig;

use App\Filament\Resources\DashboardConfig\Pages\CreateNavigationGroupConfiguration;
use App\Filament\Resources\DashboardConfig\Pages\EditNavigationGroupConfiguration;
use App\Filament\Resources\DashboardConfig\Pages\ListNavigationGroupConfigurations;
use App\Models\NavigationGroupConfiguration;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NavigationGroupConfigurationResource extends Resource
{
    protected static ?string $model = NavigationGroupConfiguration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static ?string $recordTitleAttribute = 'group_label_en';

    protected static ?int $navigationSort = 102;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.dashboard_config.nav_groups');
    }

    public static function getModelLabel(): string
    {
        return __('admin.dashboard_config.nav_group');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.dashboard_config.nav_groups');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.dashboard_config.group_info'))
                    ->schema([
                        TextInput::make('group_key')
                            ->label(__('admin.dashboard_config.group_key'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->alphaDash()
                            ->helperText('e.g. catalog, sales, inventory'),

                        TextInput::make('icon')
                            ->label(__('admin.dashboard_config.icon'))
                            ->placeholder('heroicon-o-...')
                            ->helperText('Heroicon name'),
                    ])
                    ->columns(2),

                Section::make(__('admin.dashboard_config.labels'))
                    ->schema([
                        TextInput::make('group_label_ar')
                            ->label(__('admin.dashboard_config.label_ar'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('group_label_en')
                            ->label(__('admin.dashboard_config.label_en'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make(__('admin.dashboard_config.display_settings'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('admin.table.active'))
                            ->default(true),

                        TextInput::make('default_order')
                            ->label(__('admin.dashboard_config.order'))
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
                TextColumn::make('group_key')
                    ->label(__('admin.dashboard_config.group_key'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('group_label_ar')
                    ->label(__('admin.dashboard_config.label_ar'))
                    ->searchable(),

                TextColumn::make('group_label_en')
                    ->label(__('admin.dashboard_config.label_en'))
                    ->searchable(),

                TextColumn::make('icon')
                    ->label(__('admin.dashboard_config.icon'))
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label(__('admin.table.active'))
                    ->boolean(),

                TextColumn::make('default_order')
                    ->label(__('admin.dashboard_config.order'))
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('roles')
                    ->label(__('admin.dashboard_config.roles_using'))
                    ->counts('roles')
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('default_order')
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('admin.table.active'))
                    ->options([
                        '1' => __('admin.status_values.active'),
                        '0' => __('admin.status_values.inactive'),
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNavigationGroupConfigurations::route('/'),
            'create' => CreateNavigationGroupConfiguration::route('/create'),
            'edit' => EditNavigationGroupConfiguration::route('/{record}/edit'),
        ];
    }
}
