# 🔧 Troubleshooting Guide

## Common Issues and Solutions

---

## 🔴 Issue 1: Pages/Resources still showing after disabling

### Symptom
You disabled a page or resource for a role, but it still appears in the sidebar.

### Cause
- Old cached navigation
- User is super-admin (bypasses all checks)
- Navigation not using the filtered builder

### Solution
```bash
# Clear all cache
php artisan cache:clear
php artisan optimize:clear

# If on Cloudflare, clear their cache too
```

Also verify:
- The user is NOT super-admin
- You're logged in as the correct role

---

## 🔴 Issue 2: New widget/resource/page not appearing in Role Permissions

### Symptom
You created a new component but it doesn't show in the permissions page.

### Cause
- Discovery cache not cleared
- File in wrong location
- Class is abstract
- Syntax error in the file

### Solution
```bash
# Clear discovery cache
php artisan cache:clear

# Verify file location
# Widgets: app/Filament/Widgets/
# Resources: app/Filament/Resources/
# Pages: app/Filament/Pages/
```

Check that your class:
- Is NOT abstract
- Extends the correct base class (Widget, Resource, Page)
- Has no PHP syntax errors

---

## 🔴 Issue 3: "Call to undefined method" errors

### Symptom
```
Call to undefined method App\Services\DashboardConfigurationService::someMethod()
```

### Cause
The method doesn't exist in the service, or old cached autoload.

### Solution
```bash
composer dump-autoload
php artisan cache:clear
```

---

## 🔴 Issue 4: 403 Forbidden on allowed page

### Symptom
User gets 403 error even though they should have access.

### Cause
- EnforcePageAccess middleware blocking
- Database has old deny record
- Cache not cleared after permission change

### Solution
```bash
php artisan cache:clear
```

Check database directly:
```sql
SELECT * FROM role_page_access WHERE page_class LIKE '%YourPage%';
-- Delete if exists and shouldn't:
DELETE FROM role_page_access WHERE page_class = 'App\Filament\Pages\YourPage';
```

---

## 🔴 Issue 5: Navigation groups showing empty

### Symptom
A navigation group appears in sidebar but has no items.

### Cause
All items in that group are denied for the current role.

### Solution
This is expected behavior! Enable at least one item in the group, or the group shouldn't show at all (bug fix needed in AdminPanelProvider).

---

## 🔴 Issue 6: Traits not working (pages still visible)

### Problem we faced during development
Adding traits like `ChecksPageAccess` wasn't hiding pages because Filament asks each class individually if it should register.

### Why it happened
The trait adds `shouldRegisterNavigation()` method, but Filament's discovery process might already have cached the navigation.

### Solution implemented
We moved to Solution #3: **Custom Navigation Builder** in AdminPanelProvider that:
1. Takes over ALL navigation building
2. Checks permissions BEFORE adding items
3. No traits needed in individual classes

---

## 🔴 Issue 7: "Make it impossible to fail" requirement

### Problem we faced
We wanted developers to NOT have to remember adding traits or base classes.

### Solutions considered

| Solution | Result |
|----------|--------|
| 1. Add traits manually | ❌ Developer might forget |
| 2. Architecture tests | ⚠️ Catches at test time, not runtime |
| 3. Custom Navigation Builder | ✅ Works automatically |

### Final solution
Custom `buildFilteredNavigation()` in AdminPanelProvider that checks permissions at the Panel level, not the component level.

---

## 🔴 Issue 8: DashboardConfig resources not appearing

### Problem we faced
Resources like `WidgetConfigurationResource`, `ResourceConfigurationResource` were being filtered out.

### Cause
We had this code:
```php
if (Str::contains($resourceClass, 'DashboardConfig')) {
    continue; // Skip!
}
```

### Solution
Removed the filter to allow all resources:
```php
// Skip only BaseResource
if (Str::endsWith($resourceClass, 'BaseResource')) {
    continue;
}
```

---

## 🔴 Issue 9: Navigation appears but URL gives 403

### Symptom
Item shows in sidebar → Click → 403 error

### Cause
Navigation filtering and middleware filtering are out of sync.

### Solution
Both systems now use the same `DashboardConfigurationService`:
- AdminPanelProvider uses it for navigation
- EnforcePageAccess uses it for URL protection

Clear cache to sync:
```bash
php artisan cache:clear
```

---

## 🔴 Issue 10: Migration not running

### Symptom
```
Nothing to migrate.
```

### Cause
Migration was already run, or table exists.

### Solution
Check if table exists:
```bash
php artisan tinker
> Schema::hasTable('role_page_access')
```

If not, run:
```bash
php artisan migrate:fresh --seed
# WARNING: This drops ALL tables!
```

Or create manually:
```sql
CREATE TABLE role_page_access (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    page_class VARCHAR(255) NOT NULL,
    can_access BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY (role_id, page_class)
);
```

---

## 📞 Still Having Issues?

1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode: `APP_DEBUG=true` in `.env`
3. Use browser developer tools (Network tab) for AJAX errors
4. Contact the development team

---

## 📅 Last Updated
**Date:** 2026-01-01
