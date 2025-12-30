<?php

namespace App\Filament\Resources\DashboardConfig;

use App\Filament\Resources\DashboardConfig\Pages\ListWidgetConfigurations;
use App\Filament\Resources\DashboardConfig\Pages\EditWidgetConfiguration;
use App\Models\WidgetConfiguration;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

class WidgetConfigurationResource extends Resource
{
    protected static ?string $model = WidgetConfiguration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $recordTitleAttribute = 'widget_name';

    protected static ?int $navigationSort = 100;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.dashboard_config.widgets');
    }

    public static function getModelLabel(): string
    {
        return __('admin.dashboard_config.widget');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.dashboard_config.widgets');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.dashboard_config.widget_info'))
                    ->schema([
                        TextInput::make('widget_name')
                            ->label(__('admin.table.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('widget_class')
                            ->label(__('admin.dashboard_config.class'))
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('widget_group')
                            ->label(__('admin.dashboard_config.group'))
                            ->options([
                                'general' => 'General',
                                'sales' => 'Sales',
                                'inventory' => 'Inventory',
                                'customers' => 'Customers',
                            ])
                            ->searchable(),

                        Textarea::make('description')
                            ->label(__('admin.dashboard_config.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('admin.dashboard_config.display_settings'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('admin.table.active'))
                            ->default(true)
                            ->helperText(__('admin.dashboard_config.active_help')),

                        TextInput::make('default_order')
                            ->label(__('admin.dashboard_config.order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Select::make('default_column_span')
                            ->label(__('admin.dashboard_config.column_span'))
                            ->options([
                                1 => '1 Column',
                                2 => '2 Columns',
                                3 => '3 Columns',
                                4 => '4 Columns (Full)',
                            ])
                            ->default(1),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('widget_name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('widget_group')
                    ->label(__('admin.dashboard_config.group'))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'sales' => 'success',
                        'inventory' => 'warning',
                        'customers' => 'info',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label(__('admin.table.active'))
                    ->boolean(),

                TextColumn::make('default_order')
                    ->label(__('admin.dashboard_config.order'))
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('default_column_span')
                    ->label(__('admin.dashboard_config.column_span'))
                    ->badge()
                    ->alignCenter(),

                TextColumn::make('roleDefaults')
                    ->label(__('admin.dashboard_config.roles_using'))
                    ->counts('roleDefaults')
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('default_order')
            ->filters([
                SelectFilter::make('widget_group')
                    ->label(__('admin.dashboard_config.group'))
                    ->options([
                        'general' => 'General',
                        'sales' => 'Sales',
                        'inventory' => 'Inventory',
                        'customers' => 'Customers',
                    ]),

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
            'index' => ListWidgetConfigurations::route('/'),
            'edit' => EditWidgetConfiguration::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Widgets are auto-discovered
    }
}
