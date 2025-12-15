# توثيق ميزة أيقونة اللوجو الديناميكية (Dynamic Logo Icon)

**تاريخ التحديث**: 2025-12-15
**المهمة**: استبدال اللوجو الثابت في الهيدر بلوجو ديناميكي يمكن تغييره من لوحة تحكم الإدارة.

---

## 1. الأهداف
1.  إزالة اللوجو الثابت (SVG/CSS) من `header.blade.php`.
2.  إضافة إعداد جديد `logo_icon` في قاعدة البيانات.
3.  تمكين رفع صورة اللوجو من لوحة Filament.
4.  حفظ الصور المرفوعة في مسار `public/images/logos` لسهولة الوصول.
5.  دعم صورة افتراضية (`images/logo.png`) في حال عدم رفع صورة.

---

## 2. المشاكل التي تمت مواجهتها والحلول

### أ. مشكلة `foreach() argument must be of type array|object`
*   **الخطأ**: مكون `FileUpload` في Filament يتوقع مصفوفة (Array)، بينما القيمة في قاعدة البيانات (String).
*   **المحاولات الأولية**: استخدام `cast` أو `accessors` في الـ Model، ولكن هذا تسبب في مشاكل تحويل البيانات (Array to String conversion) في الواجهة الأمامية.
*   **الحل النهائي**:
    *   إلغاء أي تحويل تلقائي للنوع (Casting) في `Setting` model للنوع `image`.
    *   التعامل مع التحويل يدويًا في طبقة الـ Resource Pages (`EditSetting`).

### ب. مشكلة تداخل البيانات وتحديث الحالة (State Interference)
*   **الخطأ**: وجود حقول متعددة بنفس الاسم `value` (Textarea, Toggle, FileUpload) في نفس النموذج تسبب في تداخل البيانات أثناء تحديثات Livewire.
*   **الحل**:
    *   استخدام اسم حقل افتراضي مختلف للرفع: `image_value`.
    *   نقل البيانات يدويًا من `image_value` إلى `value` عند الحفظ.

### ج. مسار الحفظ (Storage vs Public)
*   **التحدي**: Filament يحفظ افتراضيًا في `storage/app/public` مما يتطلب استخدام `asset('storage/...')`، بينما الصورة الافتراضية في `public/images` وتستخدم `asset(...)`. هذا خلق تعقيدًا في الـ Frontend.
*   **الحل**:
    *   إنشاء Disk جديد في `config/filesystems.php` باسم `public_dir` يشير مباشرة إلى مجلد `public`.
    *   ضبط `FileUpload` ليحفظ في `images/logos` باستخدام هذا الـ Disk.
    *   النتيجة: كل المسارات (الافتراضية والمرفوعة) أصبحت نسبية لمجلد `public`، مما وحّد طريقة العرض بـ `asset()`.

---

## 3. التنفيذ التقني (Implementation Details)

### أ. قاعدة البيانات (Migration)
تم إضافة إعداد `logo_icon` بقيمة افتراضية `images/logo.png`.
```php
DB::table('settings')->insert([
    'key' => 'logo_icon',
    'value' => 'images/logo.png', // المسار الافتراضي
    'type' => 'image',
    'group' => 'general',
]);
```

### ب. إعدادات النظام (Config)
تم إضافة قرص تخزين جديد للوصول المباشر لمجلد `public` لتنظيم الملفات.
**الملف**: `config/filesystems.php`
```php
'public_dir' => [
    'driver' => 'local',
    'root' => public_path(), // C:\server\www\violet\public
    'url' => env('APP_URL'),
    'visibility' => 'public',
],
```

### ج. لوحة التحكم (Filament Resource)

**1. النموذج (`SettingForm.php`)**
تم استخدام حقل منفصل `image_value` لتجنب التداخل.
```php
FileUpload::make('image_value')
    ->disk('public_dir')        // الحفظ في public مباشرة
    ->directory('images/logos') // المجلد الفرعي
    ->default([])
    ->visible(fn ($get) => $get('type') === 'image')
```

**2. معالجة البيانات (`EditSetting.php`)**
تم استخدام Hooks للتحكم في تدفق البيانات وضمان سلامتها:
*   `mutateFormDataBeforeFill`: تعبئة `image_value` من الـ `value` الموجود في القاعدة وتحويله لمصفوفة.
*   `mutateFormDataBeforeSave`: 
    1. استخراج المسار الجديد من `image_value`.
    2. حذف الصورة القديمة (إذا كانت تبدأ بـ `images/logos/`) لتنظيف السيرفر وعدم تراكم الملفات غير المستخدمة.
    3. تحديث حقل `value` بالمسار الجديد.

### د. الواجهة الأمامية (Frontend)
تم تبسيط الكود بفضل توحيد المسارات.
**الملف**: `resources/views/components/store/header.blade.php`
```blade
@php $logoPath = setting('logo_icon'); @endphp
@if($logoPath && $logoPath !== '')
    <img src="{{ asset($logoPath) }}" ...>
@else
    {{-- Placeholder --}}
@endif
```

---

## 4. الملفات المعدلة
1.  `database/migrations/2025_12_15_130500_add_logo_icon_setting.php`
2.  `config/filesystems.php`
3.  `app/Models/Setting.php`
4.  `app/Filament/Resources/Settings/Schemas/SettingForm.php`
5.  `app/Filament/Resources/Settings/Pages/EditSetting.php`
6.  `app/Filament/Resources/Settings/Pages/CreateSetting.php`
7.  `resources/views/components/store/header.blade.php`
