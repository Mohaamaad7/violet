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
                Section::make('General Information')
                    ->description('Basic product details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from name, but can be edited'),
                        
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(100)
                            ->helperText('Leave empty for auto-generation'),
                        
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        
                        RichEditor::make('description')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ]),
                        
                        Textarea::make('short_description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Brief description for listing pages'),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                // Media Section
                Section::make('Media')
                    ->description('Upload product images - First image will be primary')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('media')
                            ->label('Product Images')
                            ->collection('product-images')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('800')
                            ->conversion('thumbnail')
                            ->conversionsDisk('public')
                            ->panelLayout('grid')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->uploadProgressIndicatorPosition('left')
                            ->helperText('Upload up to 10 images. Drag to reorder. First image will be primary.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                
                // Pricing Section
                Section::make('Pricing')
                    ->description('Product pricing information')
                    ->schema([
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01),
                        
                        TextInput::make('sale_price')
                            ->label('Sale Price')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Optional. If set, will be displayed as discounted price'),
                        
                        TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Internal cost for profit calculation'),
                    ])
                    ->columns(3)
                    ->collapsible(),
                
                // Inventory Section
                Section::make('Inventory')
                    ->description('Stock management')
                    ->schema([
                        TextInput::make('stock')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        
                        TextInput::make('low_stock_threshold')
                            ->label('Low Stock Alert')
                            ->required()
                            ->numeric()
                            ->default(5)
                            ->minValue(0)
                            ->helperText('Get notified when stock reaches this level'),
                        
                        TextInput::make('weight')
                            ->numeric()
                            ->suffix('kg')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('For shipping calculations'),
                        
                        TextInput::make('barcode')
                            ->maxLength(100),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                // Product Variants Section
                Section::make('Product Variants')
                    ->description('Size, color, or other variations')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                TextInput::make('sku')
                                    ->label('Variant SKU')
                                    ->required()
                                    ->maxLength(100)
                                    ->unique(table: 'product_variants', ignoreRecord: true),
                                
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Red - Large, 128GB'),
                                
                                TextInput::make('price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Leave empty to use product price'),
                                
                                TextInput::make('stock')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->addActionLabel('Add Variant')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
                
                // Additional Settings Section
                Section::make('Additional Settings')
                    ->description('Status, features, and metadata')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                        
                        Toggle::make('is_featured')
                            ->label('Featured Product')
                            ->default(false)
                            ->helperText('Show on homepage'),
                        
                        TextInput::make('brand')
                            ->maxLength(100),
                        
                        TextInput::make('meta_title')
                            ->label('SEO Title')
                            ->maxLength(255)
                            ->helperText('For search engines'),
                        
                        Textarea::make('meta_description')
                            ->label('SEO Description')
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Textarea::make('meta_keywords')
                            ->label('SEO Keywords')
                            ->rows(2)
                            ->helperText('Comma-separated keywords')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
