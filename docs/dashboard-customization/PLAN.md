# 📋 Dashboard Customization System - Project Plan

## 📊 تاريخ الإعداد
**التاريخ:** 30 ديسمبر 2025  
**الحالة:** مرحلة التخطيط  
**الأولوية:** عالية جداً

---

## 🎯 الهدف من المشروع

إنشاء نظام ديناميكي ومرن للتحكم في:
1. **Dashboard Widgets** - التحكم في الـ Widgets المعروضة لكل دور
2. **Navigation Resources** - التحكم في الـ Resources المتاحة لكل دور
3. **Navigation Groups** - التحكم في المجموعات الظاهرة في القائمة الجانبية
4. **User-Specific Customization** - تخصيص على مستوى المستخدم الفردي

**الهدف النهائي:** إدارة كاملة من Dashboard بدون تعديل الكود

---

## 📷 الوضع الحالي (Current State Analysis)

### 1️⃣ **Navigation Groups الموجودة حالياً**

#### من ملف `lang/ar/admin.php` (6 Groups فقط):
```php
'nav' => [
    'inventory' => 'المخزون',
    'products' => 'المنتجات',
    'orders' => 'الطلبات',
    'sales' => 'المبيعات',
    'customers' => 'العملاء',
    'settings' => 'الإعدادات',
]
```

#### Navigation Groups المستخدمة فعلياً في Resources (لكن مش في الترجمة):
1. **`admin.nav.catalog`** → `__('admin.nav.catalog')` - (مفقودة من الترجمة ❌)
   - ProductResource
   - CategoryResource

2. **`admin.nav.sales`** → `__('admin.nav.sales')` - ✅ موجودة
   - OrderResource
   - PaymentResource
   - CouponResource
   - OrderReturnResource
   - (+ تقرير المبيعات - Page مش Resource)

3. **`admin.nav.inventory`** → `__('admin.nav.inventory')` - ✅ موجودة
   - WarehouseResource
   - StockMovementResource
   - StockCountResource
   - LowStockProductResource
   - OutOfStockProductResource

4. **`admin.nav.customers`** → `trans_db('admin.nav.customers')` - ✅ موجودة
   - CustomerResource

5. **`admin.nav.content`** → `__('admin.nav.content')` - (مفقودة من الترجمة ❌)
   - SliderResource
   - BannerResource

6. **`admin.nav.system`** → `__('admin.nav.system')` - (مفقودة من الترجمة ❌)
   - UserResource
   - RoleResource
   - PermissionResource
   - TranslationResource

7. **`'الإعدادات الجغرافية'`** - Hardcoded (مش ديناميكي ❌)
   - CountryResource
   - GovernorateResource
   - CityResource

8. **`'الإعدادات'`** - Hardcoded (مش ديناميكي ❌)
   - EmailTemplateResource
   - EmailLogResource

9. **`'النظام'`** - Hardcoded (مش ديناميكي ❌)
   - SettingResource

---

### 2️⃣ **Resources الموجودة (26 Resource)**

