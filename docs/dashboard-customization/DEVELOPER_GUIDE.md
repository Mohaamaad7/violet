# Dashboard Customization - Developer Guide

## 🎉 Zero-Config Approach (نظام الإعداد التلقائي)

This system uses a **Zero-Config approach**:
- **Everything is visible by default** ✅
- **No registration commands needed** ✅
- **No special base classes required** ✅
- **Database stores exceptions only** (what's hidden, not what's visible)

---

## 🚀 For Developers: Creating New Widgets & Resources

### Creating a Widget

```bash
php artisan make:filament-widget MyNewWidget
```

**That's it!** The widget will:
1. ✅ Appear automatically on the dashboard
2. ✅ Be visible to all roles by default
3. ✅ Show up in the Role Permissions page for admins to manage

### Creating a Resource

```bash
php artisan make:filament-resource MyModel
```

**That's it!** The resource will:
1. ✅ Appear automatically in the navigation
2. ✅ Have full access for all roles by default
3. ✅ Show up in the Role Permissions page for admins to manage

---

## 🔧 How It Works (Technical)

### Runtime Discovery

The `DashboardConfigurationService` scans:
- `app/Filament/Widgets/` → Discovers all widget classes
- `app/Filament/Resources/` → Discovers all resource classes

This happens at **runtime** - no database registration needed!

### Database Only Stores Exceptions

| Table | Purpose |
|-------|---------|
| `role_widget_defaults` | Stores only **HIDDEN** widgets |
| `role_resource_access` | Stores only **RESTRICTED** resources |

If a widget/resource is NOT in these tables → it's **visible/accessible by default**.

### Traits (Optional)

You can optionally use these traits for explicit permission checks:
- `ChecksWidgetVisibility` - For widgets
- `ChecksResourceAccess` - For resources

But they're **not required** - the system works without them too!

---

## 📋 Admin: Managing Permissions

1. Go to **System → Role Permissions**
2. Select a role from the dropdown
3. Toggle widgets/resources on/off
4. Changes take effect immediately

### What Gets Stored

| Action | Database Effect |
|--------|-----------------|
| Hide a widget | Creates record with `is_visible = false` |
| Show a widget | Deletes the record (back to default) |
| Restrict resource | Creates record with specific permissions |
| Grant full access | Deletes the record (back to default) |

---

## 🎯 Quick Reference

### For Developers

```
✅ Create widget/resource normally
✅ Refresh page - it appears automatically
✅ No artisan commands needed
✅ No base classes needed
✅ No database registration needed
```

### For Admins

```
✅ All widgets visible by default
✅ All resources accessible by default
✅ Use Role Permissions page to hide/restrict
✅ Changes are instant (no deployment needed)
```

---

## 🔄 Migration from Old System

If you have old data in `widget_configurations` or `resource_configurations`, they are ignored. The new system discovers from code directly.

To clear old cache:
```bash
php artisan cache:clear
```

---

## 📁 Key Files

| File | Purpose |
|------|---------|
| `app/Services/DashboardConfigurationService.php` | Core discovery & permission logic |
| `app/Filament/Pages/RolePermissions.php` | Admin UI for managing permissions |
| `app/Models/RoleWidgetDefault.php` | Hidden widget overrides |
| `app/Models/RoleResourceAccess.php` | Resource permission overrides |
