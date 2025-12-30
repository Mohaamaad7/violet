# 🛠️ Implementation Guide - Step by Step

## 📊 Progress Tracker

| Phase | الوصف | الحالة | تاريخ الإنجاز |
|-------|-------|--------|---------------|
| **Phase 1** | Fix Current Issues (Translations) | ✅ Done | 30 Dec 2025 |
| **Phase 2** | Database Structure (Migrations) | ✅ Done | 30 Dec 2025 |
| **Phase 3** | Models & Relationships | ✅ Done | 30 Dec 2025 |
| **Phase 4** | Service Layer | ⏳ Next | - |
| **Phase 5** | Seeders | ⏳ Pending | - |
| **Phase 6** | Filament Resources | ⏳ Pending | - |
| **Phase 7** | Panel Integration | ⏳ Pending | - |

---

## 📋 Prerequisites

- ✅ Laravel 11/12
- ✅ Filament 4
- ✅ Spatie Permission Package
- ✅ Database: MySQL 8.0+

---

## 🎯 Implementation Phases

## Phase 1: Fix Current Issues ⚠️ ✅ COMPLETED

### Step 1.1: Fix Translation Files

**Problem:** Navigation groups مستخدمة في الكود لكن مش موجودة في ملف الترجمة

**File:** `lang/ar/admin.php`

```php
'nav' => [
    'catalog' => 'الكتالوج',           // ← إضافة
    'sales' => 'المبيعات',
    'inventory' => 'المخزون',
    'customers' => 'العملاء',
    'content' => 'المحتوى',            // ← إضافة
    'geography' => 'الإعدادات الجغرافية', // ← إضافة
    'settings' => 'الإعدادات',
    'system' => 'النظام',              // ← إضافة
],
```

**File:** `lang/en/admin.php` (إنشاء if not exists)

```php
'nav' => [
    'catalog' => 'Catalog',
    'sales' => 'Sales',
    'inventory' => 'Inventory',
    'customers' => 'Customers',
    'content' => 'Content',
    'geography' => 'Geographic Settings',
    'settings' => 'Settings',
    'system' => 'System',
],
```

---

### Step 1.2: Standardize Navigation Groups

**الهدف:** توحيد كل Resources لاستخدام نفس الـ pattern

**Resources تحتاج تعديل:**

#### CountryResource.php
```php
// قبل:
protected static UnitEnum|string|null $navigationGroup = 'الإعدادات الجغرافية';

// بعد:
public static function getNavigationGroup(): ?string
{
    return __('admin.nav.geography');
}
```

#### GovernorateResource.php
```php
// Same as above
public static function getNavigationGroup(): ?string
{
    return __('admin.nav.geography');
}
```

#### CityResource.php
```php
// Same as above
public static function getNavigationGroup(): ?string
{
    return __('admin.nav.geography');
}
```

#### EmailTemplateResource.php
```php
// قبل:
protected static UnitEnum|string|null $navigationGroup = 'الإعدادات';

// بعد:
public static function getNavigationGroup(): ?string
{
    return __('admin.nav.settings');
}
```

#### EmailLogResource.php
```php
// Same as above
public static function getNavigationGroup(): ?string
{
    return __('admin.nav.settings');
}
```

#### SettingResource.php
```php
// قبل:
protected static ?string $navigationGroup = 'النظام';

// بعد:
public static function getNavigationGroup(): ?string
{
    return __('admin.nav.system');
}
```

---

## Phase 2: Database Structure 🗄️

### Step 2.1: Create Migrations