| # | Resource | Navigation Group | ملاحظات |
|---|----------|-----------------|---------|
| 1 | ProductResource | `admin.nav.catalog` | ❌ الترجمة مفقودة |
| 2 | CategoryResource | `admin.nav.catalog` | ❌ الترجمة مفقودة |
| 3 | OrderResource | `admin.nav.sales` | ✅ |
| 4 | PaymentResource | `admin.nav.sales` | ✅ |
| 5 | CouponResource | `admin.nav.sales` | ✅ |
| 6 | OrderReturnResource | `admin.nav.sales` | ✅ |
| 7 | WarehouseResource | `admin.nav.inventory` | ✅ |
| 8 | StockMovementResource | `admin.nav.inventory` | ✅ |
| 9 | StockCountResource | `admin.nav.inventory` | ✅ |
| 10 | LowStockProductResource | `admin.nav.inventory` | ✅ |
| 11 | OutOfStockProductResource | `admin.nav.inventory` | ✅ |
| 12 | CustomerResource | `admin.nav.customers` | ✅ |
| 13 | SliderResource | `admin.nav.content` | ❌ الترجمة مفقودة |
| 14 | BannerResource | `admin.nav.content` | ❌ الترجمة مفقودة |
| 15 | UserResource | `admin.nav.system` | ❌ الترجمة مفقودة |
| 16 | RoleResource | `admin.nav.system` | ❌ الترجمة مفقودة |
| 17 | PermissionResource | `admin.nav.system` | ❌ الترجمة مفقودة |
| 18 | TranslationResource | `admin.nav.system` | ❌ الترجمة مفقودة |
| 19 | CountryResource | `'الإعدادات الجغرافية'` | ⚠️ Hardcoded |
| 20 | GovernorateResource | `'الإعدادات الجغرافية'` | ⚠️ Hardcoded |
| 21 | CityResource | `'الإعدادات الجغرافية'` | ⚠️ Hardcoded |
| 22 | EmailTemplateResource | `'الإعدادات'` | ⚠️ Hardcoded |
| 23 | EmailLogResource | `'الإعدادات'` | ⚠️ Hardcoded |
| 24 | SettingResource | `'النظام'` | ⚠️ Hardcoded |
| 25 | *SalesReportPage* | `admin.nav.sales` | 📄 Page (مش Resource) |

---

### 3️⃣ **Widgets الموجودة (8 Widgets)**

| # | Widget | الوصف | المجال |
|---|--------|-------|--------|
| 1 | StatsOverviewWidget | إحصائيات عامة (الإيرادات، الطلبات، العملاء، المخزون) | عام |
| 2 | SalesReportStatsWidget | إحصائيات المبيعات التفصيلية | المبيعات |
| 3 | SalesChartWidget | رسم بياني للمبيعات | المبيعات |
| 4 | RecentOrdersWidget | آخر الطلبات | المبيعات |
| 5 | LowStockAlertWidget | تنبيهات المخزون المنخفض | المخزون |
| 6 | PendingReturnsWidget | المرتجعات المعلقة | المبيعات/المخزون |
| 7 | StockMovementsChartWidget | رسم بياني لحركات المخزون | المخزون |
| 8 | StockValueWidget | قيمة المخزون | المخزون/المالية |

---

### 4️⃣ **Roles الموجودة**

من الصورة المرفقة، عندك الأدوار التالية:
1. Super Admin
2. Manager
3. Sales
4. Warehouse
5. Customer Service
6. (ممكن يكون في أدوار تانية)

---

### 5️⃣ **المشاكل الحالية**

#### 🔴 **مشكلة 1: ملفات الترجمة ناقصة**
- `admin.nav.catalog` مش موجود في `lang/ar/admin.php`
- `admin.nav.content` مش موجود
- `admin.nav.system` مش موجود

#### 🔴 **مشكلة 2: Navigation Groups مش موحدة**
- بعض Resources بتستخدم `__('admin.nav.xyz')`
- بعض Resources بتستخدم Hardcoded strings زي `'الإعدادات الجغرافية'`
- مافيش consistency

#### 🔴 **مشكلة 3: Dashboard واحد للكل**
- كل المستخدمين بيشوفوا نفس الـ 8 Widgets
- موظف المبيعات بيشوف widgets المخزون (مش محتاجها)
- موظف المخازن بيشوف widgets المبيعات (مش محتاجها)

#### 🔴 **مشكلة 4: Resources مش مربوطة بالصلاحيات بشكل ديناميكي**
- لازم تعدل في الكود عشان تخفي Resource
- مافيش واجهة إدارية للتحكم

#### 🔴 **مشكلة 5: مافيش تخصيص على مستوى المستخدم**
- لو عايز موظف مبيعات معين يشوف widget معين (مش كل موظفين المبيعات)
- مافيش طريقة حالياً

---

## 🎯 الحل المقترح (Proposed Solution)

### **Architecture Overview**

