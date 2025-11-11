# 🔧 دليل حل المشاكل - Violet E-Commerce

**آخر تحديث:** 10 نوفمبر 2025

هذا الملف يوثق جميع المشاكل التي واجهناها خلال التطوير والحلول المُجربة.

---

## 📋 فهرس المشاكل

1. [Livewire FileUpload: Error 500 - Path cannot be empty](#1-livewire-fileupload-error-500)
2. [Filament v4: Namespace Confusion](#2-filament-v4-namespace-confusion)
3. [Column Name Mismatch](#3-column-name-mismatch)
4. [🚨 CRITICAL: Filament Actions Namespace Error](#4-critical-filament-actions-namespace-error)

---

## 1. Livewire FileUpload: Error 500 - Path cannot be empty

### 🐛 الأعراض

```
Error during upload
The data.images.xxx-xxx-xxx failed to upload.

Laravel Log:
ValueError: Path cannot be empty
#0 FilesystemAdapter.php(466): fopen('', 'r')
```

### 🔍 التشخيص

**المشكلة الحقيقية:** إعدادات PHP على السيرفر المحلي (Laragon/XAMPP)

```php
// في php.ini
;upload_tmp_dir =  ← مُعطل (commented)
```

**ماذا يحدث:**
1. المتصفح يرفع الملف عبر Livewire
2. PHP يحاول حفظ الملف المؤقت
3. لكن `upload_tmp_dir` غير مُحدد أو معطل
4. `$file->getPath()` يُرجع string فارغ `''`
5. `fopen('', 'r')` يفشل بـ ValueError

### ✅ الحل

**الخطوة 1:** تعديل `php.ini`

```ini
# قبل
;upload_tmp_dir =

# بعد
upload_tmp_dir = C:\server\tmp
```

**الخطوة 2:** إنشاء المجلد إذا لم يكن موجوداً

```powershell
New-Item -ItemType Directory -Path C:\server\tmp -Force
```

**الخطوة 3:** إعادة تشغيل السيرفر (Laragon/Apache/Nginx)

### 🎯 التحقق

```powershell
# تحقق من الإعدادات
php -i | Select-String "upload_tmp_dir"

# يجب أن يظهر:
upload_tmp_dir => C:\server\tmp => C:\server\tmp
```

### 📝 ملاحظات مهمة

- ⚠️ **`.user.ini` لا يعمل مع `php artisan serve`**
- ✅ التعديل يجب أن يكون في `php.ini` الرئيسي
- 🔄 إعادة التشغيل ضرورية
- 📁 تأكد من صلاحيات الكتابة على المجلد

### 🚫 محاولات فاشلة

❌ تعديل `config/livewire.php` فقط
❌ تعديل `config/filesystems.php` فقط
❌ إنشاء `.user.ini` في public/
❌ استخدام `php -c custom.ini artisan serve`

**السبب:** المشكلة في **بيئة PHP** نفسها، ليست في Laravel/Livewire!

---

## 2. Filament v4: Namespace Confusion

### 🐛 المشكلة

```php
Fatal error: Type of ProductResource::$navigationGroup must be UnitEnum|string|null
```

### ✅ الحل

```php
// ❌ خطأ
protected static ?string $navigationGroup = 'الكتالوج';

// ✅ صحيح
use UnitEnum;
protected static UnitEnum|string|null $navigationGroup = 'الكتالوج';
```

### 📝 ملاحظات Filament v4

**Actions Namespace:**
```php
// ✅ صحيح في v4
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;

// ❌ خطأ (كان في v3)
use Filament\Tables\Actions\EditAction;
```

**Form vs Schema:**
```php
// ✅ صحيح في v4
public static function form(Schema $schema): Schema

// ❌ خطأ (كان في v3)
public static function form(Form $form): Form
```

**Components:**
```php
// ✅ Layout components
use Filament\Schemas\Components\Section;

// ✅ Form fields
use Filament\Forms\Components\TextInput;
```

---

## 3. Column Name Mismatch

### 🐛 المشكلة

```php
SQLSTATE[42S22]: Column not found: 'image'
```

### 🔍 السبب

Migration يستخدم `image_path` لكن الكود يستخدم `image`:

```php
// Migration
$table->string('image_path');

// Model (خطأ)
protected $fillable = ['image'];
```

### ✅ الحل

**1. تحديث Model:**
```php
protected $fillable = ['image_path', 'is_primary', 'order'];
```

**2. تحديث Service:**
```php
'image_path' => $imageData['image'] ?? $imageData['image_path']
```

**3. تحديث Tests:**
```php
$image->image_path // ليس $image->image
```

### 🎯 الدرس المستفاد

✅ **دائماً راجع Migration قبل كتابة Model**
✅ **استخدم naming convention واحد**
✅ **اختبر العلاقات مباشرة بعد إنشائها**

---

## 4. 🚨 CRITICAL: Filament Actions Namespace Error

### 🐛 المشكلة

```
Class "Filament\Tables\Actions\Action" not found
في: app\Filament\Widgets\RecentOrdersWidget.php:87
```

### ❌ الخطأ الفادح المرتكب

**تم التخمين بدلاً من الرجوع للتوثيق الرسمي!**

```php
// ❌ خطأ فادح - تم استخدامه بالتخمين
use Filament\Tables\Actions\Action;

// ✅ الصحيح حسب توثيق Filament v4 الرسمي
use Filament\Actions\Action;
```

### 🔍 التشخيص الصحيح

**المشكلة الحقيقية:** في Filament v4، تم تغيير namespace الـ Actions بشكل جذري:

**Filament v3 (القديم):**
```php
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
```

**Filament v4 (الحالي):**
```php
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
```

### ✅ الحل الصحيح

**الخطوة 1:** استخدام الـ namespace الصحيح

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Actions\Action;  // ← الصحيح
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    // ...
    
    protected function table(Table $table): Table
    {
        return $table
            ->query(/* ... */)
            ->columns(/* ... */)
            ->actions([
                Action::make('view')  // يعمل بشكل صحيح
                    ->label('عرض')
                    ->url(fn (Order $record) => route('filament.admin.resources.orders.view', ['record' => $record]))
            ])
            ->headerActions([
                Action::make('viewAll')  // يعمل بشكل صحيح
                    ->label('عرض جميع الطلبات')
                    ->url(route('filament.admin.resources.orders.index'))
            ]);
    }
}
```

**الخطوة 2:** مسح الكاش

```powershell
php artisan optimize:clear
composer dump-autoload
```

### 🚨 تحذير صارم - يُمنع التخمين منعاً نهائياً

**القاعدة الذهبية:**

> **لا تخمن أبداً طالما لدينا توثيق رسمي يمكن الرجوع إليه!**

**الإجراء الصحيح:**

1. ✅ **اقرأ التوثيق الرسمي أولاً:** [Filament v4 Docs](https://filamentphp.com/docs/4.x)
2. ✅ **ابحث في Upgrade Guide:** [v3 → v4 Breaking Changes](https://filamentphp.com/docs/4.x/upgrade-guide)
3. ✅ **راجع أمثلة الكود الرسمية:** في مستودع Filament على GitHub
4. ✅ **استخدم IDE autocomplete:** للتحقق من الـ namespaces المتاحة
5. ❌ **لا تفترض** أن الـ namespace سيكون منطقياً حسب السياق

### 📚 مراجع Filament v4 المهمة

**Actions في Filament v4:**

```php
// ✅ Global Actions (تُستخدم في Resources, Widgets, Pages)
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;

// ✅ Table-specific configurations
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;

// ✅ Form components
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

// ✅ Schema (Layout)
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
```

**في TableWidget:**
```php
class MyWidget extends TableWidget
{
    protected function table(Table $table): Table
    {
        return $table
            ->actions([
                // استخدم Filament\Actions\Action
                Action::make('custom')->action(fn() => /* ... */)
            ])
            ->headerActions([
                // نفس الـ namespace
                Action::make('create')->url(/* ... */)
            ]);
    }
}
```

**في Resource:**
```php
class MyResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->actions([
                // نفس الـ namespace في كل مكان!
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
```

### 💡 الدرس المستفاد

1. **التخمين = خطأ فادح** في بيئة production
2. **التوثيق الرسمي هو المرجع الأول والأخير**
3. **Breaking changes في Major versions** تتطلب مراجعة شاملة
4. **الاختبار المباشر** بعد كل تعديل ضروري
5. **المراجعة من المستخدم** كشفت الخطأ - الاختبار اليدوي لا يُعوض

### 🎯 إجراءات وقائية مستقبلية

✅ **قبل استخدام أي Class في Filament:**
```powershell
# 1. ابحث في التوثيق
# 2. تحقق من الـ IDE autocomplete
# 3. راجع Upgrade Guide
# 4. اختبر في بيئة معزولة
```

✅ **عند الترقية لـ Major version:**
```powershell
# 1. اقرأ UPGRADE.md كاملاً
# 2. ابحث عن Breaking Changes
# 3. راجع CHANGELOG
# 4. اختبر كل Feature متأثر
```

✅ **عند الشك:**
- **لا تخمن** - ارجع للتوثيق
- **لا تفترض** - تحقق من الكود المصدري
- **لا تجرب** - اقرأ أولاً ثم نفذ

### 📝 ملاحظات الإصدار

- **Filament v4.2.0:** Actions تم نقلها من `Filament\Tables\Actions` إلى `Filament\Actions`
- **السبب:** توحيد Actions API عبر كل مكونات Filament
- **التأثير:** Breaking change يتطلب تحديث جميع الـ imports

---

## 🛠️ أدوات التشخيص السريع

### فحص PHP Settings

```powershell
# عرض جميع إعدادات PHP
php -i

# فحص upload settings محدد
php -i | Select-String "upload"

# فحص memory & size limits
php -i | Select-String "memory_limit|upload_max_filesize|post_max_size"
```

### فحص Laravel Logs

```powershell
# آخر 50 سطر
Get-Content storage\logs\laravel.log -Tail 50

# بحث عن خطأ معين
Select-String -Path storage\logs\laravel.log -Pattern "Error|Exception" -Context 2,5
```

### مسح Cache

```powershell
# مسح كل شيء
php artisan optimize:clear

# مسح محدد
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### فحص Disk Permissions

```powershell
# Windows
icacls storage\app\public

# التأكد من المجلدات
Test-Path storage\app\public\products
Test-Path storage\app\livewire-tmp
```

---

## 📚 مصادر مفيدة

### Filament v4 Documentation
- [Official Docs](https://filamentphp.com/docs/4.x)
- [Upgrade Guide v3 → v4](https://filamentphp.com/docs/4.x/upgrade-guide)

### Livewire v3 Documentation
- [File Uploads](https://livewire.laravel.com/docs/uploads)
- [Troubleshooting](https://livewire.laravel.com/docs/troubleshooting)

### Laravel 11 Documentation
- [File Storage](https://laravel.com/docs/11.x/filesystem)
- [Validation](https://laravel.com/docs/11.x/validation)

---

## 🔄 عملية التشخيص الموصى بها

عند مواجهة مشكلة جديدة، اتبع هذه الخطوات:

### 1. جمع المعلومات
```powershell
# Laravel logs
Get-Content storage\logs\laravel.log -Tail 100

# Browser console
# افتح Developer Tools → Console → Network

# PHP info
php -i | Select-String "setting_name"
```

### 2. عزل المشكلة

- [ ] هل المشكلة في البيئة (Environment) أم الكود؟
- [ ] هل تحدث في Development فقط أم Production أيضاً؟
- [ ] هل بدأت بعد تغيير معين؟

### 3. اختبار الحلول

1. ابدأ بالحلول الأبسط (Clear cache, restart server)
2. تحقق من الإعدادات الأساسية (php.ini, .env)
3. راجع الكود المتعلق
4. ابحث في Issues على GitHub
5. اسأل في المنتديات (Laravel/Filament Discord)

### 4. التوثيق

- ✅ وثق المشكلة والحل في هذا الملف
- ✅ أضف unit test إذا كانت المشكلة في الكود
- ✅ حدّث الـ README إذا كان setup requirement جديد

---

## 💡 نصائح عامة

### Development Environment

✅ **استخدم نفس الإعدادات في Dev و Production**
- PHP version
- Extensions
- php.ini settings

✅ **استخدم Docker أو Sail للاتساق**
```bash
sail up -d
sail artisan serve
```

✅ **احتفظ بنسخة من php.ini المُعدل**

### Testing

✅ **اكتب tests للـ critical features**
- File uploads
- Database transactions
- API endpoints

✅ **اختبر على بيئات مختلفة**
- Windows / Linux / Mac
- Apache / Nginx
- PHP versions

### Code Quality

✅ **اتبع Laravel best practices**
- Service Layer Pattern
- Form Requests
- Resource Controllers

✅ **استخدم Type Hints دائماً**
```php
public function upload(UploadedFile $file): string
```

✅ **Document المشاكل الغريبة في الكود**
```php
// FIXME: Livewire requires 'local' disk for temp uploads
// even though final storage is 'public'
'disk' => 'local',
```

---

**تم إنشاؤه بواسطة:** Violet Development Team  
**المشروع:** Violet E-Commerce Platform  
**Laravel Version:** 12.37.0  
**Filament Version:** 4.2.0
