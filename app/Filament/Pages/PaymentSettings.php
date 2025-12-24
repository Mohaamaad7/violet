<?php

namespace App\Filament\Pages;

use App\Models\PaymentSetting;
use App\Services\KashierService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'إعدادات الدفع';
    protected static ?string $title = 'إعدادات الدفع';
    protected static ?int $navigationSort = 101;
    protected string $view = 'filament.pages.payment-settings';

    public ?array $data = [];

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'النظام';
    }

    public function mount(): void
    {
        $this->form->fill([
            'kashier_mode' => PaymentSetting::get('kashier_mode', 'test'),
            'kashier_test_mid' => PaymentSetting::get('kashier_test_mid'),
            'kashier_test_secret_key' => PaymentSetting::get('kashier_test_secret_key'),
            'kashier_test_api_key' => PaymentSetting::get('kashier_test_api_key'),
            'kashier_live_mid' => PaymentSetting::get('kashier_live_mid'),
            'kashier_live_secret_key' => PaymentSetting::get('kashier_live_secret_key'),
            'kashier_live_api_key' => PaymentSetting::get('kashier_live_api_key'),
            'payment_cod_enabled' => (bool) PaymentSetting::get('payment_cod_enabled', true),
            'payment_card_enabled' => (bool) PaymentSetting::get('payment_card_enabled', false),
            'payment_vodafone_cash_enabled' => (bool) PaymentSetting::get('payment_vodafone_cash_enabled', false),
            'payment_orange_money_enabled' => (bool) PaymentSetting::get('payment_orange_money_enabled', false),
            'payment_etisalat_cash_enabled' => (bool) PaymentSetting::get('payment_etisalat_cash_enabled', false),
            'payment_meeza_enabled' => (bool) PaymentSetting::get('payment_meeza_enabled', false),
            'payment_valu_enabled' => (bool) PaymentSetting::get('payment_valu_enabled', false),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('إعدادات Kashier')
                    ->description('بيانات الاتصال ببوابة الدفع Kashier')
                    ->schema([
                        Select::make('kashier_mode')
                            ->label('بيئة العمل')
                            ->options([
                                'test' => '🧪 تجريبي (Sandbox)',
                                'live' => '🚀 إنتاجي (Live)',
                            ])
                            ->default('test')
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),

                        // Test Credentials - visible only in test mode
                        Section::make('بيانات التجريبي (Sandbox)')
                            ->schema([
                                TextInput::make('kashier_test_mid')
                                    ->label('Merchant ID')
                                    ->placeholder('MID-xxx-xxx')
                                    ->columnSpan(1),

                                TextInput::make('kashier_test_secret_key')
                                    ->label('Secret Key')
                                    ->password()
                                    ->revealable()
                                    ->columnSpan(1),

                                TextInput::make('kashier_test_api_key')
                                    ->label('API Key')
                                    ->password()
                                    ->revealable()
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->visible(fn($get) => $get('kashier_mode') === 'test'),

                        // Live Credentials - visible only in live mode
                        Section::make('بيانات الإنتاج (Live)')
                            ->schema([
                                TextInput::make('kashier_live_mid')
                                    ->label('Merchant ID')
                                    ->placeholder('MID-xxx-xxx')
                                    ->columnSpan(1),

                                TextInput::make('kashier_live_secret_key')
                                    ->label('Secret Key')
                                    ->password()
                                    ->revealable()
                                    ->columnSpan(1),

                                TextInput::make('kashier_live_api_key')
                                    ->label('API Key')
                                    ->password()
                                    ->revealable()
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->visible(fn($get) => $get('kashier_mode') === 'live'),
                    ])
                    ->columns(2),

                Section::make('طرق الدفع المتاحة')
                    ->description('اختر طرق الدفع التي تريد إتاحتها للعملاء')
                    ->schema([
                        Toggle::make('payment_cod_enabled')
                            ->label('💵 الدفع عند الاستلام')
                            ->default(true),

                        Toggle::make('payment_card_enabled')
                            ->label('💳 البطاقات البنكية'),

                        Toggle::make('payment_vodafone_cash_enabled')
                            ->label('📱 فودافون كاش'),

                        Toggle::make('payment_orange_money_enabled')
                            ->label('🍊 أورانج موني'),

                        Toggle::make('payment_etisalat_cash_enabled')
                            ->label('📞 اتصالات كاش'),

                        Toggle::make('payment_meeza_enabled')
                            ->label('🏦 ميزة'),

                        Toggle::make('payment_valu_enabled')
                            ->label('🛒 ڤاليو'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        PaymentSetting::set('kashier_mode', $data['kashier_mode'], 'kashier');
        PaymentSetting::set('kashier_test_mid', $data['kashier_test_mid'] ?? null, 'kashier');
        PaymentSetting::set('kashier_test_secret_key', $data['kashier_test_secret_key'] ?? null, 'kashier');
        PaymentSetting::set('kashier_test_api_key', $data['kashier_test_api_key'] ?? null, 'kashier');
        PaymentSetting::set('kashier_live_mid', $data['kashier_live_mid'] ?? null, 'kashier');
        PaymentSetting::set('kashier_live_secret_key', $data['kashier_live_secret_key'] ?? null, 'kashier');
        PaymentSetting::set('kashier_live_api_key', $data['kashier_live_api_key'] ?? null, 'kashier');

        PaymentSetting::set('payment_cod_enabled', $data['payment_cod_enabled'], 'methods');
        PaymentSetting::set('payment_card_enabled', $data['payment_card_enabled'], 'methods');
        PaymentSetting::set('payment_vodafone_cash_enabled', $data['payment_vodafone_cash_enabled'], 'methods');
        PaymentSetting::set('payment_orange_money_enabled', $data['payment_orange_money_enabled'], 'methods');
        PaymentSetting::set('payment_etisalat_cash_enabled', $data['payment_etisalat_cash_enabled'], 'methods');
        PaymentSetting::set('payment_meeza_enabled', $data['payment_meeza_enabled'], 'methods');
        PaymentSetting::set('payment_valu_enabled', $data['payment_valu_enabled'], 'methods');

        Notification::make()
            ->title('تم حفظ الإعدادات')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $this->save();

        $kashier = new KashierService();
        $result = $kashier->testConnection();

        if ($result['success']) {
            Notification::make()
                ->title('✅ الاتصال ناجح')
                ->body("الوضع: {$result['mode']} | MID: {$result['merchant_id']}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('❌ فشل الاتصال')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }
}
