# 🗄️ Database Schema - Detailed Documentation

## Overview

هذا المستند يوضح بالتفصيل الـ Database Schema المقترح لنظام Dashboard Customization.

---

## 📊 Entity Relationship Diagram (ERD)

```
┌────────────────────────────────┐
│   widget_configurations         │
│  - id                           │
│  - widget_class (unique)        │
│  - widget_name                  │
│  - widget_group                 │
│  - description                  │
│  - is_active                    │
│  - default_order                │
│  - default_column_span          │
└────────────────────────────────┘
           │                 │
           │                 │
    ┌──────┘                 └──────┐
    │                               │
    ↓                               ↓
┌────────────────────┐  ┌────────────────────────┐
│ role_widget_defaults│ │user_widget_preferences │
│  - id               │ │  - id                  │
│  - role_id (FK)     │ │  - user_id (FK)        │
│  - widget_config_id │ │  - widget_config_id    │
│  - is_visible       │ │  - is_visible          │
│  - order_position   │ │  - order_position      │
│  - column_span      │ │  - column_span         │
└────────────────────┘  └────────────────────────┘
           │                               │
           │                               │
           ↓                               ↓
    ┌──────────┐                  ┌──────────┐
    │  roles   │                  │  users   │
    └──────────┘                  └──────────┘


┌────────────────────────────────┐
│  resource_configurations        │
│  - id                           │
│  - resource_class (unique)      │
│  - resource_name                │
│  - navigation_group             │
│  - is_active                    │
│  - default_navigation_sort      │
└────────────────────────────────┘
           │
           │
           ↓
┌────────────────────────┐
│  role_resource_access   │
│  - id                   │
│  - role_id (FK)         │
│  - resource_config_id   │
│  - can_view             │
│  - can_create           │
│  - can_edit             │
│  - can_delete           │
│  - is_visible_in_nav    │
│  - navigation_sort      │
└────────────────────────┘
           │
           │
           ↓
    ┌──────────┐
    │  roles   │
    └──────────┘


┌────────────────────────────────┐
│ navigation_group_configurations │
│  - id                           │
│  - group_key (unique)           │
│  - group_label_ar               │
│  - group_label_en               │
│  - icon                         │
│  - is_active                    │
│  - default_order                │
└────────────────────────────────┘
           │
           │
           ↓
┌────────────────────────┐
│ role_navigation_groups  │
│  - id                   │
│  - role_id (FK)         │
│  - nav_group_id (FK)    │
│  - is_visible           │
│  - order_position       │
└────────────────────────┘
           │
           │
           ↓
    ┌──────────┐
    │  roles   │
    └──────────┘
```

---

## 📋 Table Definitions

### 1. `widget_configurations`

**الغرض:** تخزين معلومات عن كل Widget متاح في النظام

