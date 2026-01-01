<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\ChecksPageAccess;
use App\Models\PaymentSetting;
use App\Services\PaymentGatewayManager;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Payment Settings Page
 * 
 * صفحة إعدادات الدفع - تدعم بوابات متعددة (Kashier, Paymob)
 * يتم عرض إعدادات البوابة المختارة فقط
 */
class PaymentSettings extends Page implements HasForms
{
    use InteractsWithForms;
    use ChecksPageAccess;

    protected static ?int $navigationSort = 101;
    protected string $view = 'filament.pages.payment-settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('admin.pages.payment_settings.title');
    }

    public function getTitle(): string
    {
        return __('admin.pages.payment_settings.title');
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    public function mount(): void
    {
        $this->form->fill([
            // Active Gateway
            'active_gateway' => PaymentSetting::get('active_gateway', 'kashier'),

            // Kashier Settings
            'kashier_mode' => PaymentSetting::get('kashier_mode', 'test'),
            'kashier_test_mid' => PaymentSetting::get('kashier_test_mid'),
            'kashier_test_secret_key' => PaymentSetting::get('kashier_test_secret_key'),
            'kashier_test_api_key' => PaymentSetting::get('kashier_test_api_key'),
            'kashier_live_mid' => PaymentSetting::get('kashier_live_mid'),
            'kashier_live_secret_key' => PaymentSetting::get('kashier_live_secret_key'),
            'kashier_live_api_key' => PaymentSetting::get('kashier_live_api_key'),

            // Paymob Settings
            'paymob_api_key' => PaymentSetting::get('paymob_api_key'),
            'paymob_secret_key' => PaymentSetting::get('paymob_secret_key'),
            'paymob_public_key' => PaymentSetting::get('paymob_public_key'),
            'paymob_hmac_secret' => PaymentSetting::get('paymob_hmac_secret'),
            'paymob_integration_id_card' => PaymentSetting::get('paymob_integration_id_card'),
            'paymob_integration_id_wallet' => PaymentSetting::get('paymob_integration_id_wallet'),
            'paymob_integration_id_kiosk' => PaymentSetting::get('paymob_integration_id_kiosk'),

            // Payment Methods
            'payment_cod_enabled' => (bool) PaymentSetting::get('payment_cod_enabled', true),
            'payment_card_enabled' => (bool) PaymentSetting::get('payment_card_enabled', false),
            'payment_meeza_enabled' => (bool) PaymentSetting::get('payment_meeza_enabled', false),
            'payment_valu_enabled' => (bool) PaymentSetting::get('payment_valu_enabled', false),
            'payment_wallet_enabled' => (bool) PaymentSetting::get('payment_wallet_enabled', false),
            'payment_kiosk_enabled' => (bool) PaymentSetting::get('payment_kiosk_enabled', false),
            'payment_instapay_enabled' => (bool) PaymentSetting::get('payment_instapay_enabled', false),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ==================== Active Gateway Selection ====================
                Section::make('البوابة النشطة')
                    ->description('اختر بوابة الدفع التي سيتم استخدامها لجميع عمليات الدفع. سيتم عرض إعدادات البوابة المختارة فقط.')
                    ->schema([
                        Select::make('active_gateway')
                            ->label('بوابة الدفع المفعّلة')
                            ->options([
                                'kashier' => '🔷 Kashier',
                                'paymob' => '🔶 Paymob (Accept)',
                            ])
                            ->default('kashier')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $gatewayName = $state === 'paymob' ? 'Paymob' : 'Kashier';
                                Notification::make()
                                    ->title('تم اختيار ' . $gatewayName)
                                    ->body('احفظ الإعدادات لتفعيل البوابة')
                                    ->info()
                                    ->send();
                            })
                            ->columnSpanFull(),
                    ]),

                // ==================== Kashier Settings (visible only when selected) ====================
                Section::make('🔷 إعدادات Kashier')
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

                        // Test Credentials
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

                        // Live Credentials
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
                    ->columns(2)
                    ->visible(fn($get) => $get('active_gateway') === 'kashier'),

                // ==================== Paymob Settings (visible only when selected) ====================
                Section::make('🔶 إعدادات Paymob')
                    ->description('بيانات الاتصال ببوابة الدفع Paymob (Accept)')
                    ->schema([
                        Section::make('مفاتيح API')
                            ->description('احصل على هذه المفاتيح من لوحة تحكم Paymob → Settings → Account Info')
                            ->schema([
                                TextInput::make('paymob_api_key')
                                    ->label('API Key')
                                    ->password()
                                    ->revealable()
                                    ->helperText('مفتاح API للمصادقة (Legacy APIs)')
                                    ->columnSpan(2),

                                TextInput::make('paymob_secret_key')
                                    ->label('Secret Key')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('sk_...')
                                    ->helperText('المفتاح السري للـ Intention API')
                                    ->columnSpan(1),

                                TextInput::make('paymob_public_key')
                                    ->label('Public Key')
                                    ->placeholder('pk_...')
                                    ->helperText('المفتاح العام للـ Checkout')
                                    ->columnSpan(1),

                                TextInput::make('paymob_hmac_secret')
                                    ->label('HMAC Secret')
                                    ->password()
                                    ->revealable()
                                    ->helperText('للتحقق من صحة الـ Callbacks')
                                    ->columnSpan(2),
                            ])
                            ->columns(2),

                        Section::make('Integration IDs')
                            ->description('معرّفات طرق الدفع من Payment Integrations في لوحة تحكم Paymob')
                            ->schema([
                                TextInput::make('paymob_integration_id_card')
                                    ->label('Card Integration ID')
                                    ->numeric()
                                    ->placeholder('123456')
                                    ->helperText('للبطاقات (Visa/MC/Meeza)')
                                    ->columnSpan(1),

                                TextInput::make('paymob_integration_id_wallet')
                                    ->label('Wallet Integration ID')
                                    ->numeric()
                                    ->placeholder('123456')
                                    ->helperText('للمحافظ الإلكترونية')
                                    ->columnSpan(1),

                                TextInput::make('paymob_integration_id_kiosk')
                                    ->label('Kiosk Integration ID')
                                    ->numeric()
                                    ->placeholder('123456')
                                    ->helperText('لفوري/أمان')
                                    ->columnSpan(1),
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn($get) => $get('active_gateway') === 'paymob'),

                // ==================== Payment Methods ====================
                Section::make('طرق الدفع المتاحة')
                    ->description('اختر طرق الدفع التي تريد إتاحتها للعملاء')
                    ->schema([
                        Toggle::make('payment_cod_enabled')
                            ->label('💵 الدفع عند الاستلام (COD)')
                            ->default(true),

                        Toggle::make('payment_card_enabled')
                            ->label('💳 البطاقات البنكية (Visa/MC)'),

                        Toggle::make('payment_meeza_enabled')
                            ->label('🏦 ميزة'),

                        Toggle::make('payment_valu_enabled')
                            ->label('🛒 ڤاليو (تقسيط)'),

                        Toggle::make('payment_wallet_enabled')
                            ->label('📱 المحفظة الإلكترونية')
                            ->helperText('فودافون كاش، أورانج موني، اتصالات كاش - كلها عبر Paymob'),

                        Toggle::make('payment_kiosk_enabled')
                            ->label('🏪 فوري / أمان'),

                        Toggle::make('payment_instapay_enabled')
                            ->label('🏛️ InstaPay'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Active Gateway
        PaymentSetting::set('active_gateway', $data['active_gateway'] ?? 'kashier', 'general');

        // Kashier Settings (only save if present in form data)
        if (isset($data['kashier_mode'])) {
            PaymentSetting::set('kashier_mode', $data['kashier_mode'], 'kashier');
        }
        if (array_key_exists('kashier_test_mid', $data)) {
            PaymentSetting::set('kashier_test_mid', $data['kashier_test_mid'], 'kashier');
        }
        if (array_key_exists('kashier_test_secret_key', $data)) {
            PaymentSetting::set('kashier_test_secret_key', $data['kashier_test_secret_key'], 'kashier');
        }
        if (array_key_exists('kashier_test_api_key', $data)) {
            PaymentSetting::set('kashier_test_api_key', $data['kashier_test_api_key'], 'kashier');
        }
        if (array_key_exists('kashier_live_mid', $data)) {
            PaymentSetting::set('kashier_live_mid', $data['kashier_live_mid'], 'kashier');
        }
        if (array_key_exists('kashier_live_secret_key', $data)) {
            PaymentSetting::set('kashier_live_secret_key', $data['kashier_live_secret_key'], 'kashier');
        }
        if (array_key_exists('kashier_live_api_key', $data)) {
            PaymentSetting::set('kashier_live_api_key', $data['kashier_live_api_key'], 'kashier');
        }

        // Paymob Settings (only save if present in form data)
        if (array_key_exists('paymob_api_key', $data)) {
            PaymentSetting::set('paymob_api_key', $data['paymob_api_key'], 'paymob');
        }
        if (array_key_exists('paymob_secret_key', $data)) {
            PaymentSetting::set('paymob_secret_key', $data['paymob_secret_key'], 'paymob');
        }
        if (array_key_exists('paymob_public_key', $data)) {
            PaymentSetting::set('paymob_public_key', $data['paymob_public_key'], 'paymob');
        }
        if (array_key_exists('paymob_hmac_secret', $data)) {
            PaymentSetting::set('paymob_hmac_secret', $data['paymob_hmac_secret'], 'paymob');
        }
        if (array_key_exists('paymob_integration_id_card', $data)) {
            PaymentSetting::set('paymob_integration_id_card', $data['paymob_integration_id_card'], 'paymob');
        }
        if (array_key_exists('paymob_integration_id_wallet', $data)) {
            PaymentSetting::set('paymob_integration_id_wallet', $data['paymob_integration_id_wallet'], 'paymob');
        }
        if (array_key_exists('paymob_integration_id_kiosk', $data)) {
            PaymentSetting::set('paymob_integration_id_kiosk', $data['paymob_integration_id_kiosk'], 'paymob');
        }

        // Payment Methods (always present)
        PaymentSetting::set('payment_cod_enabled', $data['payment_cod_enabled'] ?? false, 'methods');
        PaymentSetting::set('payment_card_enabled', $data['payment_card_enabled'] ?? false, 'methods');
        PaymentSetting::set('payment_meeza_enabled', $data['payment_meeza_enabled'] ?? false, 'methods');
        PaymentSetting::set('payment_valu_enabled', $data['payment_valu_enabled'] ?? false, 'methods');
        PaymentSetting::set('payment_wallet_enabled', $data['payment_wallet_enabled'] ?? false, 'methods');
        PaymentSetting::set('payment_kiosk_enabled', $data['payment_kiosk_enabled'] ?? false, 'methods');
        PaymentSetting::set('payment_instapay_enabled', $data['payment_instapay_enabled'] ?? false, 'methods');

        Notification::make()
            ->title('✅ تم حفظ الإعدادات')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $this->save();

        $activeGateway = $this->data['active_gateway'] ?? 'kashier';

        try {
            $gatewayManager = app(PaymentGatewayManager::class);
            $gateway = $gatewayManager->getGateway($activeGateway);
            $result = $gateway->testConnection();

            if ($result['success']) {
                $gatewayName = $activeGateway === 'paymob' ? 'Paymob' : 'Kashier';
                $details = '';

                if ($activeGateway === 'kashier') {
                    $details = "الوضع: {$result['mode']} | MID: {$result['merchant_id']}";
                } else {
                    $integrations = [];
                    if ($result['has_card_integration'] ?? false)
                        $integrations[] = '✅ Card';
                    if ($result['has_wallet_integration'] ?? false)
                        $integrations[] = '✅ Wallet';
                    if ($result['has_kiosk_integration'] ?? false)
                        $integrations[] = '✅ Kiosk';
                    $details = 'Integrations: ' . (implode(', ', $integrations) ?: 'لم يتم تعيين Integration IDs');
                }

                Notification::make()
                    ->title("✅ اتصال {$gatewayName} ناجح")
                    ->body($details)
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('❌ فشل الاتصال')
                    ->body($result['message'])
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ خطأ')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
