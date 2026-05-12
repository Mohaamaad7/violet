<?php

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Slider Information')
                    ->description('Main slider details for homepage hero section')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255)
                            ->placeholder('e.g., New Winter Collection'),
                        
                        TextInput::make('subtitle')
                            ->label('Subtitle')
                            ->maxLength(255)
                            ->placeholder('e.g., Shop Now and get 20% off'),
                        
                        TextInput::make('link_url')
                            ->label('Link URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com/collection'),
                        
                        TextInput::make('order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first'),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active sliders will be displayed'),
                    ])
                    ->columns(2),
                
                Section::make('Slider Images')
                    ->description('Upload images for different devices')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Desktop Image')
                            ->image()
                            ->required()
                            ->disk('public')
                            ->directory('sliders')
                            ->maxSize(5120) // 5MB
                            ->imageEditor()
                            ->helperText('Upload desktop slider image. Max 5MB. Recommended: 1920x800px'),
                            
                        FileUpload::make('mobile_image_path')
                            ->label('Mobile Image')
                            ->image()
                            ->disk('public')
                            ->directory('sliders/mobile')
                            ->maxSize(5120) // 5MB
                            ->imageEditor()
                            ->helperText('Upload mobile slider image (Optional). Recommended: 800x1200px. If not provided, desktop image will be used.'),
                    ])->columns(2),
            ]);
    }
}
