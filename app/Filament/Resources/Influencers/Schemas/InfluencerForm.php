<?php

namespace App\Filament\Resources\Influencers\Schemas;

use App\Models\Influencer;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class InfluencerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // ========================================
            // القسم 1: بيانات الدخول والهوية
            // ========================================
            Section::make(trans_db('admin.influencers.sections.credentials'))
                ->description(trans_db('admin.influencers.sections.credentials_desc'))
                ->icon('heroicon-o-user')
                ->schema([
                    TextInput::make('name')
                        ->label(trans_db('admin.influencers.fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->visible(fn(?Influencer $record) => $record === null),

                    TextInput::make('email')
                        ->label(trans_db('admin.influencers.fields.email'))
                        ->email()
                        ->required()
                        ->unique('users', 'email')
                        ->visible(fn(?Influencer $record) => $record === null),

                    TextInput::make('phone')
                        ->label(trans_db('admin.influencers.fields.phone'))
                        ->tel()
                        ->required()
                        ->visible(fn(?Influencer $record) => $record === null),

                    Toggle::make('send_invitation')
                        ->label(trans_db('admin.influencers.fields.send_invitation'))
                        ->helperText(trans_db('admin.influencers.fields.send_invitation_help'))
                        ->default(true)
                        ->visible(fn(?Influencer $record) => $record === null),

                    // عرض بيانات المستخدم عند التعديل
                    Placeholder::make('user_info')
                        ->label(trans_db('admin.influencers.fields.user'))
                        ->content(fn(?Influencer $record) => $record?->user?->name . ' (' . $record?->user?->email . ')')
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
                ])
                ->columns(2),

            // ========================================
            // القسم 2: ملف المؤثر (السوشيال ميديا)
            // ========================================
            Section::make(trans_db('admin.influencers.sections.profile'))
                ->description(trans_db('admin.influencers.sections.profile_desc'))
                ->icon('heroicon-o-share')
                ->schema([
                    Select::make('primary_platform')
                        ->label(trans_db('admin.influencers.fields.primary_platform'))
                        ->options([
                            'instagram' => 'Instagram',
                            'facebook' => 'Facebook',
                            'tiktok' => 'TikTok',
                            'youtube' => 'YouTube',
                            'twitter' => 'Twitter/X',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),

                    TextInput::make('handle')
                        ->label(trans_db('admin.influencers.fields.handle'))
                        ->prefix('@')
                        ->placeholder('username')
                        ->required(),

                    TextInput::make('instagram_url')
                        ->label(trans_db('admin.influencers.fields.instagram'))
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('https://instagram.com/username'),

                    TextInput::make('facebook_url')
                        ->label(trans_db('admin.influencers.fields.facebook'))
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('https://facebook.com/page'),

                    TextInput::make('tiktok_url')
                        ->label(trans_db('admin.influencers.fields.tiktok'))
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('https://tiktok.com/@username'),

                    TextInput::make('youtube_url')
                        ->label(trans_db('admin.influencers.fields.youtube'))
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('https://youtube.com/c/channel'),

                    TextInput::make('twitter_url')
                        ->label(trans_db('admin.influencers.fields.twitter'))
                        ->url()
                        ->prefixIcon('heroicon-o-link')
                        ->placeholder('https://twitter.com/username'),
                ])
                ->columns(3)
                ->collapsible(),

            // ========================================
            // القسم 3: الاتفاق المالي والكود
            // ========================================
            Section::make(trans_db('admin.influencers.sections.financial'))
                ->description(trans_db('admin.influencers.sections.financial_desc'))
                ->icon('heroicon-o-banknotes')
                ->schema([
                    // --- عمولة المؤثر ---
                    Radio::make('commission_type')
                        ->label(trans_db('admin.influencers.fields.commission_type'))
                        ->options([
                            'percentage' => trans_db('admin.influencers.commission_types.percentage'),
                            'fixed' => trans_db('admin.influencers.commission_types.fixed'),
                        ])
                        ->default('percentage')
                        ->inline()
                        ->required()
                        ->live(),

                    TextInput::make('commission_rate')
                        ->label(trans_db('admin.influencers.fields.commission_value'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn($get) => $get('commission_type') === 'percentage' ? 100 : 99999)
                        ->default(10)
                        ->suffix(fn($get) => $get('commission_type') === 'percentage' ? '%' : trans_db('admin.currency.egp_short'))
                        ->required(),

                    // --- كود الخصم (بدون زر Generate لتجنب الأخطاء) ---
                    TextInput::make('coupon_code')
                        ->label(trans_db('admin.influencers.fields.coupon_code'))
                        ->required()
                        ->maxLength(20)
                        ->unique('discount_codes', 'code')
                        ->alphaDash()
                        ->helperText(trans_db('admin.influencers.fields.coupon_code_help'))
                        ->visible(fn(?Influencer $record) => $record === null),

                    Radio::make('discount_type')
                        ->label(trans_db('admin.influencers.fields.discount_type'))
                        ->options([
                            'percentage' => trans_db('admin.influencers.discount_types.percentage'),
                            'fixed' => trans_db('admin.influencers.discount_types.fixed'),
                        ])
                        ->default('percentage')
                        ->inline()
                        ->required()
                        ->live()
                        ->visible(fn(?Influencer $record) => $record === null),

                    TextInput::make('discount_value')
                        ->label(trans_db('admin.influencers.fields.discount_value'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn($get) => $get('discount_type') === 'percentage' ? 100 : 99999)
                        ->default(15)
                        ->suffix(fn($get) => $get('discount_type') === 'percentage' ? '%' : trans_db('admin.currency.egp_short'))
                        ->required()
                        ->visible(fn(?Influencer $record) => $record === null),
                ])
                ->columns(2),

            // ========================================
            // القسم 4: الإحصائيات (للعرض فقط)
            // ========================================
            Section::make(trans_db('admin.influencers.sections.statistics'))
                ->icon('heroicon-o-chart-bar')
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
