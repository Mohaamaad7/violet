# 💡 Examples & Use Cases

## نظرة عامة

هذا الملف يحتوي على أمثلة عملية وسيناريوهات حقيقية لكيفية استخدام نظام Dashboard Customization.

---

## 🎯 Use Case 1: موظف مبيعات جديد

### السيناريو
شركتك وظفت **أحمد** كموظف مبيعات. عايز تديله صلاحيات محددة.

### الخطوات

#### 1. إنشاء المستخدم وتعيين الدور
```php
$user = User::create([
    'name' => 'أحمد محمد',
    'email' => 'ahmed@violet.com',
    'password' => Hash::make('password'),
]);

$user->assignRole('Sales');
```

#### 2. الدور "Sales" معرف مسبقاً (من الـ Seeder)
```php
// في DefaultRoleConfigurationsSeeder
$salesRole = Role::where('name', 'Sales')->first();

// Widgets للمبيعات فقط
$widgets = [
    'App\Filament\Widgets\StatsOverviewWidget',
    'App\Filament\Widgets\SalesChartWidget',
    'App\Filament\Widgets\RecentOrdersWidget',
];

foreach ($widgets as $index => $widgetClass) {
    $widgetConfig = WidgetConfiguration::where('widget_class', $widgetClass)->first();
    
    RoleWidgetDefault::create([
        'role_id' => $salesRole->id,
        'widget_configuration_id' => $widgetConfig->id,
        'is_visible' => true,
        'order_position' => $index + 1,
        'column_span' => ($index === 0) ? 4 : 2,
    ]);
}

// Resources المتاحة
$resources = [
    'App\Filament\Resources\Orders\OrderResource' => [
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => false,
    ],
    'App\Filament\Resources\Customers\CustomerResource' => [
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => false,
    ],
    'App\Filament\Resources\Coupons\CouponResource' => [
        'can_view' => true,
        'can_create' => false,
        'can_edit' => false,
        'can_delete' => false,
    ],
];

foreach ($resources as $resourceClass => $permissions) {
    $resourceConfig = ResourceConfiguration::where('resource_class', $resourceClass)->first();
    
    RoleResourceAccess::create([
        'role_id' => $salesRole->id,
        'resource_configuration_id' => $resourceConfig->id,
        ...$permissions,
        'is_visible_in_navigation' => true,
    ]);
}
```

#### 3. أحمد يدخل لأول مرة - يشوف إيه؟

**Dashboard:**
```
┌────────────────────────────────────────────────────────────┐
│  📊 إحصائيات عامة                                          │
│  الإيرادات اليوم: 5,200 ج.م | الطلبات الجديدة: 12         │
└────────────────────────────────────────────────────────────┘

┌──────────────────────────────┐  ┌──────────────────────────┐
│  📈 رسم بياني للمبيعات       │  │  📋 آخر الطلبات          │
│                              │  │  #ORD-12345: 250 ج.م     │
│  [Chart showing sales]       │  │  #ORD-12346: 180 ج.م     │
│                              │  │  #ORD-12347: 420 ج.م     │
└──────────────────────────────┘  └──────────────────────────┘
```

**Sidebar Navigation:**
```
📦 المبيعات
  ├── الطلبات ✅
  ├── المدفوعات ❌ (مش ظاهر)
  ├── أكواد الخصم (Read-only)
  └── المرتجعات ❌ (مش ظاهر)

👥 العملاء
  └── العملاء ✅
```

---

## 🎯 Use Case 2: موظف مبيعات محترف - تخصيص شخصي

### السيناريو
**سارة** موظفة مبيعات محترفة، عايزة تخصص الـ Dashboard بتاعها.

### الخطوات

#### 1. سارة تدخل على Dashboard
تشوف الـ widgets الافتراضية للدور "Sales"

