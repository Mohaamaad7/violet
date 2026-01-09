# نظام إعادة التعيين والنسخ الاحتياطي

> تاريخ التنفيذ: 2026-01-09  
> الإصدار: 1.0

## نظرة عامة

تم تنفيذ نظام متكامل للسوبر أدمن يتيح:
1. **إعادة تعيين النظام** - حذف انتقائي للبيانات قبل الانتقال للإنتاج
2. **النسخ الاحتياطي** - إنشاء وإدارة النسخ الاحتياطية لقاعدة البيانات والملفات

---

## الملفات المُنشأة

### Backend
| الملف | الوصف |
|-------|-------|
| `app/Services/SystemResetService.php` | خدمة إعادة التعيين - المنطق الأساسي |
| `app/Filament/Pages/SystemReset.php` | صفحة Filament لإعادة التعيين |
| `app/Filament/Pages/BackupManager.php` | صفحة Filament لإدارة النسخ الاحتياطية |
| `config/backup.php` | إعدادات حزمة spatie/laravel-backup |

### Frontend (Blade Views)
| الملف | الوصف |
|-------|-------|
| `resources/views/filament/pages/system-reset.blade.php` | واجهة إعادة التعيين |
| `resources/views/filament/pages/backup-manager.blade.php` | واجهة النسخ الاحتياطي |

### الترجمات
- `lang/ar/admin.php` - مفاتيح `system_reset.*` و `backup.*`

---

## الحزم المستخدمة

### spatie/laravel-backup
```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

> ⚠️ **ملاحظة**: حزمة `biscolab/laravel-recaptcha` غير متوافقة مع Laravel 12، لذا تم تأجيل reCAPTCHA.

---

## صفحة إعادة تعيين النظام

### فئات البيانات القابلة للحذف

| الفئة | الجداول | الملفات |
|-------|---------|---------|
| العملاء | wishlists, cart_items, carts, shipping_addresses, customers | - |
| الطلبات | order_status_history, return_items, order_returns, order_items, orders | - |
| المنتجات | product_images, product_reviews, product_variants, products, categories | storage/app/public/products |
| المخزون | stock_count_items, stock_counts, stock_movements, batches, warehouses | - |
| المالية | payments, commission_payouts, influencer_commissions, code_usages | - |
| المؤثرين | influencer_applications, influencers, discount_codes | - |
| المحتوى | blog_posts, pages, banners, sliders, help_entries | storage/app/public/banners, sliders |
| الموظفين | users (باستثناء المستخدم الحالي) | - |
| سجلات النشاط | activity_log | - |

### القوالب الجاهزة (Presets)

1. **حذف كل البيانات (Factory Reset Lite)** - كل شيء ما عدا الإعدادات والموظفين
2. **وضع المطور** - إبقاء المنتجات فقط
3. **الاحتفاظ بالمنتجات فقط** - حذف كل شيء ما عدا المنتجات

### طبقات الأمان

1. ✅ فحص صلاحية Super Admin (`canAccess()`)
2. ✅ جملة تأكيد: "أنا أوافق على حذف البيانات"
3. ✅ تأكيد كلمة المرور
4. ✅ نسخ احتياطي تلقائي قبل الحذف
5. ✅ Activity Logging لكل عملية

---

## صفحة النسخ الاحتياطي

### الميزات

- ✅ إنشاء نسخة احتياطية (DB فقط / ملفات فقط / كاملة)
- ✅ عرض قائمة النسخ مع النوع والحجم والتاريخ
- ✅ تحميل النسخ
- ✅ حذف نسخة واحدة
- ✅ حذف جميع النسخ
- ✅ تنظيف حسب سياسة الاحتفاظ (أقدم من 7 أيام)

### اكتشاف نوع النسخة

يتم فحص محتويات ملف ZIP لتحديد:
- 🟢 **كاملة** - تحتوي على DB + ملفات
- 🔵 **قاعدة بيانات فقط** - تحتوي على .sql فقط
- 🟡 **ملفات فقط** - تحتوي على صور/ملفات فقط

### محتوى النسخة الكاملة

```
include:
  - base_path()           # كود المشروع كاملاً
  - storage_path('app/public')  # ملفات المستخدمين

exclude:
  - vendor/
  - node_modules/
  - .git/
  - storage/logs/
  - storage/app/backup-temp/
```

---

## الأخطاء والحلول

### 1. خطأ: $navigationIcon type error
```
Type of $navigationIcon must be BackedEnum|string|null
```
**الحل**: استخدام method بدلاً من property:
```php
public static function getNavigationIcon(): string|null
{
    return 'heroicon-o-arrow-path';
}
```

### 2. خطأ: $view cannot be static
```
Cannot redeclare non static Page::$view as static
```
**الحل**: إزالة `static` من التعريف:
```php
protected string $view = 'filament.pages.system-reset';
```

### 3. خطأ: Section class not found
```
Use of unknown class: Filament\Forms\Components\Section
```
**الحل**: استخدام namespace الصحيح لـ Filament 4:
```php
use Filament\Schemas\Components\Section;
```

### 4. مشكلة: Checkboxes لا تُحدَّث
**السبب**: في Livewire 3، `wire:model` لا يُحدّث فوراً
**الحل**: استخدام `wire:model.live`

### 5. مشكلة: الصور لا تُضاف للنسخة الاحتياطية
**السبب**: `follow_links = false` لا يتبع symlinks
**الحل**: 
```php
'follow_links' => true,
'include' => [
    base_path(),
    storage_path('app/public'),
],
```

---

## أوامر النشر

### على السيرفر (بعد merge أو pull)
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## الروابط

| الصفحة | المسار |
|--------|--------|
| إعادة تعيين النظام | `/admin/system-reset` |
| النسخ الاحتياطي | `/admin/backup-manager` |

---

## التحسينات المستقبلية

- [ ] إضافة reCAPTCHA v3 (يدوياً)
- [ ] Progress Bar لعمليات الحذف الكبيرة
- [ ] جدولة تلقائية للنسخ الاحتياطية
- [ ] استعادة النسخ الاحتياطية من الواجهة
- [ ] إرسال إشعارات عند اكتمال/فشل النسخ
