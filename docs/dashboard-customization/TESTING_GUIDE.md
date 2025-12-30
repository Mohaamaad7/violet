# 🧪 دليل اختبار نظام Dashboard Customization

## 📋 المتطلبات قبل البدء

```powershell
# 1. مسح الـ Cache
php artisan optimize:clear

# 2. التأكد من وجود البيانات
php artisan tinker --execute="echo 'Widgets: ' . App\Models\WidgetConfiguration::count() . ', Resources: ' . App\Models\ResourceConfiguration::count() . ', Nav Groups: ' . App\Models\NavigationGroupConfiguration::count();"
```

**النتيجة المتوقعة:** `Widgets: 8, Resources: 24, Nav Groups: 8`

إذا كانت الأرقام 0، نفذ:
```powershell
php artisan dashboard:discover
php artisan dashboard:sync-roles --super-admin-all
```

---

## 🔬 الاختبارات

---

### ✅ اختبار 1: التحقق من الجداول في قاعدة البيانات

**الخطوات:**
```powershell
php artisan tinker
```

ثم نفذ:
```php
// التحقق من الجداول
Schema::hasTable('widget_configurations'); // true
Schema::hasTable('user_widget_preferences'); // true
Schema::hasTable('role_widget_defaults'); // true
Schema::hasTable('resource_configurations'); // true
Schema::hasTable('role_resource_access'); // true
Schema::hasTable('navigation_group_configurations'); // true
Schema::hasTable('role_navigation_groups'); // true
exit
```

**النتيجة المتوقعة:** كل الأوامر ترجع `true`

**❌ إذا فشل:** نفذ `php artisan migrate`

---

### ✅ اختبار 2: التحقق من الـ Models

**الخطوات:**
```powershell
php artisan tinker --execute="App\Models\WidgetConfiguration::first()?->widget_name ?? 'No widgets found'"
```

**النتيجة المتوقعة:** اسم widget مثل `Stats Overview Widget`

**❌ إذا فشل:** نفذ `php artisan dashboard:discover`

---

### ✅ اختبار 3: التحقق من الـ Service

**الخطوات:**
```powershell
php artisan tinker
```

ثم نفذ:
```php
$user = App\Models\User::first();
$service = app(App\Services\DashboardConfigurationService::class);
$widgets = $service->getWidgetsForUser($user);
count($widgets); // يجب أن يكون > 0
exit
```

**النتيجة المتوقعة:** رقم أكبر من 0 (مثلاً 8)

**❌ إذا فشل:** تحقق من أن المستخدم له role وأن الـ sync تم

---

### ✅ اختبار 4: التحقق من الـ Commands

**الخطوات:**
```powershell
# 1. اختبار dashboard:discover
php artisan dashboard:discover --help
# يجب أن يظهر Help message

# 2. اختبار تشغيل الأمر (لن يسجل شيء لأن كل شيء مسجل)
php artisan dashboard:discover
# Expected: 0 new widgets, 0 new resources, 0 new navigation groups
```

**النتيجة المتوقعة:** الأمر يعمل بدون أخطاء

---

### ✅ اختبار 5: الدخول للـ Admin Panel

**الخطوات:**
1. افتح المتصفح
2. اذهب إلى: `http://violet.test/admin`
3. سجل دخول بـ:
   - Email: `mohaamaad7@gmail.com`
   - Password: `18101978`

**النتيجة المتوقعة:**
- ✅ صفحة Dashboard تفتح بدون أخطاء
- ✅ تظهر Widgets على الصفحة الرئيسية

**❌ إذا فشل:** 
- شوف الـ Laravel log في `storage/logs/laravel.log`
- أو شغل `php artisan serve` وشوف الـ console errors

---

### ✅ اختبار 6: التحقق من صفحات الإدارة الجديدة

**الخطوات:**
1. بعد تسجيل الدخول، اذهب إلى:
   - `http://violet.test/admin/widget-configurations`
   - `http://violet.test/admin/resource-configurations`
   - `http://violet.test/admin/navigation-group-configurations`

**النتيجة المتوقعة:**
- ✅ كل صفحة تفتح بدون أخطاء
- ✅ تظهر قائمة بالبيانات

**❌ إذا فشل:** 
- تحقق من أن الـ Resources مسجلة: `php artisan route:list | findstr configurations`

---

### ✅ اختبار 7: تعديل Widget

**الخطوات:**
1. اذهب إلى: `http://violet.test/admin/widget-configurations`
2. اضغط Edit على أي widget
3. غير الـ "Order" من 0 إلى 10
4. اضغط Save

**النتيجة المتوقعة:**
- ✅ الحفظ يتم بنجاح
- ✅ تظهر رسالة نجاح

---

### ✅ اختبار 8: تعديل Navigation Group

**الخطوات:**
1. اذهب إلى: `http://violet.test/admin/navigation-group-configurations`
2. اضغط Edit على أي مجموعة
3. غير الـ "Arabic Label"
4. اضغط Save

