# 📋 Changelog - Dashboard Permissions System

## Version History

---

## [2.0.0] - 2026-01-01 - Zero-Config V2 (True Auto-Filtering)

### 🎉 Major Release: Complete Zero-Config System

This release eliminates the need for ANY special code in components.

### Added
- **Custom Navigation Builder** in AdminPanelProvider
  - Automatically filters ALL resources based on permissions
  - Automatically filters ALL pages based on permissions
  - No traits or base classes required

- **Page Access Control**
  - New `role_page_access` database table
  - New `RolePageAccess` model
  - Full CRUD for page permissions

- **EnforcePageAccess Middleware**
  - Backup protection for direct URL access
  - Returns 403 for denied pages

- **Smart Grouping**
  - Automatic group detection from class names
  - Keyword-based categorization
  - Navigation group mapping

- **Bulk Actions**
  - Enable/disable all widgets per group
  - Enable/disable all resources per group
  - Enable/disable all pages per group

### Changed
- AdminPanelProvider now uses `->navigation()` callback
- Permissions page redesigned with grouped card layout
- Database stores only exceptions (not all components)

### Fixed
- DashboardConfig resources now appear in permissions
- Navigation filtering now works without traits
- Cache clearing properly refreshes navigation

---

## [1.5.0] - 2025-12-31 - Widget Visibility with Groups

### Added
- Widget grouping in permissions page
- Filter by group (sales, inventory, etc.)
- Grouped card display
- Localized widget names

### Changed
- RolePermissions page UI overhaul
- Updated translations for group names

---

## [1.4.0] - 2025-12-31 - Resource Access Control

### Added
- `role_resource_access` table
- `RoleResourceAccess` model
- Per-resource CRUD permissions (view, create, edit, delete)
- Resource discovery from filesystem

### Changed
- DashboardConfigurationService split into multiple responsibilities

---

## [1.3.0] - 2025-12-31 - Widget Default Visibility

### Added
- `role_widget_defaults` table
- `RoleWidgetDefault` model
- Per-role widget visibility control
- Widget discovery from filesystem

---

## [1.2.0] - 2025-12-31 - Base Classes

### Added
- `BaseWidget` with `ChecksWidgetVisibility` trait
- `BaseResource` with `ChecksResourceAccess` trait
- `BasePage` with `ChecksPageAccess` trait

### Notes
These are now optional - the Zero-Config V2 system works without them.

---

## [1.1.0] - 2025-12-31 - Discovery System

### Added
- Automatic widget discovery
- Automatic resource discovery
- Class name to display name conversion
- Navigation group detection

---

## [1.0.0] - 2025-12-30 - Initial Implementation

### Added
- Basic dashboard customization
- Manual widget registration
- Simple show/hide functionality

---

## Migration Path

### From 1.x to 2.0

1. Run new migration:
```bash
php artisan migrate
```

2. Clear cache:
```bash
php artisan cache:clear
php artisan optimize:clear
```

3. No code changes needed in your components!

---

## Breaking Changes in 2.0

| Change | Impact | Migration |
|--------|--------|-----------|
| Navigation override | Navigation now built by AdminPanelProvider | No action needed |
| Traits optional | Components work without traits | No action needed |
| New middleware | EnforcePageAccess added | Automatic |

---

## Known Issues

1. **First load after cache clear may be slow**
   - Cause: Discovery runs on every request until cached
   - Workaround: Run `php artisan config:cache` after deployment

2. **Navigation group order**
   - Groups appear in discovery order, not defined order
   - Future fix: Add group ordering configuration

---

## 📅 Last Updated
**Date:** 2026-01-01
