<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

/**
 * Shipping Discount Settings Page — Filament v4.
 *
 * Patterns from project's existing pages (SalesReport.php, RolePermissions.php):
 *   - $view is NON-STATIC (instance property)
 *   - $navigationIcon type: string|BackedEnum|null
 *   - getNavigationGroup() is a static method
 *   - public ?array $data = [] with ->statePath('data')
 *   - $this->form->fill([...]) in mount()
 *   - ->live() for reactive fields
 *
 * Settings read via Eloquent + Cache (no Setting::get()).
 */
class ShippingDiscountSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'خصم الشحن';

    protected static ?int $navigationSort = 101;

    // NON-STATIC: matches parent class Filament\Pages\Page::$view
    protected string $view = 'filament.pages.shipping-discount-settings';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    public function getTitle(): string
    {
        return 'إعدادات خصم الشحن الديناميكي';
    }

    /** Filament v4: all form data lives in this single array property */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'shipping_discount_enabled'    => (bool)  Setting::where('key', 'shipping_discount_enabled')->value('value'),
            'shipping_discount_threshold'  => (int)  (Setting::where('key', 'shipping_discount_threshold')->value('value') ?: 250),
            'shipping_discount_percentage' => (int)  (Setting::where('key', 'shipping_discount_percentage')->value('value') ?: 50),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات الخصم الديناميكي على الشحن')
                    ->description('يُطبَّق تلقائياً بدون كوبون عند تجاوز قيمة الطلب للحد الأدنى. نسبة 100% = شحن مجاني كامل.')
                    ->schema([
                        Toggle::make('shipping_discount_enabled')
                            ->label('تفعيل خصم الشحن التلقائي')
                            ->helperText('عند التفعيل، يُطبَّق الخصم تلقائياً على أي طلب يتجاوز الحد الأدنى')
                            ->live(),

                        TextInput::make('shipping_discount_threshold')
                            ->label('الحد الأدنى للتفعيل')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('ج.م')
                            ->helperText('الحد الذي يجب أن تتجاوزه قيمة الطلب')
                            ->visible(fn (callable $get) => (bool) $get('shipping_discount_enabled'))
                            ->required(fn (callable $get) => (bool) $get('shipping_discount_enabled')),

                        TextInput::make('shipping_discount_percentage')
                            ->label('نسبة الخصم على تكلفة الشحن')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->helperText('50 = خصم نصف الشحن | 100 = شحن مجاني كامل')
                            ->visible(fn (callable $get) => (bool) $get('shipping_discount_enabled'))
                            ->required(fn (callable $get) => (bool) $get('shipping_discount_enabled')),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $this->validate([
            'data.shipping_discount_threshold'  => 'required_if:data.shipping_discount_enabled,true|integer|min:1',
            'data.shipping_discount_percentage' => 'required_if:data.shipping_discount_enabled,true|integer|min:1|max:100',
        ]);

        Setting::updateOrCreate(
            ['key' => 'shipping_discount_enabled'],
            ['value' => $state['shipping_discount_enabled'] ? '1' : '0', 'type' => 'boolean', 'group' => 'shipping']
        );

        Setting::updateOrCreate(
            ['key' => 'shipping_discount_threshold'],
            ['value' => (string) ($state['shipping_discount_threshold'] ?? 250), 'type' => 'integer', 'group' => 'shipping']
        );

        Setting::updateOrCreate(
            ['key' => 'shipping_discount_percentage'],
            ['value' => (string) ($state['shipping_discount_percentage'] ?? 50), 'type' => 'integer', 'group' => 'shipping']
        );

        // Invalidate cache so all components pick up new settings immediately
        Cache::forget('shipping_discount_config');

        Notification::make()
            ->title('تم حفظ إعدادات خصم الشحن بنجاح')
            ->success()
            ->send();
    }
}