**النتيجة المتوقعة:**
- ✅ الحفظ يتم بنجاح

---

### ✅ اختبار 9: إنشاء Navigation Group جديدة

**الخطوات:**
1. اذهب إلى: `http://violet.test/admin/navigation-group-configurations`
2. اضغط "Create" أو "New"
3. أدخل:
   - Group Key: `test_group`
   - Arabic Label: `مجموعة اختبار`
   - English Label: `Test Group`
4. اضغط Create

**النتيجة المتوقعة:**
- ✅ الإنشاء يتم بنجاح
- ✅ المجموعة الجديدة تظهر في القائمة

---

### ✅ اختبار 10: التحقق من Widgets في Dashboard

**الخطوات:**
1. اذهب إلى: `http://violet.test/admin`
2. تحقق من وجود widgets على الصفحة

**النتيجة المتوقعة:**
- ✅ تظهر widgets متعددة (Stats, Charts, etc.)
- ✅ لا توجد أخطاء PHP

---

## 🔧 استكشاف الأخطاء

### مشكلة: "Class not found" errors

**الحل:**
```powershell
composer dump-autoload
php artisan optimize:clear
```

---

### مشكلة: صفحة فارغة أو 500 Error

**الحل:**
```powershell
# 1. شوف الـ Log
Get-Content storage/logs/laravel.log -Tail 50

# 2. أو شغل artisan serve وشوف الأخطاء
php artisan serve
```

---

### مشكلة: Widgets لا تظهر

**الحل:**
```powershell
# 1. تحقق من وجود widgets في الـ DB
php artisan tinker --execute="App\Models\WidgetConfiguration::where('is_active', true)->count()"

# 2. تحقق من Role Defaults
php artisan tinker --execute="App\Models\RoleWidgetDefault::count()"

# 3. أعد الـ Sync
php artisan dashboard:sync-roles --super-admin-all
```

---

### مشكلة: صفحات الإدارة الجديدة لا تظهر في القائمة

**الحل:**
الصفحات ستظهر تحت "النظام" (System) في الـ Sidebar.
إذا لم تظهر:
```powershell
php artisan optimize:clear
```
ثم refresh الصفحة.

---

## ✅ Checklist نهائي

قبل اعتماد النظام، تأكد من:

- [ ] كل الاختبارات (1-10) مرت بنجاح
- [ ] Dashboard يعرض widgets
- [ ] صفحات Widget Configurations تعمل
- [ ] صفحات Resource Configurations تعمل
- [ ] صفحات Navigation Group Configurations تعمل
- [ ] يمكن تعديل الإعدادات وحفظها
- [ ] لا توجد أخطاء في `storage/logs/laravel.log`

---

## 🔐 إدارة الصلاحيات

### كيف يعمل نظام الصلاحيات؟

```
+---------------------------+
|    User Preferences       |  ← أولوية 1 (تفضيلات المستخدم الشخصية)
+---------------------------+
            ↓
+---------------------------+
|     Role Defaults         |  ← أولوية 2 (إعدادات الدور الافتراضية)
+---------------------------+
            ↓
+---------------------------+
|    System Defaults        |  ← أولوية 3 (كل شيء مفعل)
+---------------------------+
```

---

### 📊 الجداول المسؤولة عن الصلاحيات

| الجدول | الوظيفة |
|--------|---------|
| `role_widget_defaults` | أي widgets يشوفها كل role |
| `role_resource_access` | أي resources يقدر يوصلها كل role |
| `role_navigation_groups` | أي navigation groups تظهر لكل role |

---

### 🎯 تحديد صلاحيات الـ Widgets لـ Role معين

#### الطريقة 1: عبر الـ Tinker (سريعة)

```powershell
php artisan tinker
```

```php
// 1. جلب الـ Role
$role = App\Models\Role::where('name', 'sales')->first();

// 2. جلب Widget معين
$widget = App\Models\WidgetConfiguration::where('widget_name', 'like', '%Stock%')->first();

// 3. تعديل الصلاحية (إخفاء widget من role)
App\Models\RoleWidgetDefault::updateOrCreate(
    ['role_id' => $role->id, 'widget_configuration_id' => $widget->id],
    ['is_visible' => false] // false = مخفي, true = ظاهر
);

// 4. مسح الـ Cache
Cache::flush();

exit
```

#### الطريقة 2: عبر SQL مباشرة (للمتقدمين)

```sql
-- إخفاء widget من role
UPDATE role_widget_defaults 
SET is_visible = 0 
WHERE role_id = (SELECT id FROM roles WHERE name = 'sales')
AND widget_configuration_id = (SELECT id FROM widget_configurations WHERE widget_name LIKE '%Stock%');
```

---

### 🎯 تحديد صلاحيات الـ Resources لـ Role معين

#### عبر الـ Tinker:

```powershell
php artisan tinker
```