```sql
CREATE TABLE widget_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    widget_class VARCHAR(255) NOT NULL COMMENT 'Full class name: App\\Filament\\Widgets\\StatsOverviewWidget',
    widget_name VARCHAR(255) NOT NULL COMMENT 'Human-readable name: إحصائيات عامة',
    widget_group VARCHAR(100) COMMENT 'Group: sales, inventory, general, etc.',
    description TEXT COMMENT 'Widget description for admin reference',
    is_active BOOLEAN DEFAULT true COMMENT 'Enable/disable widget globally',
    default_order INT DEFAULT 0 COMMENT 'Default position in dashboard',
    default_column_span INT DEFAULT 1 COMMENT 'Default width (1-4 columns)',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    UNIQUE KEY unique_widget_class (widget_class),
    INDEX idx_widget_class (widget_class),
    INDEX idx_is_active (is_active),
    INDEX idx_widget_group (widget_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**مثال على البيانات:**
```sql
INSERT INTO widget_configurations (widget_class, widget_name, widget_group, description, default_order, default_column_span) VALUES
('App\\Filament\\Widgets\\StatsOverviewWidget', 'إحصائيات عامة', 'general', 'عرض الإيرادات والطلبات والعملاء والمخزون', 1, 4),
('App\\Filament\\Widgets\\SalesChartWidget', 'رسم بياني للمبيعات', 'sales', 'رسم بياني يوضح المبيعات اليومية', 2, 2),
('App\\Filament\\Widgets\\LowStockAlertWidget', 'تنبيهات المخزون', 'inventory', 'المنتجات ذات المخزون المنخفض', 3, 2);
```

---

### 2. `user_widget_preferences`

**الغرض:** تخزين تفضيلات المستخدم الشخصية للـ Widgets

```sql
CREATE TABLE user_widget_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    widget_configuration_id BIGINT UNSIGNED NOT NULL,
    is_visible BOOLEAN DEFAULT true COMMENT 'Show/hide this widget for this user',
    order_position INT DEFAULT 0 COMMENT 'Custom order for this user',
    column_span INT DEFAULT 1 COMMENT 'Custom width for this user',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_configuration_id) REFERENCES widget_configurations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_widget (user_id, widget_configuration_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_visible (is_visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**مثال:**
```sql
-- User #5 يريد إخفاء widget المخزون
INSERT INTO user_widget_preferences (user_id, widget_configuration_id, is_visible, order_position, column_span) VALUES
(5, 3, false, 0, 2);

-- User #7 يريد widget المبيعات في الأول
INSERT INTO user_widget_preferences (user_id, widget_configuration_id, is_visible, order_position, column_span) VALUES
(7, 2, true, 1, 3);
```

---

### 3. `role_widget_defaults`

**الغرض:** تحديد الـ Widgets الافتراضية لكل Role

```sql
CREATE TABLE role_widget_defaults (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    widget_configuration_id BIGINT UNSIGNED NOT NULL,
    is_visible BOOLEAN DEFAULT true COMMENT 'Default visibility for this role',
    order_position INT DEFAULT 0 COMMENT 'Default order for this role',
    column_span INT DEFAULT 1 COMMENT 'Default width for this role',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_configuration_id) REFERENCES widget_configurations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_widget (role_id, widget_configuration_id),
    INDEX idx_role_id (role_id),
    INDEX idx_is_visible (is_visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**مثال:**
```sql
-- Role "Sales" يشوف بس widgets المبيعات
INSERT INTO role_widget_defaults (role_id, widget_configuration_id, is_visible, order_position, column_span) VALUES
(3, 1, true, 1, 4),  -- Stats Overview
(3, 2, true, 2, 2),  -- Sales Chart
(3, 4, true, 3, 2);  -- Recent Orders

-- Role "Warehouse" يشوف بس widgets المخزون
INSERT INTO role_widget_defaults (role_id, widget_configuration_id, is_visible, order_position, column_span) VALUES
(4, 1, true, 1, 4),  -- Stats Overview
(4, 5, true, 2, 2),  -- Low Stock Alert
(4, 7, true, 3, 2);  -- Stock Movements Chart
```

---

### 4. `resource_configurations`

**الغرض:** تخزين معلومات عن كل Resource متاح في النظام

```sql
CREATE TABLE resource_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resource_class VARCHAR(255) NOT NULL COMMENT 'Full class name: App\\Filament\\Resources\\Products\\ProductResource',
    resource_name VARCHAR(255) NOT NULL COMMENT 'Human-readable name: المنتجات',
    navigation_group VARCHAR(100) COMMENT 'Navigation group key: admin.nav.catalog',
    is_active BOOLEAN DEFAULT true COMMENT 'Enable/disable resource globally',
    default_navigation_sort INT DEFAULT 0 COMMENT 'Default position in navigation',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    UNIQUE KEY unique_resource_class (resource_class),
    INDEX idx_resource_class (resource_class),
    INDEX idx_navigation_group (navigation_group),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**مثال:**
```sql
INSERT INTO resource_configurations (resource_class, resource_name, navigation_group, default_navigation_sort) VALUES
('App\\Filament\\Resources\\Products\\ProductResource', 'المنتجات', 'admin.nav.catalog', 1),
('App\\Filament\\Resources\\CategoryResource', 'الفئات', 'admin.nav.catalog', 2),
('App\\Filament\\Resources\\Orders\\OrderResource', 'الطلبات', 'admin.nav.sales', 1),
('App\\Filament\\Resources\\Payments\\PaymentResource', 'المدفوعات', 'admin.nav.sales', 2);
```

---

### 5. `role_resource_access`

**الغرض:** تحديد صلاحيات الوصول لكل Role على كل Resource

```sql
CREATE TABLE role_resource_access (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    resource_configuration_id BIGINT UNSIGNED NOT NULL,
    can_view BOOLEAN DEFAULT true COMMENT 'Can view list',
    can_create BOOLEAN DEFAULT false COMMENT 'Can create new records',
    can_edit BOOLEAN DEFAULT false COMMENT 'Can edit existing records',
    can_delete BOOLEAN DEFAULT false COMMENT 'Can delete records',
    is_visible_in_navigation BOOLEAN DEFAULT true COMMENT 'Show in sidebar navigation',
    navigation_sort INT DEFAULT 0 COMMENT 'Custom sort order for this role',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_configuration_id) REFERENCES resource_configurations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_resource (role_id, resource_configuration_id),
    INDEX idx_role_id (role_id),
    INDEX idx_is_visible (is_visible_in_navigation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**مثال:**
```sql
-- Role "Sales" له full access على Orders
INSERT INTO role_resource_access 
(role_id, resource_configuration_id, can_view, can_create, can_edit, can_delete, is_visible_in_navigation) VALUES
(3, 3, true, true, true, false, true);

-- Role "Sales" له read-only access على Products
INSERT INTO role_resource_access 
(role_id, resource_configuration_id, can_view, can_create, can_edit, can_delete, is_visible_in_navigation) VALUES
(3, 1, true, false, false, false, true);

-- Role "Warehouse" له full access على Products
INSERT INTO role_resource_access 
(role_id, resource_configuration_id, can_view, can_create, can_edit, can_delete, is_visible_in_navigation) VALUES
(4, 1, true, true, true, true, true);

-- Role "Warehouse" مايشوفش Orders في الـ Navigation
INSERT INTO role_resource_access 
(role_id, resource_configuration_id, can_view, can_create, can_edit, can_delete, is_visible_in_navigation) VALUES
(4, 3, false, false, false, false, false);
```

---

### 6. `navigation_group_configurations`

**الغرض:** تخزين معلومات عن كل Navigation Group

```sql
CREATE TABLE navigation_group_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_key VARCHAR(100) NOT NULL COMMENT 'Unique key: admin.nav.catalog',
    group_label_ar VARCHAR(255) NOT NULL COMMENT 'Arabic label: الكتالوج',
    group_label_en VARCHAR(255) NOT NULL COMMENT 'English label: Catalog',
    icon VARCHAR(100) COMMENT 'Heroicon name (optional)',
    is_active BOOLEAN DEFAULT true COMMENT 'Enable/disable group globally',
    default_order INT DEFAULT 0 COMMENT 'Default position in sidebar',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    UNIQUE KEY unique_group_key (group_key),
    INDEX idx_group_key (group_key),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**مثال:**
```sql
INSERT INTO navigation_group_configurations (group_key, group_label_ar, group_label_en, icon, default_order) VALUES
('admin.nav.catalog', 'الكتالوج', 'Catalog', 'heroicon-o-rectangle-stack', 1),
('admin.nav.sales', 'المبيعات', 'Sales', 'heroicon-o-shopping-cart', 2),
('admin.nav.inventory', 'المخزون', 'Inventory', 'heroicon-o-cube', 3),
('admin.nav.customers', 'العملاء', 'Customers', 'heroicon-o-users', 4),
('admin.nav.content', 'المحتوى', 'Content', 'heroicon-o-photo', 5),
('admin.nav.geography', 'الإعدادات الجغرافية', 'Geographic Settings', 'heroicon-o-map', 6),
('admin.nav.settings', 'الإعدادات', 'Settings', 'heroicon-o-cog', 7),
('admin.nav.system', 'النظام', 'System', 'heroicon-o-shield-check', 8);
```

---

### 7. `role_navigation_groups`

**الغرض:** تحديد الـ Navigation Groups المرئية لكل Role

```sql
CREATE TABLE role_navigation_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    navigation_group_id BIGINT UNSIGNED NOT NULL,
    is_visible BOOLEAN DEFAULT true COMMENT 'Show/hide this group for this role',
    order_position INT DEFAULT 0 COMMENT 'Custom order for this role',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (navigation_group_id) REFERENCES navigation_group_configurations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_nav_group (role_id, navigation_group_id),
    INDEX idx_role_id (role_id),
    INDEX idx_is_visible (is_visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**مثال:**
```sql
-- Role "Sales" يشوف بس: المبيعات + العملاء
INSERT INTO role_navigation_groups (role_id, navigation_group_id, is_visible, order_position) VALUES
(3, 2, true, 1),  -- المبيعات
(3, 4, true, 2);  -- العملاء

-- Role "Warehouse" يشوف بس: الكتالوج + المخزون
INSERT INTO role_navigation_groups (role_id, navigation_group_id, is_visible, order_position) VALUES
(4, 1, true, 1),  -- الكتالوج
(4, 3, true, 2);  -- المخزون

-- Role "Super Admin" يشوف كل حاجة
INSERT INTO role_navigation_groups (role_id, navigation_group_id, is_visible, order_position) VALUES
(1, 1, true, 1),
(1, 2, true, 2),
(1, 3, true, 3),
(1, 4, true, 4),
(1, 5, true, 5),
(1, 6, true, 6),
(1, 7, true, 7),
(1, 8, true, 8);
```

---

## 🔄 Data Flow Examples

### Example 1: Loading Dashboard for Sales User

```
User logs in (Role: Sales, User ID: 15)
    ↓
DashboardConfigurationService::getWidgetsForUser(15)
    ↓
1. Check user_widget_preferences for user_id=15
   → Found: User hid LowStockAlertWidget
    ↓
2. Get role_widget_defaults for role_id=3 (Sales)
   → Found: StatsOverview, SalesChart, RecentOrders
    ↓
3. Merge + Apply user overrides
   → Result: [StatsOverview, SalesChart, RecentOrders]
    ↓
4. Return ordered array to AdminPanelProvider
    ↓
Dashboard renders with 3 widgets only
```

### Example 2: Loading Navigation for Warehouse User

```
User accesses /admin (Role: Warehouse, User ID: 22)
    ↓
DashboardConfigurationService::getNavigationGroupsForUser(22)
    ↓
1. Get role_navigation_groups for role_id=4 (Warehouse)
   → Found: Catalog, Inventory groups only
    ↓
2. Get resource_configurations filtered by these groups
   → Catalog: Products, Categories
   → Inventory: Warehouses, StockCounts, StockMovements
    ↓
3. Check role_resource_access for role_id=4
   → Products: can_view=true, is_visible_in_navigation=true
   → Orders: is_visible_in_navigation=false (hidden!)
    ↓
4. Return filtered navigation structure
    ↓
Sidebar shows only: Catalog, Inventory groups
```

---

## 🔍 Query Examples

### Get all widgets for a specific role

```sql
SELECT 
    wc.widget_class,
    wc.widget_name,
    rwd.is_visible,
    rwd.order_position,
    rwd.column_span
FROM widget_configurations wc
INNER JOIN role_widget_defaults rwd ON wc.id = rwd.widget_configuration_id
WHERE rwd.role_id = 3
  AND wc.is_active = true
  AND rwd.is_visible = true
ORDER BY rwd.order_position;
```

### Get user's custom widget preferences (with fallback to role defaults)

```sql
SELECT 
    wc.widget_class,
    COALESCE(uwp.is_visible, rwd.is_visible) as is_visible,
    COALESCE(uwp.order_position, rwd.order_position) as order_position,
    COALESCE(uwp.column_span, rwd.column_span) as column_span
FROM widget_configurations wc
LEFT JOIN user_widget_preferences uwp ON wc.id = uwp.widget_configuration_id AND uwp.user_id = 15
LEFT JOIN role_widget_defaults rwd ON wc.id = rwd.widget_configuration_id AND rwd.role_id = (
    SELECT role_id FROM model_has_roles WHERE model_id = 15 AND model_type = 'App\\Models\\User' LIMIT 1
)
WHERE wc.is_active = true
  AND COALESCE(uwp.is_visible, rwd.is_visible, false) = true
ORDER BY order_position;
```

### Get accessible resources for a role

```sql
SELECT 
    rc.resource_class,
    rc.resource_name,
    rc.navigation_group,
    rra.can_view,
    rra.can_create,
    rra.can_edit,
    rra.can_delete,
    rra.is_visible_in_navigation
FROM resource_configurations rc
INNER JOIN role_resource_access rra ON rc.id = rra.resource_configuration_id
WHERE rra.role_id = 3
  AND rc.is_active = true
  AND rra.is_visible_in_navigation = true
ORDER BY rc.navigation_group, rra.navigation_sort;
```

---

## 📊 Data Volume Estimates

| Table | Estimated Rows | Notes |
|-------|---------------|-------|
| widget_configurations | ~10-20 | عدد الـ Widgets في النظام |
| user_widget_preferences | ~100-500 | User-specific customizations |
| role_widget_defaults | ~40-80 | 5 roles × 8-16 widgets |
| resource_configurations | ~30-50 | عدد الـ Resources |
| role_resource_access | ~150-250 | 5 roles × 30-50 resources |
| navigation_group_configurations | ~8-12 | عدد الـ Groups |
| role_navigation_groups | ~40-60 | 5 roles × 8-12 groups |

**Total:** حوالي 400-1000 row - حجم صغير جداً ✅

---

## 🚀 Performance Optimization

### Caching Strategy

```php
// Cache widgets for role for 1 hour
Cache::remember("role.{$roleId}.widgets", 3600, function() use ($roleId) {
    return $this->getRoleWidgets($roleId);
});

// Invalidate on update
Cache::forget("role.{$roleId}.widgets");
```

### Indexes

كل الـ Foreign Keys عليها indexes ✅  
كل الـ boolean columns اللي بنعمل عليها filtering عليها indexes ✅

---

## ✅ Migration Plan

1. Run migrations في الترتيب ده:
   - `create_widget_configurations_table`
   - `create_user_widget_preferences_table`
   - `create_role_widget_defaults_table`
   - `create_resource_configurations_table`
   - `create_role_resource_access_table`
   - `create_navigation_group_configurations_table`
   - `create_role_navigation_groups_table`

2. Run seeders:
   - `WidgetConfigurationSeeder`
   - `ResourceConfigurationSeeder`
   - `NavigationGroupSeeder`
   - `DefaultRoleConfigurationsSeeder`

3. Test queries في Tinker

---

**Ready for implementation! 🎉**
