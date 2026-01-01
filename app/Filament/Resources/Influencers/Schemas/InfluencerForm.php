<?php

namespace App\Filament\Resources\Influencers\Schemas;

use App\Models\Influencer;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InfluencerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(trans_db('admin.influencers.sections.basic_info'))
                ->schema([
                    // For Create: Select user
                    Select::make('user_id')
                        ->label(trans_db('admin.influencers.fields.user'))
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->visible(fn(?Influencer $record) => $record === null),

                    // For Edit/View: Show user name as placeholder
                    Placeholder::make('user_name')
                        ->label(trans_db('admin.influencers.fields.user'))
                        ->content(fn(?Influencer $record) => $record?->user?->name ?? '-')
                        ->visible(fn(?Influencer $record) => $record !== null),

                    Select::make('status')
                        ->label(trans_db('admin.influencers.fields.status'))
                        ->options([
                            'active' => trans_db('admin.influencers.status.active'),
                            'inactive' => trans_db('admin.influencers.status.inactive'),
                            'suspended' => trans_db('admin.influencers.status.suspended'),
                        ])
                        ->default('active')
                        ->required()
                        ->native(false),

                    TextInput::make('commission_rate')
                        ->label(trans_db('admin.influencers.fields.commission_rate'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(10)
                        ->suffix('%')
                        ->required(),
                ])
                ->columns(3),

            Section::make(trans_db('admin.influencers.sections.social_accounts'))
                ->schema([
                    TextInput::make('instagram_url')
                        ->label(trans_db('admin.influencers.fields.instagram'))
                        ->url()
                        ->suffixIcon('heroicon-o-link'),

                    TextInput::make('facebook_url')
                        ->label(trans_db('admin.influencers.fields.facebook'))
                        ->url()
                        ->suffixIcon('heroicon-o-link'),

                    TextInput::make('tiktok_url')
                        ->label(trans_db('admin.influencers.fields.tiktok'))
                        ->url()
                        ->suffixIcon('heroicon-o-link'),

                    TextInput::make('youtube_url')
                        ->label(trans_db('admin.influencers.fields.youtube'))
                        ->url()
                        ->suffixIcon('heroicon-o-link'),

                    TextInput::make('twitter_url')
                        ->label(trans_db('admin.influencers.fields.twitter'))
                        ->url()
                        ->suffixIcon('heroicon-o-link'),
                ])
                ->columns(3)
                ->collapsible(),

            Section::make(trans_db('admin.influencers.sections.statistics'))
                ->schema([
                    Placeholder::make('total_sales_display')
                        ->label(trans_db('admin.influencers.fields.total_sales'))
                        ->content(fn(?Influencer $record) => number_format($record?->total_sales ?? 0, 2) . ' ' . trans_db('admin.currency.egp_short')),

                    Placeholder::make('total_earned_display')
                        ->label(trans_db('admin.influencers.fields.total_earned'))
                        ->content(fn(?Influencer $record) => number_format($record?->total_commission_earned ?? 0, 2) . ' ' . trans_db('admin.currency.egp_short')),

                    Placeholder::make('total_paid_display')
                        ->label(trans_db('admin.influencers.fields.total_paid'))
                        ->content(fn(?Influencer $record) => number_format($record?->total_commission_paid ?? 0, 2) . ' ' . trans_db('admin.currency.egp_short')),

                    Placeholder::make('balance_display')
                        ->label(trans_db('admin.influencers.fields.balance'))
                        ->content(fn(?Influencer $record) => number_format($record?->balance ?? 0, 2) . ' ' . trans_db('admin.currency.egp_short')),

                    Placeholder::make('discount_codes_count')
                        ->label(trans_db('admin.influencers.sections.discount_codes'))
                        ->content(fn(?Influencer $record) => $record?->discountCodes()->count() ?? 0),

                    Placeholder::make('commissions_count')
                        ->label(trans_db('admin.influencers.sections.commission'))
                        ->content(fn(?Influencer $record) => $record?->commissions()->count() ?? 0),
                ])
                ->columns(3)
                ->collapsible()
                ->visible(fn(?Influencer $record) => $record !== null),
        ]);
    }
}

