<?php

namespace App\Filament\Pages;

use App\Enums\StorefrontTemplate;
use App\Filament\Pages\Concerns\ChecksPageAccess;
use App\Models\Setting;
use App\Services\StorefrontLayout;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StorefrontSettings extends Page implements HasForms
{
    use ChecksPageAccess;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 103;

    protected string $view = 'filament.pages.storefront-settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('storefront.settings.title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    public function mount(): void
    {
        $this->form->fill(['template' => app(StorefrontLayout::class)->template()->value]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('storefront.settings.title'))->schema([
                Select::make('template')
                    ->label(__('storefront.settings.template'))
                    ->options([
                        StorefrontTemplate::Classic->value => __('storefront.settings.classic'),
                        StorefrontTemplate::CategoryFirst->value => __('storefront.settings.category_first'),
                    ])
                    ->required()
                    ->in(array_column(StorefrontTemplate::cases(), 'value'))
                    ->helperText(__('storefront.settings.help')),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $data = $this->form->getState();
        Setting::set(StorefrontLayout::SETTING_KEY, $data['template'], 'string', 'general');

        Notification::make()->title(__('storefront.settings.saved'))->success()->send();
    }
}