#### 2. سارة تضغط على "تخصيص Dashboard" (من UI)
```php
// في الـ Frontend (Livewire Component)
class CustomizeDashboard extends Component
{
    public $widgets = [];
    
    public function mount()
    {
        $service = app(DashboardConfigurationService::class);
        $this->widgets = $service->getAvailableWidgetsForUser(auth()->user());
    }
    
    public function toggleWidget($widgetId)
    {
        UserWidgetPreference::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'widget_configuration_id' => $widgetId,
            ],
            [
                'is_visible' => !$this->widgets[$widgetId]['is_visible'],
            ]
        );
        
        // Invalidate cache
        app(DashboardConfigurationService::class)
            ->invalidateUserWidgetCache(auth()->user());
        
        $this->mount(); // Refresh
    }
}
```

#### 3. سارة تخفي "رسم بياني للمبيعات" وتضيف "المرتجعات المعلقة"
```php
// Hide Sales Chart
UserWidgetPreference::create([
    'user_id' => $sara->id,
    'widget_configuration_id' => 3, // SalesChartWidget
    'is_visible' => false,
]);

// Show Pending Returns
UserWidgetPreference::create([
    'user_id' => $sara->id,
    'widget_configuration_id' => 6, // PendingReturnsWidget
    'is_visible' => true,
    'order_position' => 3,
    'column_span' => 2,
]);
```

#### 4. سارة دلوقتي تشوف:
```
┌────────────────────────────────────────────────────────────┐
│  📊 إحصائيات عامة                                          │
└────────────────────────────────────────────────────────────┘

┌──────────────────────────────┐  ┌──────────────────────────┐
│  📋 آخر الطلبات              │  │  🔄 المرتجعات المعلقة    │
│  [Recent orders list]        │  │  [Pending returns]       │
└──────────────────────────────┘  └──────────────────────────┘
```

---

## 🎯 Use Case 3: إضافة Widget جديد - Auto Discovery

### السيناريو
المطور أنشأ widget جديد: `TopProductsWidget.php`

### الكود

#### 1. المطور ينشئ الـ Widget
```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TopProductsWidget extends ChartWidget
{
    protected static ?string $heading = 'أكثر المنتجات مبيعاً';
    
    protected function getData(): array
    {
        // Implementation
    }
}
```

#### 2. المطور يشغل الـ Discovery Command
```bash
php artisan dashboard:discover
```

#### 3. الـ Command يكتشف الـ Widget تلقائياً
```php
// في DashboardDiscoveryCommand
public function handle()
{
    $widgetFiles = File::files(app_path('Filament/Widgets'));
    
    foreach ($widgetFiles as $file) {
        $class = 'App\\Filament\\Widgets\\' . $file->getFilenameWithoutExtension();
        
        if (class_exists($class)) {
            WidgetConfiguration::updateOrCreate(
                ['widget_class' => $class],
                [
                    'widget_name' => $this->extractWidgetName($class),
                    'widget_group' => $this->guessWidgetGroup($class),
                    'is_active' => true,
                    'default_order' => 999,
                    'default_column_span' => 2,
                ]
            );
            
            $this->info("Registered: {$class}");
        }
    }
}
```

#### 4. الـ Admin يدخل على لوحة التحكم
يروح على **Widget Configurations** → يشوف `TopProductsWidget` موجود تلقائياً

#### 5. الـ Admin يضيفه لدور "Sales"
```php
// من الـ UI
RoleWidgetDefault::create([
    'role_id' => 3, // Sales
    'widget_configuration_id' => 9, // TopProductsWidget
    'is_visible' => true,
    'order_position' => 4,
    'column_span' => 2,
]);
```

#### 6. كل موظفين المبيعات دلوقتي يشوفوا الـ Widget الجديد! ✨

---

## 🎯 Use Case 4: منع موظف معين من شيء محدد

### السيناريو
**خالد** موظف مبيعات، بس مش عايزينه يشوف أكواد الخصم (عشان سبب معين).

### الحل

#### Option 1: User-Specific Resource Override (Future Feature)
```php
// NOT IMPLEMENTED YET - but this is the concept
UserResourceOverride::create([
    'user_id' => $khaled->id,
    'resource_configuration_id' => 4, // CouponResource
    'is_visible_in_navigation' => false,
]);
```

