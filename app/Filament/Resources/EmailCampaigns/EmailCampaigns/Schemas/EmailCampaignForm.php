<?php

namespace App\Filament\Resources\EmailCampaigns\EmailCampaigns\Schemas;

use App\Models\DiscountCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make(__('newsletter.campaign_information'))
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('newsletter.campaign_title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label(__('newsletter.campaign_type'))
                            ->options([
                                'offers' => __('newsletter.offers_campaign'),
                                'custom' => __('newsletter.custom_message'),
                                'newsletter' => __('newsletter.newsletter'),
                            ])
                            ->required()
                            ->default('custom')
                            ->live()
                            ->afterStateUpdated(fn($state, $set) => $state !== 'offers' ? $set('offers', []) : null),

                        TextInput::make('subject')
                            ->label(__('newsletter.email_subject'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('preview_text')
                            ->label(__('newsletter.preview_text'))
                            ->helperText(__('newsletter.preview_text_help'))
                            ->maxLength(200)
                            ->columnSpanFull(),

                        RichEditor::make('content_html')
                            ->label(__('newsletter.email_content'))
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
                            ->label(__('newsletter.select_offers'))
                            ->multiple()
                            ->relationship('offers', 'code')
                            ->preload()
                            ->searchable()
                            ->helperText(__('newsletter.select_offers_help'))
                            ->visible(fn($get) => $get('type') === 'offers')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('newsletter.targeting_settings'))
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->label(__('newsletter.status'))
                            ->options([
                                'draft' => __('newsletter.draft'),
                                'scheduled' => __('newsletter.scheduled'),
                                'sent' => __('newsletter.sent'),
                                'paused' => __('newsletter.paused'),
                                'cancelled' => __('newsletter.cancelled'),
                            ])
                            ->default('draft')
                            ->required(),

                        Select::make('send_to')
                            ->label(__('newsletter.send_to'))
                            ->options([
                                'all' => __('newsletter.all_subscribers'),
                                'active_only' => __('newsletter.active_only'),
                                'recent' => __('newsletter.recent_30_days'),
                                'custom' => __('newsletter.custom_filters'),
                            ])
                            ->default('active_only')
                            ->required()
                            ->helperText(__('newsletter.choose_audience')),

                        DateTimePicker::make('scheduled_at')
                            ->label(__('newsletter.schedule_for'))
                            ->helperText(__('newsletter.send_immediately'))
                            ->minDate(now())
                            ->seconds(false),

                        TextInput::make('send_rate_limit')
                            ->label(__('newsletter.send_rate'))
                            ->numeric()
                            ->default(50)
                            ->minValue(1)
                            ->maxValue(200)
                            ->helperText(__('newsletter.send_rate_help')),
                    ]),
            ]);
    }
}
