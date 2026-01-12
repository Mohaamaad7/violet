<?php

namespace App\Filament\Resources\EmailCampaigns\EmailCampaigns\Schemas;

use App\Models\DiscountCode;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Get;

class EmailCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make(__('Campaign Information'))
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Campaign Title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label(__('Campaign Type'))
                            ->options([
                                'offers' => __('Offers Campaign'),
                                'custom' => __('Custom Message'),
                                'newsletter' => __('Newsletter'),
                            ])
                            ->required()
                            ->default('custom')
                            ->live()
                            ->afterStateUpdated(fn($state, $set) => $state !== 'offers' ? $set('offers', []) : null),

                        TextInput::make('subject')
                            ->label(__('Email Subject'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('preview_text')
                            ->label(__('Preview Text'))
                            ->helperText(__('This text appears in email previews'))
                            ->maxLength(200)
                            ->columnSpanFull(),

                        RichEditor::make('content_html')
                            ->label(__('Email Content'))
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                            ])
                            ->columnSpanFull(),

                        Select::make('offers')
                            ->label(__('Select Offers'))
                            ->multiple()
                            ->relationship('offers', 'code')
                            ->preload()
                            ->searchable()
                            ->helperText(__('Select discount codes to include in the campaign'))
                            ->visible(fn(Get $get) => $get('type') === 'offers')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Targeting & Settings'))
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'draft' => __('Draft'),
                                'scheduled' => __('Scheduled'),
                                'sent' => __('Sent'),
                                'paused' => __('Paused'),
                                'cancelled' => __('Cancelled'),
                            ])
                            ->default('draft')
                            ->required(),

                        Select::make('send_to')
                            ->label(__('Send To'))
                            ->options([
                                'all' => __('All Subscribers'),
                                'active_only' => __('Active Only'),
                                'recent' => __('Recent (Last 30 Days)'),
                                'custom' => __('Custom Filters'),
                            ])
                            ->default('active_only')
                            ->required()
                            ->helperText(__('Choose your target audience')),

                        DateTimePicker::make('scheduled_at')
                            ->label(__('Schedule For'))
                            ->helperText(__('Leave empty to send immediately'))
                            ->minDate(now())
                            ->seconds(false),

                        TextInput::make('send_rate_limit')
                            ->label(__('Send Rate (emails/minute)'))
                            ->numeric()
                            ->default(50)
                            ->minValue(1)
                            ->maxValue(200)
                            ->helperText(__('Recommended: 50-100 emails per minute')),
                    ]),
            ]);
    }
}
