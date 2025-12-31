# Dashboard Customization - Developer Guide

## 🎯 Quick Reference

When creating new Filament components, extend these base classes to automatically get role-based access control:

### For Resources
```php
<?php

namespace App\Filament\Resources\MyNewResource;

use App\Filament\Resources\BaseResource;

class MyNewResource extends BaseResource  // ← Extend BaseResource instead of Resource
{
    protected static ?string $model = MyModel::class;
    // ... rest of your resource
}
```

### For Stats Widgets
```php
<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\BaseStatsWidget;

class MyNewStatsWidget extends BaseStatsWidget  // ← Extend BaseStatsWidget
{
    protected function getStats(): array
    {
        return [
            // Your stats
        ];
    }
}
```

### For Chart Widgets
```php
<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\BaseChartWidget;

class MyNewChartWidget extends BaseChartWidget  // ← Extend BaseChartWidget
{
    protected function getData(): array
    {
        return [
            'datasets' => [...],
            'labels' => [...],
        ];
    }
    
    protected function getType(): string
    {
        return 'line'; // or 'bar', 'pie', 'doughnut'
    }
}
```

### For Table Widgets
```php
<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\BaseTableWidget;
use Filament\Tables\Table;

class MyNewTableWidget extends BaseTableWidget  // ← Extend BaseTableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(MyModel::query())
            ->columns([...]);
    }
}
```

---

## 📋 After Creating New Components

After creating a new Resource or Widget, run:

```bash
# Discover and register new components
php artisan dashboard:discover

# Sync with all roles (give super-admin full access)
php artisan dashboard:sync-roles --super-admin-all

# Clear cache
php artisan cache:clear
```

That's it! The new component will be automatically managed through the Role Permissions UI.

---

## 🔧 Available Base Classes

| Type | Base Class | Use For |
|------|------------|---------|
| Resource | `BaseResource` | All Filament Resources |
| Stats | `BaseStatsWidget` | Stats overview cards |
| Chart | `BaseChartWidget` | Line, bar, pie charts |
| Table | `BaseTableWidget` | Table widgets |
| Generic | `BaseWidget` | Other widgets |

---

## 🚀 How It Works

1. **Base classes include traits** that check user permissions
2. **Widgets**: `ChecksWidgetVisibility` trait → calls `canView()`
3. **Resources**: `ChecksResourceAccess` trait → calls `canViewAny()`, `canCreate()`, `canEdit()`, `canDelete()`, `shouldRegisterNavigation()`
4. Permission checks look at `role_widget_defaults` and `role_resource_access` tables
5. **Super-admin** always has full access (bypasses all checks)

---

## 📝 Configuration Tables

| Table | Purpose |
|-------|---------|
| `widget_configurations` | Registered widgets |
| `role_widget_defaults` | Widget visibility per role |
| `resource_configurations` | Registered resources |
| `role_resource_access` | Resource permissions per role |
| `navigation_group_configurations` | Navigation groups |
| `role_navigation_groups` | Nav group visibility per role |

---

## 🔐 Managing Permissions

Use the **Role Permissions** page in admin panel:
- Navigate to: `System > Role Permissions`
- Select a role
- Toggle widgets/resources/navigation groups on/off