```
┌─────────────────────────────────────────────────────────────┐
│                    AdminPanelProvider                        │
│  - Dynamic Widget Loading                                    │
│  - Dynamic Resource Filtering                                │
│  - Dynamic Navigation Groups                                 │
└─────────────────────────────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────┐
│              DashboardConfigurationService                   │
│  - getWidgetsForUser()                                       │
│  - getResourcesForUser()                                     │
│  - getNavigationGroupsForUser()                              │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        ↓                     ↓                     ↓
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ widget_      │    │ resource_    │    │ navigation_  │
│ configurations│   │ configurations│   │ group_configs│
└──────────────┘    └──────────────┘    └──────────────┘
```

---

### **Phase 1: Database Schema** 📊

#### 1.1. جدول `widget_configurations`
```sql
CREATE TABLE widget_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    widget_class VARCHAR(255) NOT NULL,
    widget_name VARCHAR(255) NOT NULL,
    widget_group VARCHAR(100),
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    default_order INT DEFAULT 0,
    default_column_span INT DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_widget_class (widget_class),
    INDEX idx_is_active (is_active)
);
```

#### 1.2. جدول `user_widget_preferences`
```sql
CREATE TABLE user_widget_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    widget_configuration_id BIGINT UNSIGNED NOT NULL,
    is_visible BOOLEAN DEFAULT true,
    order_position INT DEFAULT 0,
    column_span INT DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_configuration_id) REFERENCES widget_configurations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_widget (user_id, widget_configuration_id)
);
```

#### 1.3. جدول `role_widget_defaults`
```sql
CREATE TABLE role_widget_defaults (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    widget_configuration_id BIGINT UNSIGNED NOT NULL,
    is_visible BOOLEAN DEFAULT true,
    order_position INT DEFAULT 0,
    column_span INT DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_configuration_id) REFERENCES widget_configurations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_widget (role_id, widget_configuration_id)
);
```

#### 1.4. جدول `resource_configurations`
```sql
CREATE TABLE resource_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resource_class VARCHAR(255) NOT NULL UNIQUE,
    resource_name VARCHAR(255) NOT NULL,
    navigation_group VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    default_navigation_sort INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_resource_class (resource_class),
    INDEX idx_navigation_group (navigation_group),
    INDEX idx_is_active (is_active)
);
```

#### 1.5. جدول `role_resource_access`
```sql
CREATE TABLE role_resource_access (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    resource_configuration_id BIGINT UNSIGNED NOT NULL,
    can_view BOOLEAN DEFAULT true,
    can_create BOOLEAN DEFAULT false,
    can_edit BOOLEAN DEFAULT false,
    can_delete BOOLEAN DEFAULT false,
    is_visible_in_navigation BOOLEAN DEFAULT true,
    navigation_sort INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_configuration_id) REFERENCES resource_configurations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_resource (role_id, resource_configuration_id)
);
```

#### 1.6. جدول `navigation_group_configurations`
```sql
CREATE TABLE navigation_group_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_key VARCHAR(100) NOT NULL UNIQUE,
    group_label_ar VARCHAR(255) NOT NULL,
    group_label_en VARCHAR(255) NOT NULL,
    icon VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    default_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_group_key (group_key),
    INDEX idx_is_active (is_active)
);
```

#### 1.7. جدول `role_navigation_groups`
```sql
CREATE TABLE role_navigation_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    navigation_group_id BIGINT UNSIGNED NOT NULL,
    is_visible BOOLEAN DEFAULT true,
    order_position INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (navigation_group_id) REFERENCES navigation_group_configurations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_nav_group (role_id, navigation_group_id)
);
```

---

### **Phase 2: Models & Relationships** 🏗️

#### 2.1. Models المطلوبة
1. `WidgetConfiguration.php`
2. `UserWidgetPreference.php`
3. `RoleWidgetDefault.php`
4. `ResourceConfiguration.php`
5. `RoleResourceAccess.php`
6. `NavigationGroupConfiguration.php`
7. `RoleNavigationGroup.php`

