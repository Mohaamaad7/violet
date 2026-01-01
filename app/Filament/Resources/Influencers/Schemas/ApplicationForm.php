<?php

namespace App\Filament\Resources\Influencers\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(trans_db('admin.applications.sections.applicant_info'))
                ->schema([
                    TextInput::make('full_name')
                        ->label(trans_db('admin.applications.fields.full_name'))
                        ->required()
                        ->maxLength(255)
                        ->disabled(),

                    TextInput::make('email')
                        ->label(trans_db('admin.applications.fields.email'))
                        ->email()
                        ->required()
                        ->disabled(),

                    TextInput::make('phone')
                        ->label(trans_db('admin.applications.fields.phone'))
                        ->tel()
                        ->disabled(),

                    Textarea::make('portfolio')
                        ->label(trans_db('admin.applications.fields.portfolio'))
                        ->rows(3)
                        ->disabled()
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Section::make(trans_db('admin.applications.sections.social_accounts'))
                ->schema([
                    TextInput::make('instagram_url')
                        ->label(trans_db('admin.influencers.fields.instagram'))
                        ->url()
                        ->disabled()
                        ->suffixIcon('heroicon-o-link'),

                    TextInput::make('facebook_url')
                        ->label(trans_db('admin.influencers.fields.facebook'))
                        ->url()
                        ->disabled()
                        ->suffixIcon('heroicon-o-link'),

                    TextInput::make('tiktok_url')
                        ->label(trans_db('admin.influencers.fields.tiktok'))
                        ->url()
                        ->disabled()
                        ->suffixIcon('heroicon-o-link'),

                    TextInput::make('youtube_url')
                        ->label(trans_db('admin.influencers.fields.youtube'))
                        ->url()
                        ->disabled()
                        ->suffixIcon('heroicon-o-link'),

                    TextInput::make('twitter_url')
                        ->label(trans_db('admin.influencers.fields.twitter'))
                        ->url()
                        ->disabled()
                        ->suffixIcon('heroicon-o-link'),
                ])
                ->columns(3)
                ->collapsible(),

            Section::make(trans_db('admin.applications.sections.followers_info'))
                ->schema([
                    TextInput::make('instagram_followers')
                        ->label(trans_db('admin.influencers.fields.instagram') . ' ' . trans_db('admin.influencers.fields.followers'))
                        ->numeric()
                        ->disabled(),

                    TextInput::make('facebook_followers')
                        ->label(trans_db('admin.influencers.fields.facebook') . ' ' . trans_db('admin.influencers.fields.followers'))
                        ->numeric()
                        ->disabled(),

                    TextInput::make('tiktok_followers')
                        ->label(trans_db('admin.influencers.fields.tiktok') . ' ' . trans_db('admin.influencers.fields.followers'))
                        ->numeric()
                        ->disabled(),

                    TextInput::make('youtube_followers')
                        ->label(trans_db('admin.influencers.fields.youtube') . ' ' . trans_db('admin.influencers.fields.followers'))
                        ->numeric()
                        ->disabled(),

                    TextInput::make('twitter_followers')
                        ->label(trans_db('admin.influencers.fields.twitter') . ' ' . trans_db('admin.influencers.fields.followers'))
                        ->numeric()
                        ->disabled(),

                    Placeholder::make('total_followers')
                        ->label(trans_db('admin.applications.fields.total_followers'))
                        ->content(fn($record) => number_format(
                            ($record?->instagram_followers ?? 0) +
                            ($record?->facebook_followers ?? 0) +
                            ($record?->tiktok_followers ?? 0) +
                            ($record?->youtube_followers ?? 0) +
                            ($record?->twitter_followers ?? 0)
                        )),
                ])
                ->columns(3)
                ->collapsible(),

            Section::make(trans_db('admin.applications.sections.review'))
                ->schema([
                    Select::make('status')
                        ->label(trans_db('admin.applications.fields.status'))
                        ->options([
                            'pending' => trans_db('admin.applications.status.pending'),
                            'approved' => trans_db('admin.applications.status.approved'),
                            'rejected' => trans_db('admin.applications.status.rejected'),
                        ])
                        ->disabled()
                        ->native(false),

                    Placeholder::make('reviewed_by_name')
                        ->label(trans_db('admin.applications.fields.reviewed_by'))
                        ->content(fn($record) => $record?->reviewer?->name ?? '-')
                        ->visible(fn($record) => $record?->reviewed_by !== null),

                    Placeholder::make('reviewed_at_display')
                        ->label(trans_db('admin.applications.fields.reviewed_at'))
                        ->content(fn($record) => $record?->reviewed_at?->format('Y-m-d H:i') ?? '-')
                        ->visible(fn($record) => $record?->reviewed_at !== null),

                    Textarea::make('rejection_reason')
                        ->label(trans_db('admin.applications.fields.rejection_reason'))
                        ->rows(2)
                        ->disabled()
                        ->visible(fn($record) => $record?->status === 'rejected')
                        ->columnSpanFull(),
                ])
                ->columns(3)
                ->visible(fn($record) => $record !== null),
        ]);
    }
}
