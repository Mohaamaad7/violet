# 👨‍💻 Developer Guide - Zero-Config Dashboard Permissions

## Overview

This system provides **automatic** permission management for all Filament components.

**You don't need to do anything special!** Just create your components normally and they will:
1. Be auto-discovered
2. Appear in Role Permissions page
3. Respect access control automatically

---

## How It Works (Simplified)

```
You create Widget/Resource/Page
         ↓
AdminPanelProvider discovers it
         ↓
Checks DashboardConfigurationService for permissions
         ↓
Only shows to users with access ✅
```

---

## Creating New Components

### Widgets

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyNewStatsWidget extends StatsOverviewWidget
{
    // Just write your widget normally!
    // No special traits or base classes needed.
    
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', \App\Models\User::count()),
        ];
    }
}
```

**That's it!** The widget will automatically:
- Appear in the Role Permissions page
- Be filterable by role
- Show/hide based on permissions

---

### Resources

```php
<?php

namespace App\Filament\Resources;

use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    
    // Define your resource normally
    // No special code needed!
    
    public static function getNavigationGroup(): ?string
    {
        return 'المخزون';  // This determines the group in permissions
    }
}
```

---

### Pages

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MyReportPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 10;
    
    protected string $view = 'filament.pages.my-report';
    
    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }
    
    // No special code needed!
}
```

---

## Optional: Using Base Classes

While not required, you can use base classes for consistency:

```php
// Widgets
class MyWidget extends BaseWidget { }

// Resources  
class MyResource extends BaseResource { }

// Pages
class MyPage extends BasePage { }
```

These base classes include traits that provide additional features like `shouldRegisterNavigation()` override.

---

## Custom Grouping

### Automatic Grouping
The system automatically groups components based on:
1. `getNavigationGroup()` method return value
2. Keywords in class name (sales, inventory, etc.)

### Manual Grouping
Add a static property to override:

```php
class MyWidget extends Widget
{
    public static string $dashboardGroup = 'sales';
}
```

### Available Groups
| Key | Arabic Label | Keywords |
|-----|--------------|----------|
| `sales` | المبيعات | sales, order, revenue, payment |
| `inventory` | المخزون | stock, warehouse, product |
| `customers` | العملاء | customer, user, client |
| `catalog` | الكتالوج | category, product, brand |
| `content` | المحتوى | banner, slider, email |
| `geography` | الجغرافيا | city, country |
| `system` | النظام | role, permission, setting |
| `general` | عام | (default) |

---

## Localized Names

### For Widgets
```php
public static function getLabel(): ?string
{
    return __('admin.widgets.my_widget');
}
```

### For Resources
```php
public static function getNavigationLabel(): string
{
    return __('admin.resources.products');
}
```

### For Pages
```php
public static function getNavigationLabel(): string
{
    return __('admin.pages.my_report');
}
```

---

## How Permissions Are Checked

### Navigation Filtering (Automatic)
The `AdminPanelProvider` uses a custom navigation builder that:
1. Discovers all components from filesystem
2. Checks each against `DashboardConfigurationService`
3. Only adds allowed items to navigation

### Direct URL Protection (Middleware)
`EnforcePageAccess` middleware catches direct URL access:
- Checks user permissions
- Returns 403 if denied
- Works even if navigation shows the item (shouldn't happen)

---

## Default Behavior

| Scenario | Result |
|----------|--------|
| No record in database | **VISIBLE** (default) |
| Record with deny | **HIDDEN** |
| User is super-admin | **ALWAYS VISIBLE** |

---

## File Locations

| Component | Directory |
|-----------|-----------|
| Widgets | `app/Filament/Widgets/` |
| Resources | `app/Filament/Resources/` |
| Pages | `app/Filament/Pages/` |
| Service | `app/Services/DashboardConfigurationService.php` |
| Provider | `app/Providers/Filament/AdminPanelProvider.php` |
| Middleware | `app/Http/Middleware/EnforcePageAccess.php` |

---

## Database Tables

| Table | Purpose |
|-------|---------|
| `role_widget_defaults` | Widget visibility overrides |
| `role_resource_access` | Resource CRUD permissions |
| `role_page_access` | Page access overrides |

---

## Testing Your Component

1. Create your component
2. Clear cache: `php artisan cache:clear`
3. Go to Role Permissions page
4. Your component should appear automatically
5. Toggle permissions and test with different users

---

## Debugging

### Check if component is discovered
```bash
php artisan tinker
> app(\App\Services\DashboardConfigurationService::class)->discoverAllWidgets()
> app(\App\Services\DashboardConfigurationService::class)->discoverAllResources()
> app(\App\Services\DashboardConfigurationService::class)->discoverAllPages()
```

### Check permissions for a user
```bash
php artisan tinker
> $service = app(\App\Services\DashboardConfigurationService::class)
> $service->canAccessPage(\App\Filament\Pages\SalesReport::class)
```

---

## Summary

| Old Way | Zero-Config Way |
|---------|-----------------|
| Extend special base class | Extend regular Filament class |
| Add traits | Nothing needed |
| Register in config | Auto-discovered |
| Manual permission checks | Automatic filtering |

**Just create your component and forget about permissions!** ✅

---

## 📅 Last Updated
**Date:** 2026-01-01