#### Option 2: إنشاء دور مخصص
```php
// إنشاء دور جديد: "Sales - Limited"
$limitedSalesRole = Role::create(['name' => 'Sales - Limited']);

// نسخ كل configurations من "Sales" ماعدا Coupons
$salesRole = Role::where('name', 'Sales')->first();

// Copy widget defaults
foreach ($salesRole->widgetDefaults as $default) {
    RoleWidgetDefault::create([
        'role_id' => $limitedSalesRole->id,
        'widget_configuration_id' => $default->widget_configuration_id,
        'is_visible' => $default->is_visible,
        'order_position' => $default->order_position,
        'column_span' => $default->column_span,
    ]);
}

// Copy resource access (excluding Coupons)
foreach ($salesRole->resourceAccess as $access) {
    if ($access->resourceConfiguration->resource_class !== 'App\Filament\Resources\Coupons\CouponResource') {
        RoleResourceAccess::create([
            'role_id' => $limitedSalesRole->id,
            'resource_configuration_id' => $access->resource_configuration_id,
            'can_view' => $access->can_view,
            'can_create' => $access->can_create,
            'can_edit' => $access->can_edit,
            'can_delete' => $access->can_delete,
            'is_visible_in_navigation' => $access->is_visible_in_navigation,
        ]);
    }
}

// تعيين خالد للدور الجديد
$khaled->syncRoles(['Sales - Limited']);
```

---

## 🎯 Use Case 5: Dashboard للمدير العام

### السيناريو
**المدير العام** عايز يشوف **كل حاجة** - overview كامل.

### التنفيذ

```php
$managerRole = Role::where('name', 'Manager')->first();

// All widgets visible
$allWidgets = WidgetConfiguration::where('is_active', true)->get();

foreach ($allWidgets as $index => $widget) {
    RoleWidgetDefault::create([
        'role_id' => $managerRole->id,
        'widget_configuration_id' => $widget->id,
        'is_visible' => true,
        'order_position' => $index + 1,
        'column_span' => $widget->default_column_span,
    ]);
}

// All navigation groups visible
$allGroups = NavigationGroupConfiguration::where('is_active', true)->get();

foreach ($allGroups as $index => $group) {
    RoleNavigationGroup::create([
        'role_id' => $managerRole->id,
        'navigation_group_configuration_id' => $group->id,
        'is_visible' => true,
        'order_position' => $index + 1,
    ]);
}

// All resources with full access
$allResources = ResourceConfiguration::where('is_active', true)->get();

foreach ($allResources as $resource) {
    RoleResourceAccess::create([
        'role_id' => $managerRole->id,
        'resource_configuration_id' => $resource->id,
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => true,
        'is_visible_in_navigation' => true,
    ]);
}
```

### النتيجة
```
Manager Dashboard = Super Dashboard!

┌────────────────┐ ┌────────────────┐ ┌────────────────┐ ┌────────────────┐
│ إحصائيات عامة  │ │ مبيعات         │ │ مخزون          │ │ عملاء          │
└────────────────┘ └────────────────┘ └────────────────┘ └────────────────┘

┌────────────────┐ ┌────────────────┐ ┌────────────────┐ ┌────────────────┐
│ رسم بياني      │ │ طلبات حديثة    │ │ تنبيهات مخزون │ │ مرتجعات معلقة │
└────────────────┘ └────────────────┘ └────────────────┘ └────────────────┘

Navigation: [الكتالوج] [المبيعات] [المخزون] [العملاء] [المحتوى] [الإعدادات] [النظام]
```

---

## 🎯 Use Case 6: تقرير مخصص لدور معين

### السيناريو
عايز تضيف **تقرير مالي** بس للـ "Finance" role.

### الخطوات

#### 1. إنشاء Widget التقرير
```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialReportWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي الإيرادات', 'EGP 125,340')
                ->description('هذا الشهر')
                ->color('success'),
            
            Stat::make('إجمالي المصروفات', 'EGP 45,890')
                ->description('هذا الشهر')
                ->color('danger'),
            
            Stat::make('صافي الربح', 'EGP 79,450')
                ->description('+15% من الشهر السابق')
                ->color('primary'),
        ];
    }
}
```

