<?php

namespace App\Filament\Resources\Newsletter\NewsletterSubscriptions;

use App\Filament\Resources\Newsletter\NewsletterSubscriptions\Pages\CreateNewsletterSubscription;
use App\Filament\Resources\Newsletter\NewsletterSubscriptions\Pages\EditNewsletterSubscription;
use App\Filament\Resources\Newsletter\NewsletterSubscriptions\Pages\ListNewsletterSubscriptions;
use App\Filament\Resources\Newsletter\NewsletterSubscriptions\Schemas\NewsletterSubscriptionForm;
use App\Filament\Resources\Newsletter\NewsletterSubscriptions\Tables\NewsletterSubscriptionsTable;
use App\Models\NewsletterSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NewsletterSubscriptionResource extends Resource
{
    protected static ?string $model = NewsletterSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'email';
    
    protected static UnitEnum|string|null $navigationGroup = null;
    
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('newsletter.newsletter_subscriptions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('newsletter.navigation_group_marketing');
    }

    public static function getModelLabel(): string
    {
        return __('newsletter.subscriber');
    }

    public static function getPluralModelLabel(): string
    {
        return __('newsletter.subscribers');
    }

    public static function form(Schema $schema): Schema
    {
        return NewsletterSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsletterSubscriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterSubscriptions::route('/'),
            'create' => CreateNewsletterSubscription::route('/create'),
            'edit' => EditNewsletterSubscription::route('/{record}/edit'),
        ];
    }
}
