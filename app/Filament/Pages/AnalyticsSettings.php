<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
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
                Section::make('إعدادات واجهة المتجر (Frontend Tracking)')
                    ->description('يستخدم لتتبع زوار المتجر في واجهة المستخدم.')
                    ->schema([
                        TextInput::make('ga_tracking_id')
                            ->label('Tracking ID (Measurement ID)')
                            ->placeholder('G-XXXXXXXXXX')
                            ->helperText('معرف التتبع الخاص بـ Google Analytics 4.')
                            ->columnSpanFull(),
                    ]),

                Section::make('إعدادات جلب البيانات (Backend Analytics API)')
                    ->description('يستخدم لجلب وعرض الإحصائيات داخل لوحة التحكم هذه.')
                    ->schema([
                        TextInput::make('ga_property_id')
                            ->label('Property ID')
                            ->numeric()
                            ->placeholder('123456789')
                            ->helperText('رقم الـ Property الخاص بحسابك في Google Analytics.')
                            ->columnSpanFull(),

                        FileUpload::make('ga_service_account_json')
                            ->label('Service Account Credentials (JSON)')
                            ->disk('local')
                            ->directory('private/analytics')
                            ->acceptedFileTypes(['application/json'])
                            ->helperText('يجب رفع ملف الـ JSON الخاص بـ Service Account لربط لوحة التحكم بـ API جوجل بشكل آمن.')
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
        Cache::tags(['analytics'])->flush(); // For tagged cache if supported
        
        // Also clear simple cache keys just in case
        Cache::forget('analytics_visitors');
        Cache::forget('analytics_top_referrers');
        Cache::forget('analytics_top_pages');
        Cache::forget('analytics_top_countries');

        Notification::make()
            ->title('✅ تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
