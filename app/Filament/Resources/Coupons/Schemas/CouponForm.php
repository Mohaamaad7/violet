<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Section 1: Basic Information
                Section::make(__('admin.coupons.form.basic.title'))
                    ->description(__('admin.coupons.form.basic.desc'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('admin.coupons.form.code'))
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('WINTER50'),

                        Select::make('type')
                            ->label(__('admin.coupons.form.type'))
                            ->options([
                                'general' => __('admin.coupons.types.general'),
                                'influencer' => __('admin.coupons.types.influencer'),
                                'campaign' => __('admin.coupons.types.campaign'),
                            ])
                            ->default('general')
                            ->required()
                            ->native(false),

                        Textarea::make('internal_notes')
                            ->label(__('admin.coupons.form.internal_notes'))
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText(__('admin.coupons.form.internal_notes_help')),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Section 2: Discount Settings
                Section::make(__('admin.coupons.form.discount.title'))
                    ->description(__('admin.coupons.form.discount.desc'))
                    ->schema([
                        Select::make('discount_type')
                            ->label(__('admin.coupons.form.discount_type'))
                            ->options([
                                'percentage' => __('admin.coupons.discount_types.percentage'),
                                'fixed' => __('admin.coupons.discount_types.fixed'),
                                'free_shipping' => __('admin.coupons.discount_types.free_shipping'),
                            ])
                            ->default('percentage')
                            ->required()
                            ->live()
                            ->native(false),

                        TextInput::make('discount_value')
                            ->label(__('admin.coupons.form.discount_value'))
                            ->numeric()
                            ->required(fn($get) => $get('discount_type') !== 'free_shipping')
                            ->visible(fn($get) => $get('discount_type') !== 'free_shipping')
                            ->suffix(fn($get) => $get('discount_type') === 'percentage' ? '%' : __('messages.currency.egp'))
                            ->minValue(0)
                            ->step(0.01),

                        TextInput::make('max_discount_amount')
                            ->label(__('admin.coupons.form.max_discount'))
                            ->numeric()
                            ->visible(fn($get) => $get('discount_type') === 'percentage')
                            ->suffix(__('messages.currency.egp'))
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText(__('admin.coupons.form.max_discount_help')),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Section 3: Conditions
                Section::make(__('admin.coupons.form.conditions.title'))
                    ->description(__('admin.coupons.form.conditions.desc'))
                    ->schema([
                        TextInput::make('min_order_amount')
                            ->label(__('admin.coupons.form.min_order'))
                            ->numeric()
                            ->default(0)
                            ->suffix(__('messages.currency.egp'))
                            ->minValue(0)
                            ->step(0.01),

                        DateTimePicker::make('starts_at')
                            ->label(__('admin.coupons.form.starts_at'))
                            ->native(false),

                        DateTimePicker::make('expires_at')
                            ->label(__('admin.coupons.form.expires_at'))
                            ->native(false)
                            ->after('starts_at'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Section 4: Usage Limits
                Section::make(__('admin.coupons.form.limits.title'))
                    ->description(__('admin.coupons.form.limits.desc'))
                    ->schema([
                        TextInput::make('usage_limit')
                            ->label(__('admin.coupons.form.usage_limit'))
                            ->numeric()
                            ->minValue(1)
                            ->helperText(__('admin.coupons.form.usage_limit_help')),

                        TextInput::make('usage_limit_per_user')
                            ->label(__('admin.coupons.form.usage_per_user'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText(__('admin.coupons.form.usage_per_user_help')),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Section 5: Targeting (Products & Categories)
                Section::make(__('admin.coupons.form.targeting.title'))
                    ->description(__('admin.coupons.form.targeting.desc'))
                    ->schema([
                        Select::make('applies_to_categories')
                            ->label(__('admin.coupons.form.applies_categories'))
                            ->multiple()
                            ->options(fn() => \App\Models\Category::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText(__('admin.coupons.form.applies_categories_help')),

                        Select::make('applies_to_products')
                            ->label(__('admin.coupons.form.applies_products'))
                            ->multiple()
                            ->options(fn() => \App\Models\Product::pluck('name', 'id'))
                            ->searchable()
                            ->helperText(__('admin.coupons.form.applies_products_help')),

                        Select::make('exclude_categories')
                            ->label(__('admin.coupons.form.exclude_categories'))
                            ->multiple()
                            ->options(fn() => \App\Models\Category::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText(__('admin.coupons.form.exclude_categories_help')),

                        Select::make('exclude_products')
                            ->label(__('admin.coupons.form.exclude_products'))
                            ->multiple()
                            ->options(fn() => \App\Models\Product::where('sale_price', '>', 0)->pluck('name', 'id'))
                            ->searchable()
                            ->helperText(__('admin.coupons.form.exclude_products_help')),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                // Section 6: Settings
                Section::make(__('admin.coupons.form.settings.title'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('admin.coupons.form.is_active'))
                            ->default(true)
                            ->helperText(__('admin.coupons.form.is_active_help')),

                        Select::make('influencer_id')
                            ->label(__('admin.coupons.form.influencer'))
                            ->relationship('influencer', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->user?->name ?? 'Influencer #' . $record->id)
                            ->searchable()
                            ->preload()
                            ->visible(fn($get) => $get('type') === 'influencer'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