#### 2. تشغيل Discovery
```bash
php artisan dashboard:discover
```

#### 3. ربط الـ Widget بدور Finance فقط
```php
$financeRole = Role::where('name', 'Finance')->first();
$widgetConfig = WidgetConfiguration::where('widget_class', 
    'App\Filament\Widgets\FinancialReportWidget'
)->first();

RoleWidgetDefault::create([
    'role_id' => $financeRole->id,
    'widget_configuration_id' => $widgetConfig->id,
    'is_visible' => true,
    'order_position' => 1,
    'column_span' => 4,
]);
```

#### 4. موظفين Finance بس يشوفوا الـ Widget ده! 🎉

---

## 🎯 Use Case 7: إخفاء Navigation Group مؤقتاً

### السيناريو
عايز تخفي "المحتوى" Group من كل الأدوار مؤقتاً (صيانة).

### الحل السريع
```php
$contentGroup = NavigationGroupConfiguration::where('group_key', 'admin.nav.content')->first();

// Disable globally
$contentGroup->update(['is_active' => false]);

// All roles will immediately stop seeing it!
```

### إعادة التفعيل
```php
$contentGroup->update(['is_active' => true]);
```

---

## 📊 Performance Examples

### Caching Strategy

```php
// في DashboardConfigurationService
public function getWidgetsForUser(User $user): array
{
    // Cache for 1 hour
    return Cache::remember("user.{$user->id}.widgets", 3600, function () use ($user) {
        return $this->buildUserWidgets($user);
    });
}

// Invalidate on change
public function updateUserWidgetPreference(User $user, $widgetId, $data)
{
    UserWidgetPreference::updateOrCreate([
        'user_id' => $user->id,
        'widget_configuration_id' => $widgetId,
    ], $data);
    
    // Clear cache immediately
    Cache::forget("user.{$user->id}.widgets");
}
```

### Eager Loading

```php
// Bad ❌
$widgets = $user->widgetPreferences;
foreach ($widgets as $widget) {
    echo $widget->widgetConfiguration->widget_name; // N+1 Query!
}

// Good ✅
$widgets = $user->widgetPreferences()
    ->with('widgetConfiguration')
    ->get();

foreach ($widgets as $widget) {
    echo $widget->widgetConfiguration->widget_name; // Single Query!
}
```

---

## 🔧 Testing Examples

### Unit Test
```php
public function test_user_can_hide_widget()
{
    $user = User::factory()->create();
    $widget = WidgetConfiguration::factory()->create();
    
    UserWidgetPreference::create([
        'user_id' => $user->id,
        'widget_configuration_id' => $widget->id,
        'is_visible' => false,
    ]);
    
    $service = new DashboardConfigurationService();
    $widgets = $service->getWidgetsForUser($user);
    
    $this->assertNotContains($widget->widget_class, $widgets);
}
```

### Feature Test
```php
public function test_sales_role_sees_only_sales_widgets()
{
    $user = User::factory()->create();
    $user->assignRole('Sales');
    
    $this->actingAs($user)
        ->get('/admin')
        ->assertSeeLivewire('stats-overview-widget')
        ->assertSeeLivewire('sales-chart-widget')
        ->assertDontSeeLivewire('stock-movements-chart-widget');
}
```

---

## ✅ Best Practices

### 1. Always Use Service Layer
```php
// Bad ❌
$widgets = WidgetConfiguration::where('is_active', true)->get();

// Good ✅
$widgets = app(DashboardConfigurationService::class)
    ->getWidgetsForUser(auth()->user());
```

### 2. Invalidate Cache on Updates
```php
// After any configuration change
app(DashboardConfigurationService::class)
    ->invalidateUserWidgetCache($user);
```

### 3. Use Transactions for Bulk Operations
```php
DB::transaction(function () use ($roleId, $widgets) {
    foreach ($widgets as $widget) {
        RoleWidgetDefault::create([
            'role_id' => $roleId,
            'widget_configuration_id' => $widget['id'],
            'is_visible' => $widget['visible'],
        ]);
    }
});
```

---

**هذه الأمثلة توضح قوة ومرونة النظام! 🚀**