#### 2.2. Relationships
```php
// User Model
public function widgetPreferences()
{
    return $this->hasMany(UserWidgetPreference::class);
}

// Role Model
public function widgetDefaults()
{
    return $this->hasMany(RoleWidgetDefault::class);
}

public function resourceAccess()
{
    return $this->hasMany(RoleResourceAccess::class);
}

public function navigationGroups()
{
    return $this->belongsToMany(
        NavigationGroupConfiguration::class,
        'role_navigation_groups'
    );
}
```

---

### **Phase 3: Service Layer** 🔧

#### 3.1. `DashboardConfigurationService.php`

```php
class DashboardConfigurationService
{
    /**
     * Get widgets for current user
     */
    public function getWidgetsForUser(User $user): array
    {
        // 1. Check user-specific preferences first
        // 2. Fall back to role defaults
        // 3. Fall back to system defaults
        // 4. Return ordered array of widget classes
    }
    
    /**
     * Get resources for current user
     */
    public function getResourcesForUser(User $user): array
    {
        // 1. Get user's roles
        // 2. Get accessible resources per role
        // 3. Merge and deduplicate
        // 4. Apply user-specific overrides if exist
        // 5. Return filtered resource classes
    }
    
    /**
     * Get navigation groups for current user
     */
    public function getNavigationGroupsForUser(User $user): array
    {
        // 1. Get user's roles
        // 2. Get visible navigation groups per role
        // 3. Merge and deduplicate
        // 4. Return ordered array
    }
    
    /**
     * Auto-discover and register widgets
     */
    public function discoverWidgets(): void
    {
        // Scan app/Filament/Widgets directory
        // Register new widgets automatically
    }
    
    /**
     * Auto-discover and register resources
     */
    public function discoverResources(): void
    {
        // Scan app/Filament/Resources directory
        // Register new resources automatically
    }
}
```

---

### **Phase 4: Filament Resources** 🎨

#### 4.1. `WidgetConfigurationResource`
- إدارة الـ Widgets المتاحة
- تحديد الـ Widget Groups
- تفعيل/تعطيل Widgets

#### 4.2. `RoleWidgetConfigurationResource`
- ربط Roles بـ Widgets
- تحديد الترتيب والحجم
- Preview Dashboard لكل Role

#### 4.3. `ResourceConfigurationResource`
- إدارة الـ Resources المتاحة
- تحديد Navigation Groups
- تفعيل/تعطيل Resources

#### 4.4. `RoleResourceAccessResource`
- ربط Roles بـ Resources
- تحديد الصلاحيات (View, Create, Edit, Delete)
- تحديد الظهور في Navigation

#### 4.5. `NavigationGroupResource`
- إدارة Navigation Groups
- تحديد الترجمات (AR/EN)
- تحديد الأيقونات والترتيب

---

### **Phase 5: Admin Panel Integration** ⚙️

#### 5.1. تعديل `AdminPanelProvider.php`

```php
public function panel(Panel $panel): Panel
{
    $dashboardService = app(DashboardConfigurationService::class);
    $currentUser = auth()->user();
    
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        // ... existing config ...
        ->widgets(function () use ($dashboardService, $currentUser) {
            return $dashboardService->getWidgetsForUser($currentUser);
        })
        ->discoverResources(...)
        ->resources(function () use ($dashboardService, $currentUser) {
            return $dashboardService->getResourcesForUser($currentUser);
        })
        ->navigationGroups(function () use ($dashboardService, $currentUser) {
            return $dashboardService->getNavigationGroupsForUser($currentUser);
        });
}
```

#### 5.2. Resource Authorization Middleware

```php
class FilterResourcesByRole implements Middleware
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        $service = app(DashboardConfigurationService::class);
        
        // Filter resources dynamically
        $accessibleResources = $service->getResourcesForUser($user);
        
        // Apply filter to Filament registry
        
        return $next($request);
    }
}
```

---

### **Phase 6: Seeding & Migration** 🌱