```php
// 1. جلب الـ Role
$role = App\Models\Role::where('name', 'sales')->first();

// 2. جلب Resource معين
$resource = App\Models\ResourceConfiguration::where('resource_name', 'like', '%Product%')->first();

// 3. تعديل الصلاحيات
App\Models\RoleResourceAccess::updateOrCreate(
    ['role_id' => $role->id, 'resource_configuration_id' => $resource->id],
    [
        'can_view' => true,      // يقدر يشوف
        'can_create' => false,   // لا يقدر ينشئ
        'can_edit' => false,     // لا يقدر يعدل
        'can_delete' => false,   // لا يقدر يحذف
        'is_visible_in_navigation' => true // يظهر في القائمة
    ]
);

Cache::flush();
exit
```

---

### 🎯 تحديد Navigation Groups المتاحة لـ Role

```powershell
php artisan tinker
```

```php
// 1. جلب الـ Role
$role = App\Models\Role::where('name', 'sales')->first();

// 2. جلب Navigation Group
$navGroup = App\Models\NavigationGroupConfiguration::where('group_key', 'inventory')->first();

// 3. إخفاء المجموعة من هذا الـ Role
App\Models\RoleNavigationGroup::updateOrCreate(
    ['role_id' => $role->id, 'navigation_group_id' => $navGroup->id],
    ['is_visible' => false]
);

Cache::flush();
exit
```

---

### 📋 أمثلة عملية

#### مثال 1: موظف المبيعات يشوف فقط المبيعات والعملاء

```powershell
php artisan tinker
```

```php
$role = App\Models\Role::where('name', 'sales')->first();

// إخفاء مجموعات المخزون والإعدادات والنظام
$hiddenGroups = ['inventory', 'settings', 'system', 'geography'];

foreach ($hiddenGroups as $groupKey) {
    $group = App\Models\NavigationGroupConfiguration::where('group_key', $groupKey)->first();
    if ($group) {
        App\Models\RoleNavigationGroup::updateOrCreate(
            ['role_id' => $role->id, 'navigation_group_id' => $group->id],
            ['is_visible' => false]
        );
    }
}

Cache::flush();
echo "Done!";
exit
```

#### مثال 2: إظهار كل شيء لـ Manager

```powershell
php artisan tinker
```

```php
$role = App\Models\Role::where('name', 'manager')->first();

// إظهار كل الـ Navigation Groups
App\Models\RoleNavigationGroup::where('role_id', $role->id)
    ->update(['is_visible' => true]);

// إظهار كل الـ Widgets
App\Models\RoleWidgetDefault::where('role_id', $role->id)
    ->update(['is_visible' => true]);

// إعطاء كل الصلاحيات على الـ Resources
App\Models\RoleResourceAccess::where('role_id', $role->id)
    ->update([
        'can_view' => true,
        'can_create' => true,
        'can_edit' => true,
        'can_delete' => true,
        'is_visible_in_navigation' => true
    ]);

Cache::flush();
echo "Manager now has full access!";
exit
```

---

### 🔄 إعادة تعيين صلاحيات Role للإعدادات الافتراضية

```powershell
php artisan dashboard:sync-roles --role=sales
```

أو لكل الـ Roles:

```powershell
php artisan dashboard:sync-roles
```

أو مع إعطاء Super Admin كل الصلاحيات:

```powershell
php artisan dashboard:sync-roles --super-admin-all
```

---

### 🔍 عرض صلاحيات Role معين

```powershell
php artisan tinker
```

```php
$role = App\Models\Role::where('name', 'sales')->first();

// Widgets المتاحة
echo "=== Widgets ===\n";
$role->widgetDefaults()->where('is_visible', true)->with('widgetConfiguration')->get()
    ->each(fn($wd) => echo "✅ " . $wd->widgetConfiguration->widget_name . "\n");

// Navigation Groups المتاحة
echo "\n=== Navigation Groups ===\n";
$role->roleNavigationGroups()->where('is_visible', true)->with('navigationGroup')->get()
    ->each(fn($rng) => echo "✅ " . $rng->navigationGroup->group_key . "\n");

exit
```

---

### ⚠️ ملاحظات مهمة

1. **دائماً امسح الـ Cache بعد التعديل:**
   ```php
   Cache::flush();
   ```
   أو:
   ```powershell
   php artisan cache:clear
   ```

2. **User Preferences لها الأولوية:**
   - إذا المستخدم عنده تفضيلات شخصية، ستتجاوز إعدادات الـ Role
   - لإعادة تعيين تفضيلات مستخدم:
   ```powershell
   php artisan dashboard:reset-user {email}
   ```

3. **التغييرات لا تحتاج restart للـ server**
   - بمجرد مسح الـ Cache، التغييرات تأخذ المفعول مباشرة

---

## 📞 الدعم

إذا وجدت مشكلة لم تُحل:
1. انسخ رسالة الخطأ
2. انسخ آخر 50 سطر من الـ log:
   ```powershell
   Get-Content storage/logs/laravel.log -Tail 50
   ```
3. شاركها معي

---

**تاريخ الإنشاء:** 30 ديسمبر 2025
