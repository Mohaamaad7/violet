# 🚀 دليل الرفع للـ Production - Violet E-commerce

**التاريخ:** 13 ديسمبر 2025  
**الإصدار:** 2.0 (محدث مع نظام المرتجعات)

---

## ⚠️ قواعد مهمة

### البيانات التي يجب الحفاظ عليها 100%:
- ✅ **المستخدمين (users)** - Admin/Staff
- ✅ **المنتجات (products)** - مع الصور والـ variants
- ✅ **التصنيفات (categories)**
- ✅ **البراندات (brands)**
- ✅ **الإعدادات (settings)**

### البيانات التي يمكن إعادة تعيينها:
- ⚪ الطلبات (orders)
- ⚪ العملاء (customers) 
- ⚪ السلات (carts)
- ⚪ المرتجعات (returns)
- ⚪ المراجعات (reviews)
- ⚪ قوائم الأمنيات (wishlists)

---

## 🎯 الخطوات السريعة (Fresh Production)

### 1️⃣ قبل الرفع - Backup الأساسيات

```powershell
# 1. Backup جدول users (المستخدمين)
mysqldump -u root -p violet users > backup_users_$(date +%Y%m%d).sql

# 2. Backup جدول products (المنتجات)
mysqldump -u root -p violet products product_images product_variants > backup_products_$(date +%Y%m%d).sql

# 3. Backup الإعدادات
mysqldump -u root -p violet settings categories brands > backup_settings_$(date +%Y%m%d).sql

# 4. Backup كامل للأمان
mysqldump -u root -p violet > backup_full_$(date +%Y%m%d).sql
```

### 2️⃣ التحقق من الـ Migrations

```powershell
# عرض الـ migrations المعلقة
php artisan migrate:status
```

### 3️⃣ وضع الموقع في Maintenance

```powershell
php artisan down --message="جاري تحديث النظام..." --retry=60
```

### 4️⃣ رفع الكود الجديد

```powershell
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
```

### 5️⃣ تشغيل الـ Migrations

```powershell
# تشغيل الـ migrations بشكل آمن
php artisan migrate --force
```

### 6️⃣ تشغيل الـ Seeders الضرورية

```powershell
# إعدادات نظام المرتجعات
php artisan db:seed --class=ReturnPolicySettingsSeeder --force
```

### 7️⃣ مسح الـ Cache

```powershell
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8️⃣ إعادة تشغيل الموقع

```powershell
php artisan up
```

---

## 📋 قائمة التحقق بعد الرفع

### الأساسيات:
- [ ] Admin يقدر يسجل دخول
- [ ] المنتجات ظاهرة في Dashboard
- [ ] صور المنتجات شغالة
- [ ] التصنيفات موجودة

### نظام المرتجعات:
- [ ] صفحة Returns تفتح بدون أخطاء: `/admin/order-returns`
- [ ] إعدادات المرتجعات موجودة في Settings
- [ ] زر "رفض الاستلام" يظهر على الطلبات قيد الشحن
- [ ] زر "طلب مرتجع" يظهر على الطلبات المسلمة

### الإعدادات المهمة للمرتجعات:
```powershell
# التحقق من الإعدادات
php artisan tinker
>>> setting('return_window_days');  # المتوقع: 5 أو 14
>>> setting('auto_approve_rejections');  # المتوقع: true أو false
```

---

## 🔄 Rollback (خطة الرجوع)

إذا حصلت مشكلة:

```powershell
# 1. Maintenance mode
php artisan down

# 2. استرجاع الـ Backup
mysql -u root -p violet < backup_full_YYYYMMDD.sql

# 3. رجوع الكود
git checkout <previous-commit>
composer install

# 4. مسح Cache
php artisan optimize:clear

# 5. إعادة التشغيل
php artisan up
```

---

## � الإعدادات المهمة للـ .env

```env
# Production Settings
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=violet_production

# Cache
CACHE_DRIVER=redis  # أو file
SESSION_DRIVER=redis  # أو database

# Queue (للإشعارات)
QUEUE_CONNECTION=database
```

---

## ⚡ نصائح مهمة

1. **نفذ في وقت قليل الزحمة** (مثلاً 3-5 صباحاً)
2. **احتفظ بـ terminal مفتوح** لمراقبة الـ logs
3. **اختبر على staging قبل production**
4. **الـ Backup إلزامي** - لا تتجاوز هذه الخطوة أبداً

---

**الوقت المتوقع للعملية:** 10-15 دقيقة

✅ **جاهز للـ Production!**
