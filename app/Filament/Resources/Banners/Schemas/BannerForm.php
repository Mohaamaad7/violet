<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Banner Information')
                    ->description('Promotional banner details')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title (Internal Reference)')
                            ->maxLength(255)
                            ->placeholder('e.g., Homepage Sidebar Banner')
                            ->helperText('This is for internal reference only, not displayed to customers'),
                        
                        TextInput::make('link_url')
                            ->label('Link URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com/promotion'),
                        
                        Select::make('position')
                            ->label('Position')
                            ->required()
                            ->options([
                                'homepage_top' => 'Homepage - Top',
                                'homepage_middle' => 'Homepage - Middle',
                                'homepage_bottom' => 'Homepage - Bottom',
                                'sidebar_top' => 'Sidebar - Top',
                                'sidebar_middle' => 'Sidebar - Middle',
                                'sidebar_bottom' => 'Sidebar - Bottom',
                                'category_page' => 'Category Page',
                                'product_page' => 'Product Page',
                            ])
                            ->searchable()
                            ->helperText('Select where this banner should be displayed'),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active banners will be displayed'),
                    ])
                    ->columns(2),
                
                Section::make('Banner Image')
                    ->description('Upload the banner image')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->required()
                            ->disk('public')
                            ->directory('banners')
                            ->maxSize(5120) // 5MB
                            ->imageEditor()
                            ->helperText('Upload banner image. Max 5MB.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
