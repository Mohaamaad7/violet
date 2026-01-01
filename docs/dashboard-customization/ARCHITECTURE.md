# 🏗️ Architecture - Zero-Config Dashboard Permissions

## System Overview

The Zero-Config Dashboard Permissions system provides **automatic** role-based access control for Filament components without requiring developers to add any special code.

---

## Core Principle: "Make It Impossible to Fail"

```
Traditional Approach:
Developer creates component → Must add trait → Must extend base class → Must register

Zero-Config Approach:
Developer creates component → DONE! System handles everything automatically.
```

---

## Component Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                         FILAMENT PANEL                            │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                  AdminPanelProvider                         │  │
│  │                                                             │  │
│  │  ->navigation(function(NavigationBuilder) {                 │  │
│  │      return $this->buildFilteredNavigation($builder);       │  │
│  │  })                                                         │  │
│  │                                                             │  │
│  │  ┌───────────────────────────────────────────────────┐     │  │
│  │  │         buildFilteredNavigation()                  │     │  │
│  │  │                                                    │     │  │
│  │  │  For each Resource:                               │     │  │
│  │  │    → Ask Service: canAccessResource()?            │     │  │
│  │  │    → If YES: Add to navigation                    │     │  │
│  │  │    → If NO: Skip                                  │     │  │
│  │  │                                                    │     │  │
│  │  │  For each Page:                                   │     │  │
│  │  │    → Ask Service: canAccessPage()?                │     │  │
│  │  │    → If YES: Add to navigation                    │     │  │
│  │  │    → If NO: Skip                                  │     │  │
│  │  └───────────────────────────────────────────────────┘     │  │
│  └────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘
                                 │
                                 ▼
┌──────────────────────────────────────────────────────────────────┐
│                 DashboardConfigurationService                     │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                    DISCOVERY LAYER                          │  │
│  │  • discoverAllWidgets() - Scans Filament/Widgets/          │  │
│  │  • discoverAllResources() - Scans Filament/Resources/      │  │
│  │  • discoverAllPages() - Scans Filament/Pages/              │  │
│  └────────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                   PERMISSION LAYER                          │  │
│  │  • canAccessResource(class, permission) → bool              │  │
│  │  • canAccessPage(class) → bool                              │  │
│  │  • isWidgetVisibleForUser(class, user) → bool              │  │
│  └────────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────────┐  │
│  │                    GROUPING LAYER                           │  │
│  │  • getWidgetGroup(class) - Smart detection                  │  │
│  │  • getResourceGroup(class) - From NavigationGroup           │  │
│  │  • getPageGroup(class) - From NavigationGroup               │  │
│  └────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘
                                 │
                                 ▼
┌──────────────────────────────────────────────────────────────────┐
│                         DATABASE                                  │
│  ┌────────────┐  ┌─────────────────┐  ┌─────────────────┐       │
│  │role_widget │  │role_resource_   │  │role_page_       │       │
│  │_defaults   │  │access           │  │access           │       │
│  │            │  │                 │  │                 │       │
│  │• role_id   │  │• role_id        │  │• role_id        │       │
│  │• widget_   │  │• resource_class │  │• page_class     │       │
│  │  class     │  │• can_view       │  │• can_access     │       │
│  │• is_visible│  │• can_create     │  │                 │       │
│  │            │  │• can_edit       │  │                 │       │
│  │            │  │• can_delete     │  │                 │       │
│  └────────────┘  └─────────────────┘  └─────────────────┘       │
└──────────────────────────────────────────────────────────────────┘
```

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Providers/Filament/AdminPanelProvider.php` | Central navigation filtering |
| `app/Services/DashboardConfigurationService.php` | Discovery and permission checking |
| `app/Filament/Pages/RolePermissions.php` | Admin UI for managing permissions |
| `app/Http/Middleware/EnforcePageAccess.php` | Backup URL protection |
| `app/Models/RoleWidgetDefault.php` | Widget visibility model |
| `app/Models/RoleResourceAccess.php` | Resource access model |
| `app/Models/RolePageAccess.php` | Page access model |

---

## Permission Logic

### Default: Everything is VISIBLE

```php
// If no record in database → VISIBLE/ACCESSIBLE
if ($override === null) {
    return true; // Allow access
}
```

### Exception: Override exists

```php
// If record exists → Use the stored value
return $override->is_visible; // or can_view, can_access
```

### Super Admin: Always allowed

```php
if ($user->hasRole('super-admin')) {
    return true; // Bypass all checks
}
```

---

## Data Flow

### When User Logs In:

```
1. User authenticates
           ↓
2. Filament calls AdminPanelProvider->navigation()
           ↓
3. buildFilteredNavigation() runs
           ↓
4. Service discovers all Resources & Pages
           ↓
5. For each component: Check permission
           ↓
6. Build navigation with ONLY allowed items
           ↓
7. User sees filtered sidebar
```

### When Admin Changes Permission:

```
1. Admin toggles permission in RolePermissions page
           ↓
2. Database is updated (record created/deleted)
           ↓
3. Cache is cleared
           ↓
4. Next page load: User sees updated navigation
```

---

## Caching Strategy

| What | Cache Key | TTL | Cleared When |
|------|-----------|-----|--------------|
| Widget classes | `all_widget_classes` | 1 hour | Permission change |
| Resource classes | `all_resource_classes` | 1 hour | Permission change |
| Page classes | `all_page_classes` | 1 hour | Permission change |
| User's visible widgets | `visible_widgets_user_{id}` | 5 min | Permission change |

---

## Middleware Stack

```php
->middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    AuthenticateSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
    SubstituteBindings::class,
    DisableBladeIconComponents::class,
    DispatchServingFilamentEvent::class,
    SetLocale::class,
    ApplyDashboardConfiguration::class,
])
->authMiddleware([
    Authenticate::class,
    EnforcePageAccess::class,  // ← Backup protection
])
```

---

## Smart Grouping Keywords

```php
$groupKeywords = [
    'sales' => ['sales', 'order', 'revenue', 'payment', 'return', 'coupon'],
    'inventory' => ['stock', 'warehouse', 'inventory', 'product', 'batch', 'movement'],
    'customers' => ['customer', 'user', 'client'],
    'catalog' => ['category', 'product', 'brand'],
    'content' => ['banner', 'slider', 'email', 'template'],
    'geography' => ['city', 'country', 'governorate'],
    'system' => ['role', 'permission', 'setting', 'config', 'user'],
];
```

---

## Security Layers

| Layer | What it does |
|-------|--------------|
| **Navigation Filtering** | Hides items from sidebar |
| **EnforcePageAccess Middleware** | Blocks direct URL access |
| **Resource canView() check** | Blocks view if permission denied |
| **Database constraints** | Foreign keys ensure data integrity |

---

## Testing the System

```bash
# Clear cache
php artisan cache:clear

# Run as specific user
php artisan tinker
> auth()->loginUsingId(2); // User with 'sales' role
> app(DashboardConfigurationService::class)->canAccessPage(SalesReport::class);
```

---

## 📅 Last Updated
**Date:** 2026-01-01
