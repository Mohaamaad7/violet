<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // General Information Section
                Section::make(__('admin.products.form.general.title'))
                    ->description(__('admin.products.form.general.desc'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.form.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label(__('admin.form.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('admin.products.form.general.slug_help')),

                        TextInput::make('sku')
                            ->label(__('admin.form.sku'))
                            ->maxLength(100)
                            ->helperText(__('admin.products.form.general.sku_help')),

                        Select::make('category_id')
                            ->label(__('admin.form.category'))
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('admin.form.name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->label(__('admin.form.slug'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        RichEditor::make('description')
                            ->label(__('admin.form.description'))
                            ->columnSpanFull()
                            ->toolbarButtons([
                                // Row 1: Text Formatting
                                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                                // Row 2: Colors, Headings & Alignment
                                ['textColor', 'highlight', 'clearFormatting'],
                                ['h1', 'h2', 'h3'],
                                ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                                // Row 3: Structure & Media
                                ['bulletList', 'orderedList', 'blockquote', 'table'],
                                ['attachFiles'],
                                ['undo', 'redo'],
                            ])
                            ->textColors([
                                '#000000' => 'Black',
                                '#ef4444' => 'Red',
                                '#f97316' => 'Orange',
                                '#eab308' => 'Yellow',
                                '#22c55e' => 'Green',
                                '#3b82f6' => 'Blue',
                                '#6366f1' => 'Indigo',
                                '#a855f7' => 'Purple',
                                '#ec4899' => 'Pink',
                                '#6b7280' => 'Gray',
                                '#ffffff' => 'White',
                            ]),

                        Textarea::make('short_description')
                            ->label(__('admin.products.form.general.short_description'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(__('admin.products.form.general.short_description_help')),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Detailed Content Section
                Section::make(__('admin.products.form.detailed.title'))
                    ->description(__('admin.products.form.detailed.desc'))
                    ->schema([
                        RichEditor::make('long_description')
                            ->label(__('admin.products.form.detailed.long_description'))
                            ->columnSpanFull()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                                ['textColor', 'highlight', 'clearFormatting'],
                                ['h1', 'h2', 'h3'],
                                ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                                ['bulletList', 'orderedList', 'blockquote', 'table'],
                                ['attachFiles'],
                                ['undo', 'redo'],
                            ])
                            ->textColors([
                                '#000000' => 'Black',
                                '#ef4444' => 'Red',
                                '#f97316' => 'Orange',
                                '#eab308' => 'Yellow',
                                '#22c55e' => 'Green',
                                '#3b82f6' => 'Blue',
                                '#6366f1' => 'Indigo',
                                '#a855f7' => 'Purple',
                                '#ec4899' => 'Pink',
                                '#6b7280' => 'Gray',
                                '#ffffff' => 'White',
                            ])
                            ->helperText(__('admin.products.form.detailed.long_description_help')),

                        RichEditor::make('specifications')
                            ->label(__('admin.products.form.detailed.specifications'))
                            ->columnSpanFull()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['textColor', 'highlight', 'clearFormatting'],
                                ['h2', 'h3'],
                                ['alignStart', 'alignCenter', 'alignEnd'],
                                ['bulletList', 'orderedList', 'table'],
                                ['undo', 'redo'],
                            ])
                            ->textColors([
                                '#000000' => 'Black',
                                '#ef4444' => 'Red',
                                '#22c55e' => 'Green',
                                '#3b82f6' => 'Blue',
                                '#6b7280' => 'Gray',
                            ])
                            ->helperText(__('admin.products.form.detailed.specifications_help')),

                        RichEditor::make('how_to_use')
                            ->label(__('admin.products.form.detailed.how_to_use'))
                            ->columnSpanFull()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['textColor', 'highlight', 'clearFormatting'],
                                ['h2', 'h3'],
                                ['alignStart', 'alignCenter', 'alignEnd'],
                                ['bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->textColors([
                                '#000000' => 'Black',
                                '#ef4444' => 'Red',
                                '#22c55e' => 'Green',
                                '#3b82f6' => 'Blue',
                                '#6b7280' => 'Gray',
                            ])
                            ->helperText(__('admin.products.form.detailed.how_to_use_help')),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Media Section
                Section::make(__('admin.products.form.media.title'))
                    ->description(__('admin.products.form.media.desc'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('media')
                            ->label(__('admin.products.form.media.images_label'))
                            ->collection('product-images')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->image()
                            ->imageEditor() // Optional editor - user can crop if they want
                            // Removed forced 1:1 crop to preserve original aspect ratio
                            // Images will be resized in conversions with padding
                            ->conversion('thumbnail')
                            ->conversionsDisk('public')
                            ->panelLayout('grid')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->uploadProgressIndicatorPosition('left')
                            ->helperText(__('admin.products.form.media.images_help'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // Pricing Section
                Section::make(__('admin.products.form.pricing.title'))
                    ->description(__('admin.products.form.pricing.desc'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('admin.form.price'))
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01),

                        TextInput::make('sale_price')
                            ->label(__('admin.products.form.pricing.sale_price'))
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText(__('admin.products.form.pricing.sale_price_help')),

                        TextInput::make('cost_price')
                            ->label(__('admin.products.form.pricing.cost_price'))
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText(__('admin.products.form.pricing.cost_price_help')),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Inventory & Physical Properties Section
                Section::make(__('admin.products.form.inventory.title'))
                    ->description(__('admin.products.form.inventory.desc'))
                    ->schema([
                        TextInput::make('barcode')
                            ->label(__('admin.products.form.inventory.barcode'))
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('admin.products.form.inventory.barcode_help')),

                        TextInput::make('weight')
                            ->label(__('admin.products.form.inventory.weight'))
                            ->numeric()
                            ->suffix(__('admin.unit.kg'))
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText(__('admin.products.form.inventory.weight_help')),

                        TextInput::make('low_stock_threshold')
                            ->label(__('admin.products.form.inventory.low_stock_alert'))
                            ->required()
                            ->numeric()
                            ->default(5)
                            ->minValue(0)
                            ->helperText(__('admin.products.form.inventory.low_stock_help')),

                        TextInput::make('stock')
                            ->label(__('admin.form.stock'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(__('admin.products.form.inventory.stock_readonly_help'))
                            ->suffix(__('admin.unit.units')),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Product Variants Section
                Section::make(__('admin.products.form.variants.title'))
                    ->description(__('admin.products.form.variants.desc'))
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                TextInput::make('sku')
                                    ->label(__('admin.products.form.variants.variant_sku'))
                                    ->required()
                                    ->maxLength(100)
                                    ->unique(table: 'product_variants', ignoreRecord: true),

                                TextInput::make('name')
                                    ->label(__('admin.form.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('admin.products.form.variants.name_placeholder')),

                                TextInput::make('price')
                                    ->label(__('admin.form.price'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText(__('admin.products.form.variants.price_help')),

                                TextInput::make('stock')
                                    ->label(__('admin.form.stock'))
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                            ->addActionLabel(__('admin.products.form.variants.add_variant'))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Additional Settings Section
                Section::make(__('admin.products.form.additional.title'))
                    ->description(__('admin.products.form.additional.desc'))
                    ->schema([
                        Select::make('status')
                            ->label(__('admin.table.status'))
                            ->options([
                                'draft' => __('admin.status.draft'),
                                'active' => __('admin.status.active'),
                                'inactive' => __('admin.status.inactive'),
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),

                        Toggle::make('is_featured')
                            ->label(__('admin.products.form.additional.featured_product'))
                            ->default(false)
                            ->helperText(__('admin.products.form.additional.featured_help')),

                        TextInput::make('brand')
                            ->label(__('admin.products.form.additional.brand'))
                            ->maxLength(100),

                        TextInput::make('meta_title')
                            ->label(__('admin.products.form.additional.seo_title'))
                            ->maxLength(255)
                            ->helperText(__('admin.products.form.additional.seo_help')),

                        Textarea::make('meta_description')
                            ->label(__('admin.products.form.additional.seo_description'))
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('meta_keywords')
                            ->label(__('admin.products.form.additional.seo_keywords'))
                            ->rows(2)
                            ->helperText(__('admin.products.form.additional.seo_keywords_help'))
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
