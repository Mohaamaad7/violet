<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

class AnalyticsSettings extends Page implements HasForms
{
    use InteractsWithForms;
    use ChecksPageAccess;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.analytics-settings';

    protected static ?int $navigationSort = 102;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('admin.pages.analytics_settings.title') ?? 'إعدادات الإحصائيات';
    }

    public function getTitle(): string
    {
        return __('admin.pages.analytics_settings.title') ?? 'إعدادات الإحصائيات (Google Analytics)';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system') ?? 'النظام';
    }

    public function mount(): void
    {
        $this->form->fill([
            'ga_tracking_id' => Setting::get('ga_tracking_id'),
            'ga_property_id' => Setting::get('ga_property_id'),
            'ga_service_account_json' => Setting::get('ga_service_account_json'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.pages.analytics_settings.frontend_tracking'))
                    ->description(__('admin.pages.analytics_settings.frontend_tracking_desc'))
                    ->schema([
                        TextInput::make('ga_tracking_id')
                            ->label(__('admin.pages.analytics_settings.tracking_id'))
                            ->placeholder('G-XXXXXXXXXX')
                            ->helperText(__('admin.pages.analytics_settings.tracking_id_help'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.pages.analytics_settings.backend_api'))
                    ->description(__('admin.pages.analytics_settings.backend_api_desc'))
                    ->schema([
                        TextInput::make('ga_property_id')
                            ->label(__('admin.pages.analytics_settings.property_id'))
                            ->numeric()
                            ->placeholder('123456789')
                            ->helperText(__('admin.pages.analytics_settings.property_id_help'))
                            ->columnSpanFull(),

                        FileUpload::make('ga_service_account_json')
                            ->label(__('admin.pages.analytics_settings.service_account_json'))
                            ->disk('local')
                            ->directory('private/analytics')
                            ->acceptedFileTypes(['application/json'])
                            ->helperText(__('admin.pages.analytics_settings.service_account_json_help'))
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('ga_tracking_id', $data['ga_tracking_id'] ?? null, 'string', 'analytics');
        Setting::set('ga_property_id', $data['ga_property_id'] ?? null, 'string', 'analytics');
        Setting::set('ga_service_account_json', $data['ga_service_account_json'] ?? null, 'string', 'analytics');

        // مسح الكاش للإحصائيات حتى يقرأ الإعدادات الجديدة فوراً
        Cache::forget('analytics_settings');
        if (Cache::supportsTags()) {
            Cache::tags(['analytics'])->flush(); // For tagged cache if supported
        }
        
        // Also clear simple cache keys just in case
        Cache::forget('analytics_visitors');
        Cache::forget('analytics_top_referrers');
        Cache::forget('analytics_top_pages');
        Cache::forget('analytics_top_countries');

        Notification::make()
            ->title(__('admin.pages.analytics_settings.saved') ?? 'تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