#### 6.1. `WidgetConfigurationSeeder`
- تسجيل كل الـ 8 Widgets الموجودة
- تحديد الـ Widget Groups

#### 6.2. `ResourceConfigurationSeeder`
- تسجيل كل الـ 26 Resources الموجودة
- تحديد Navigation Groups الصحيحة

#### 6.3. `NavigationGroupSeeder`
- تسجيل كل Navigation Groups
- إضافة الترجمات

#### 6.4. `DefaultRoleConfigurationsSeeder`
- إعداد تكوينات افتراضية لكل Role:
  - **Super Admin** → كل حاجة
  - **Manager** → كل حاجة ماعدا الإعدادات
  - **Sales** → المبيعات + العملاء فقط
  - **Warehouse** → المخزون + المنتجات فقط
  - **Customer Service** → الطلبات + العملاء فقط

---

### **Phase 7: Artisan Commands** 🛠️

#### 7.1. `php artisan dashboard:discover`
- Auto-discover widgets and resources
- Update configurations table
- Safe to run multiple times

#### 7.2. `php artisan dashboard:sync-roles`
- Sync role configurations
- Apply default settings to new roles

#### 7.3. `php artisan dashboard:reset-user {user_id}`
- Reset user preferences to role defaults

---

## 📋 Implementation Roadmap

### **Sprint 1: Foundation (Week 1)**
- [ ] إنشاء Migrations
- [ ] إنشاء Models
- [ ] إنشاء Service Layer
- [ ] Unit Tests للـ Service

### **Sprint 2: Auto-Discovery (Week 1)**
- [ ] Widget Discovery Command
- [ ] Resource Discovery Command
- [ ] Seeding Scripts
- [ ] Integration Tests

### **Sprint 3: Filament Resources (Week 2)**
- [ ] WidgetConfigurationResource
- [ ] RoleWidgetConfigurationResource
- [ ] ResourceConfigurationResource
- [ ] RoleResourceAccessResource
- [ ] NavigationGroupResource

### **Sprint 4: Panel Integration (Week 2)**
- [ ] تعديل AdminPanelProvider
- [ ] Dynamic Widget Loading
- [ ] Dynamic Resource Filtering
- [ ] Dynamic Navigation Groups

### **Sprint 5: User Customization (Week 3)**
- [ ] User Widget Preferences UI
- [ ] Drag & Drop Dashboard
- [ ] Save/Reset Preferences
- [ ] User Experience Testing

### **Sprint 6: Testing & Documentation (Week 3)**
- [ ] Feature Tests
- [ ] UI/UX Testing
- [ ] User Guide Documentation
- [ ] Code Documentation

---

## 🎁 Expected Benefits

### ✅ **للمستخدمين:**
1. Dashboard نظيف ومنظم حسب دورهم
2. تركيز أفضل على المهام المطلوبة
3. تخصيص شخصي حسب الاحتياج

### ✅ **للمطورين:**
1. إضافة Widget/Resource جديد يتسجل تلقائياً
2. مافيش حاجة للتعديل في الكود للتحكم في الصلاحيات
3. Maintainability أعلى

### ✅ **للإدارة:**
1. تحكم كامل من Dashboard
2. مرونة في إعطاء صلاحيات مخصصة
3. سهولة في إدارة الأدوار

---

## ⚠️ Important Considerations

### 🔒 **Security**
- جميع التغييرات تمر عبر Policies
- User permissions تُفحص في كل request
- Audit trail لكل التغييرات

### ⚡ **Performance**
- Caching للـ configurations
- Eager loading للـ relationships
- Optimized queries

### 🔄 **Backward Compatibility**
- النظام الحالي يشتغل كما هو
- التفعيل تدريجي
- Rollback plan جاهز

---

## 📝 Next Steps

1. ✅ **Review هذا الـ Plan**
2. ⏳ **الموافقة على الـ Database Schema**
3. ⏳ **البدء في التنفيذ - Sprint 1**

---

**ملاحظة:** هذا Plan قابل للتعديل بناءً على ملاحظاتك واحتياجات المشروع.
