<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use App\Models\Warehouse;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('inventory.warehouse_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('inventory.warehouse_name'))
                            ->required()
                            ->maxLength(255),

                        Select::make('parent_id')
                            ->label(__('inventory.parent_warehouse'))
                            ->options(function ($record) {
                                // Get all warehouses except self and descendants
                                $query = Warehouse::active();

                                if ($record) {
                                    // Exclude self
                                    $query->where('id', '!=', $record->id);
                                }

                                return $query->get()->mapWithKeys(function ($warehouse) {
                                    $prefix = str_repeat('— ', $warehouse->depth);
                                    return [$warehouse->id => $prefix . $warehouse->name];
                                });
                            })
                            ->searchable()
                            ->placeholder(__('inventory.root_warehouse'))
                            ->helperText(__('inventory.parent_warehouse_hint')),

                        TextInput::make('phone')
                            ->label(__('admin.phone'))
                            ->tel()
                            ->maxLength(20),

                        Textarea::make('address')
                            ->label(__('admin.address'))
                            ->rows(2)
                            ->maxLength(500),

                        Toggle::make('is_default')
                            ->label(__('inventory.is_default_warehouse'))
                            ->helperText(__('inventory.default_warehouse_hint'))
                            ->default(false),

                        Toggle::make('is_active')
                            ->label(__('admin.active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
