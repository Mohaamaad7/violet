# Zero-Config Dashboard Permissions - Developer Guide

## Overview

This system provides **automatic** permission management for all Filament components:
- ✅ Widgets
- ✅ Resources
- ✅ Pages

**You don't need to do anything special!** Just create your components normally.

---

## How It Works

```
Developer creates Widget/Resource/Page
         ↓
System auto-discovers it at runtime
         ↓
Appears in Role Permissions page
         ↓
Admin controls access per role
         ↓
Components auto-hide for unauthorized users
```

---

## Creating New Components

### Widgets

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;

class MyNewWidget extends StatsOverviewWidget
{
    // Your widget code - nothing special needed!
    
    protected function getStats(): array
    {
        return [
            // ...
        ];
    }
}
```

**Best Practice (Optional):** Extend `BaseWidget` for consistency:
```php
class MyNewWidget extends BaseWidget
```

### Resources

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

class MyNewResource extends Resource
{
    // Your resource code - nothing special needed!
}
```

**Best Practice (Optional):** Extend `BaseResource` for consistency:
```php
class MyNewResource extends BaseResource
```

### Pages

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MyNewPage extends Page
{
    // Your page code - nothing special needed!
}
```

**Best Practice (Optional):** Extend `BasePage` for consistency:
```php
class MyNewPage extends BasePage
```

---

## Custom Grouping

By default, components are grouped by smart detection from class names.

To override, add a static property:

```php
class MyWidget extends BaseWidget
{
    public static string $dashboardGroup = 'sales';
}
```

Available groups: `sales`, `inventory`, `catalog`, `customers`, `content`, `geography`, `system`, `general`

---

## How Permissions Work

### Default Behavior
- **All components are VISIBLE by default**
- Access is only restricted when explicitly set in Role Permissions

### Database Tables
- `role_widget_defaults` - Widget visibility overrides
- `role_resource_access` - Resource CRUD permissions
- `role_page_access` - Page access overrides

### Permission Hierarchy
1. `super-admin` role → Always has full access
2. No override in database → Component is visible/accessible
3. Override exists with deny → Component is hidden/blocked

---

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                 AdminPanelProvider                       │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Auto-discovers and filters ALL components        │   │
│  │ based on DashboardConfigurationService           │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│              DashboardConfigurationService               │
│  ┌─────────────────────────────────────────────────┐   │
│  │ - discoverAllWidgets()                           │   │
│  │ - discoverAllResources()                         │   │
│  │ - discoverAllPages()                             │   │
│  │ - canAccessResource(class, permission)           │   │
│  │ - canAccessPage(class)                           │   │
│  │ - isWidgetVisibleForUser(class, user)           │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│              Database (Exceptions Only)                  │
│  ┌───────────────────┐  ┌───────────────────────────┐  │
│  │ role_widget_      │  │ role_resource_access      │  │
│  │ defaults          │  │ role_page_access          │  │
│  └───────────────────┘  └───────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

---

## Testing

Architecture tests ensure code quality:

```bash
php artisan test --filter=Architecture
```

These tests encourage (but don't require) using Base classes:
- `BaseWidget` → Includes `ChecksWidgetVisibility`
- `BaseResource` → Includes `ChecksResourceAccess`
- `BasePage` → Includes `ChecksPageAccess`

---

## Middleware Protection

Even if Navigation filtering fails, the `EnforcePageAccess` middleware provides backup protection:

- Checks every request to Filament pages/resources
- Returns 403 if access denied
- Works automatically without any configuration

---

## Summary

| Aspect | Old Approach | Zero-Config Approach |
|--------|--------------|----------------------|
| Discovery | Manual registration | Automatic from filesystem |
| Permissions | Traits required | Automatic in Panel |
| Defaults | Hidden until enabled | Visible until disabled |
| Developer Work | Add traits, register | Just create the file |
| Navigation Filtering | Per-component | Centralized in Panel |

**Make it impossible to fail!** 🎯