#### Migration 1: `create_widget_configurations_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('widget_class')->unique()->comment('Full class name');
            $table->string('widget_name')->comment('Human-readable name');
            $table->string('widget_group', 100)->nullable()->comment('Group: sales, inventory, general');
            $table->text('description')->nullable()->comment('Widget description');
            $table->boolean('is_active')->default(true)->comment('Enable/disable globally');
            $table->integer('default_order')->default(0)->comment('Default position');
            $table->integer('default_column_span')->default(1)->comment('Default width 1-4');
            $table->timestamps();
            
            $table->index('widget_class');
            $table->index('is_active');
            $table->index('widget_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_configurations');
    }
};
```

#### Migration 2: `create_user_widget_preferences_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_widget_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('widget_configuration_id')->constrained()->onDelete('cascade');
            $table->boolean('is_visible')->default(true);
            $table->integer('order_position')->default(0);
            $table->integer('column_span')->default(1);
            $table->timestamps();
            
            $table->unique(['user_id', 'widget_configuration_id'], 'unique_user_widget');
            $table->index('user_id');
            $table->index('is_visible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_widget_preferences');
    }
};
```

#### Migration 3: `create_role_widget_defaults_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_widget_defaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('widget_configuration_id')->constrained()->onDelete('cascade');
            $table->boolean('is_visible')->default(true);
            $table->integer('order_position')->default(0);
            $table->integer('column_span')->default(1);
            $table->timestamps();
            
            $table->unique(['role_id', 'widget_configuration_id'], 'unique_role_widget');
            $table->index('role_id');
            $table->index('is_visible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_widget_defaults');
    }
};
```

#### Migration 4: `create_resource_configurations_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('resource_class')->unique()->comment('Full class name');
            $table->string('resource_name')->comment('Human-readable name');
            $table->string('navigation_group', 100)->nullable()->comment('Navigation group key');
            $table->boolean('is_active')->default(true);
            $table->integer('default_navigation_sort')->default(0);
            $table->timestamps();
            
            $table->index('resource_class');
            $table->index('navigation_group');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_configurations');
    }
};
```

#### Migration 5: `create_role_resource_access_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_resource_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('resource_configuration_id')->constrained()->onDelete('cascade');
            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('is_visible_in_navigation')->default(true);
            $table->integer('navigation_sort')->default(0);
            $table->timestamps();
            
            $table->unique(['role_id', 'resource_configuration_id'], 'unique_role_resource');
            $table->index('role_id');
            $table->index('is_visible_in_navigation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_resource_access');
    }
};
```

#### Migration 6: `create_navigation_group_configurations_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_group_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('group_key', 100)->unique()->comment('Unique key: admin.nav.catalog');
            $table->string('group_label_ar')->comment('Arabic label');
            $table->string('group_label_en')->comment('English label');
            $table->string('icon', 100)->nullable()->comment('Heroicon name');
            $table->boolean('is_active')->default(true);
            $table->integer('default_order')->default(0);
            $table->timestamps();
            
            $table->index('group_key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_group_configurations');
    }
};
```

#### Migration 7: `create_role_navigation_groups_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_navigation_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('navigation_group_configuration_id', 'nav_group_config_id')->constrained()->onDelete('cascade');
            $table->boolean('is_visible')->default(true);
            $table->integer('order_position')->default(0);
            $table->timestamps();
            
            $table->unique(['role_id', 'navigation_group_configuration_id'], 'unique_role_nav_group');
            $table->index('role_id');
            $table->index('is_visible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_navigation_groups');
    }
};
```

---

### Step 2.2: Run Migrations

```bash
php artisan migrate
```

---

## Phase 3: Models & Relationships 🏗️

### Step 3.1: Create Models

#### `app/Models/WidgetConfiguration.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WidgetConfiguration extends Model
{
    protected $fillable = [
        'widget_class',
        'widget_name',
        'widget_group',
        'description',
        'is_active',
        'default_order',
        'default_column_span',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_order' => 'integer',
        'default_column_span' => 'integer',
    ];

    public function roleDefaults(): HasMany
    {
        return $this->hasMany(RoleWidgetDefault::class);
    }

    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserWidgetPreference::class);
    }
}
```

#### `app/Models/UserWidgetPreference.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWidgetPreference extends Model
{
    protected $fillable = [
        'user_id',
        'widget_configuration_id',
        'is_visible',
        'order_position',
        'column_span',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order_position' => 'integer',
        'column_span' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function widgetConfiguration(): BelongsTo
    {
        return $this->belongsTo(WidgetConfiguration::class);
    }
}
```

#### `app/Models/RoleWidgetDefault.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleWidgetDefault extends Model
{
    protected $fillable = [
        'role_id',
        'widget_configuration_id',
        'is_visible',
        'order_position',
        'column_span',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'order_position' => 'integer',
        'column_span' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function widgetConfiguration(): BelongsTo
    {
        return $this->belongsTo(WidgetConfiguration::class);
    }
}
```

**(Continue with remaining models...)**

---

## Phase 4: Service Layer 🔧

### Step 4.1: Create DashboardConfigurationService

#### `app/Services/DashboardConfigurationService.php`

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\WidgetConfiguration;
use App\Models\ResourceConfiguration;
use App\Models\NavigationGroupConfiguration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardConfigurationService
{
    /**
     * Get widgets for user
     */
    public function getWidgetsForUser(User $user): array
    {
        $cacheKey = "user.{$user->id}.widgets";
        
        return Cache::remember($cacheKey, 3600, function () use ($user) {
            // Implementation here
            return $this->buildUserWidgets($user);
        });
    }

    /**
     * Build user widgets with preference cascade
     */
    protected function buildUserWidgets(User $user): array
    {
        // 1. Get active widgets
        $widgets = WidgetConfiguration::where('is_active', true)->get();
        
        // 2. Get user preferences
        $userPrefs = $user->widgetPreferences()
            ->with('widgetConfiguration')
            ->get()
            ->keyBy('widget_configuration_id');
        
        // 3. Get role defaults
        $roleDefaults = $user->roles()
            ->with('widgetDefaults.widgetConfiguration')
            ->get()
            ->pluck('widgetDefaults')
            ->flatten()
            ->keyBy('widget_configuration_id');
        
        // 4. Build final widget list
        $result = [];
        foreach ($widgets as $widget) {
            // User preference takes priority
            if (isset($userPrefs[$widget->id])) {
                $pref = $userPrefs[$widget->id];
                if ($pref->is_visible) {
                    $result[] = [
                        'class' => $widget->widget_class,
                        'order' => $pref->order_position,
                        'span' => $pref->column_span,
                    ];
                }
            }
            // Fall back to role default
            elseif (isset($roleDefaults[$widget->id])) {
                $default = $roleDefaults[$widget->id];
                if ($default->is_visible) {
                    $result[] = [
                        'class' => $widget->widget_class,
                        'order' => $default->order_position,
                        'span' => $default->column_span,
                    ];
                }
            }
            // Fall back to widget default (show nothing if no config)
        }
        
        // Sort by order
        usort($result, fn($a, $b) => $a['order'] <=> $b['order']);
        
        // Return just the class names
        return array_column($result, 'class');
    }

    /**
     * Invalidate user widget cache
     */
    public function invalidateUserWidgetCache(User $user): void
    {
        Cache::forget("user.{$user->id}.widgets");
    }

    // ... more methods
}
```

---

## Phase 5: Seeders 🌱

### Step 5.1: Widget Configuration Seeder

#### `database/seeders/WidgetConfigurationSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\WidgetConfiguration;
use Illuminate\Database\Seeder;

class WidgetConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $widgets = [
            [
                'widget_class' => 'App\\Filament\\Widgets\\StatsOverviewWidget',
                'widget_name' => 'إحصائيات عامة',
                'widget_group' => 'general',
                'description' => 'عرض الإيرادات اليومية، الطلبات الجديدة، العملاء، والمخزون',
                'default_order' => 1,
                'default_column_span' => 4,
            ],
            [
                'widget_class' => 'App\\Filament\\Widgets\\SalesReportStatsWidget',
                'widget_name' => 'إحصائيات المبيعات التفصيلية',
                'widget_group' => 'sales',
                'description' => 'تقارير المبيعات التفصيلية',
                'default_order' => 2,
                'default_column_span' => 2,
            ],
            [
                'widget_class' => 'App\\Filament\\Widgets\\SalesChartWidget',
                'widget_name' => 'رسم بياني للمبيعات',
                'widget_group' => 'sales',
                'description' => 'رسم بياني يوضح المبيعات اليومية',
                'default_order' => 3,
                'default_column_span' => 2,
            ],
            [
                'widget_class' => 'App\\Filament\\Widgets\\RecentOrdersWidget',
                'widget_name' => 'آخر الطلبات',
                'widget_group' => 'sales',
                'description' => 'عرض آخر الطلبات الواردة',
                'default_order' => 4,
                'default_column_span' => 4,
            ],
            [
                'widget_class' => 'App\\Filament\\Widgets\\LowStockAlertWidget',
                'widget_name' => 'تنبيهات المخزون المنخفض',
                'widget_group' => 'inventory',
                'description' => 'المنتجات التي وصلت للحد الأدنى من المخزون',
                'default_order' => 5,
                'default_column_span' => 2,
            ],
            [
                'widget_class' => 'App\\Filament\\Widgets\\PendingReturnsWidget',
                'widget_name' => 'المرتجعات المعلقة',
                'widget_group' => 'sales',
                'description' => 'المرتجعات التي تحتاج مراجعة',
                'default_order' => 6,
                'default_column_span' => 2,
            ],
            [
                'widget_class' => 'App\\Filament\\Widgets\\StockMovementsChartWidget',
                'widget_name' => 'رسم بياني لحركات المخزون',
                'widget_group' => 'inventory',
                'description' => 'رسم بياني يوضح حركات المخزون',
                'default_order' => 7,
                'default_column_span' => 2,
            ],
            [
                'widget_class' => 'App\\Filament\\Widgets\\StockValueWidget',
                'widget_name' => 'قيمة المخزون',
                'widget_group' => 'inventory',
                'description' => 'إجمالي قيمة المخزون الحالي',
                'default_order' => 8,
                'default_column_span' => 2,
            ],
        ];

        foreach ($widgets as $widget) {
            WidgetConfiguration::updateOrCreate(
                ['widget_class' => $widget['widget_class']],
                $widget
            );
        }
    }
}
```

---

## Testing Plan 🧪

### Unit Tests

```php
// tests/Unit/DashboardConfigurationServiceTest.php
public function test_get_widgets_for_user_with_custom_preferences()
{
    // Arrange
    $user = User::factory()->create();
    $widget = WidgetConfiguration::factory()->create();
    UserWidgetPreference::create([
        'user_id' => $user->id,
        'widget_configuration_id' => $widget->id,
        'is_visible' => false,
    ]);

    // Act
    $service = new DashboardConfigurationService();
    $widgets = $service->getWidgetsForUser($user);

    // Assert
    $this->assertNotContains($widget->widget_class, $widgets);
}
```

---

## Deployment Checklist ✅

- [ ] Phase 1: Fix translations completed
- [ ] Phase 1: Standardize navigation groups completed
- [ ] Phase 2: All migrations created
- [ ] Phase 2: Migrations tested locally
- [ ] Phase 3: All models created
- [ ] Phase 3: Relationships tested
- [ ] Phase 4: Service layer implemented
- [ ] Phase 4: Service layer tested
- [ ] Phase 5: All seeders created
- [ ] Phase 5: Seeders tested
- [ ] Phase 6: Filament resources created
- [ ] Phase 7: Admin panel integration
- [ ] Phase 8: End-to-end testing
- [ ] Phase 9: Documentation updated
- [ ] Phase 10: Production deployment

---

**Let's start with Phase 1! 🚀**
