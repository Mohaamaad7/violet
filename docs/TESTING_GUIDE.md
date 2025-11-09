# 🧪 دليل الاختبار - Violet E-Commerce

## 📌 الحالة الحالية: **المرحلة 2 مكتملة**

يمكنك الآن اختبار:
- ✅ Models & Relations
- ✅ Database Schema
- ✅ Seeders & Factories
- ✅ Scopes & Accessors
- ✅ User Roles & Permissions

---

## 🎯 مستويات الاختبار المتاحة

### **المستوى 1: اختبار Manual (متاح الآن ✅)**

#### 1. اختبار Models عبر Tinker
```bash
php artisan tinker
```

**أمثلة:**
```php
// اختبار Category
$category = App\Models\Category::with('products', 'children')->first();
echo $category->name; // Electronics
echo $category->products->count(); // عدد المنتجات
echo $category->children->count(); // عدد الفئات الفرعية

// اختبار Product
$product = App\Models\Product::with('category')->first();
echo $product->name;
echo $product->final_price; // السعر بعد الخصم
echo $product->is_on_sale; // هل عليه تخفيض؟
echo $product->discount_percentage; // نسبة الخصم

// اختبار User Roles
$admin = App\Models\User::where('email', 'admin@violet.com')->first();
echo $admin->getRoleNames(); // ["super-admin"]
echo $admin->can('manage products'); // true
```

#### 2. تشغيل اختبار شامل
```bash
php test_examples.php
```

#### 3. اختبار Seeders
```bash
# إعادة بناء قاعدة البيانات مع البيانات التجريبية
php artisan migrate:fresh --seed

# تشغيل seeder معين
php artisan db:seed --class=DemoDataSeeder
```

---

### **المستوى 2: اختبار API (يحتاج المرحلة 3 ⏳)**

**متطلبات:**
- إنشاء API Controllers
- إنشاء API Routes
- تفعيل Sanctum Authentication

**أمثلة بعد المرحلة 3:**
```bash
# Test Authentication
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@violet.com","password":"password"}'

# Test Get Products
curl http://localhost:8000/api/products

# Test Get Categories
curl http://localhost:8000/api/categories
```

---

### **المستوى 3: اختبار UI (يحتاج المرحلة 4-5 ⏳)**

**متطلبات:**
- إنشاء Livewire Components
- إنشاء Views
- إنشاء Routes للواجهة

**الصفحات المتاحة بعد المرحلة 4:**
- http://localhost:8000/admin/dashboard
- http://localhost:8000/admin/products
- http://localhost:8000/admin/categories
- http://localhost:8000/admin/orders
- http://localhost:8000/admin/influencers

---

### **المستوى 4: Feature Tests (يحتاج المرحلة 6 ⏳)**

**متطلبات:**
- إنشاء Test Classes
- إعداد Testing Database

**أمثلة:**
```bash
# تشغيل جميع الاختبارات
php artisan test

# اختبار محدد
php artisan test --filter=ProductTest
```

---

## 🔬 اختبارات متاحة الآن

### ✅ **1. اختبار Database Schema**
```bash
# عرض جميع الجداول
php artisan tinker --execute="print_r(DB::select('SHOW TABLES'))"

# عرض بنية جدول معين
php artisan tinker --execute="print_r(DB::select('DESCRIBE categories'))"

# إحصائيات قاعدة البيانات
php artisan tinker --execute="
echo 'Categories: ' . App\Models\Category::count() . PHP_EOL;
echo 'Products: ' . App\Models\Product::count() . PHP_EOL;
echo 'Users: ' . App\Models\User::count() . PHP_EOL;
"
```

### ✅ **2. اختبار Relations**
```bash
php test_examples.php
```

أو عبر Tinker:
```php
// Category -> Products
$category = App\Models\Category::find(1);
$category->products; // جميع المنتجات

// Product -> Category
$product = App\Models\Product::find(1);
$product->category->name; // اسم الفئة

// User -> Orders
$user = App\Models\User::find(1);
$user->orders; // جميع الطلبات (فارغة حالياً)

// Category Hierarchy
$parent = App\Models\Category::whereNull('parent_id')->first();
$parent->children; // الفئات الفرعية
```

### ✅ **3. اختبار Scopes**
```php
// Products
App\Models\Product::active()->get(); // المنتجات النشطة
App\Models\Product::featured()->get(); // المنتجات المميزة
App\Models\Product::inStock()->get(); // المتوفرة
App\Models\Product::lowStock()->get(); // منخفضة المخزون

// Categories
App\Models\Category::active()->get(); // الفئات النشطة

// Users
App\Models\User::active()->get(); // المستخدمين النشطين
App\Models\User::customers()->get(); // العملاء
App\Models\User::influencers()->get(); // المؤثرين
```

### ✅ **4. اختبار Accessors**
```php
$product = App\Models\Product::first();
echo $product->final_price; // السعر النهائي (بعد الخصم)
echo $product->is_on_sale; // bool
echo $product->discount_percentage; // نسبة الخصم

$address = App\Models\ShippingAddress::first();
echo $address->full_address; // العنوان كامل منسق
```

### ✅ **5. اختبار Roles & Permissions**
```php
$admin = App\Models\User::where('email', 'admin@violet.com')->first();

// الأدوار
$admin->getRoleNames(); // ["super-admin"]
$admin->hasRole('super-admin'); // true

// الصلاحيات
$admin->can('manage products'); // true
$admin->getAllPermissions(); // جميع الصلاحيات

// جميع الأدوار
Spatie\Permission\Models\Role::all();

// جميع الصلاحيات
Spatie\Permission\Models\Permission::all();
```

### ✅ **6. اختبار Factories**
```php
// إنشاء فئة جديدة
App\Models\Category::factory()->create();

// إنشاء 10 منتجات
App\Models\Product::factory(10)->create([
    'category_id' => 1
]);

// إنشاء فئة مع منتجات
$category = App\Models\Category::factory()
    ->has(App\Models\Product::factory(5))
    ->create();
```

---

## 📊 **حالة قاعدة البيانات الحالية**

```
Categories: 20 (5 رئيسية + 15 فرعية)
Products: 150 (10 لكل فئة فرعية)
Users: 3 (Admin, Manager, Customer)
Roles: 6 (super-admin, admin, manager, sales, accountant, content-manager)
Permissions: 32
```

---

## 🔐 **حسابات الدخول (للمراحل القادمة)**

```
Super Admin:
- Email: admin@violet.com
- Password: password

Manager:
- Email: manager@violet.com
- Password: password

Customer:
- Email: customer@violet.com
- Password: password
```

---

## 🎯 **متى يمكن اختبار الواجهات؟**

### **المرحلة 3: Admin Business Logic**
✅ اختبار Controllers عبر Routes
✅ اختبار Form Validation
✅ اختبار Services

### **المرحلة 4: Admin Panel UI**
✅ اختبار صفحات الإدارة
✅ اختبار Livewire Components
✅ اختبار CRUD Operations

### **المرحلة 5: Frontend Store**
✅ اختبار واجهة المتجر
✅ اختبار صفحات المنتجات
✅ اختبار عملية الشراء

### **المرحلة 6: Testing & Security**
✅ Feature Tests
✅ Unit Tests
✅ Integration Tests
✅ Security Tests

---

## 💡 **أمثلة عملية للاختبار الآن**

### مثال 1: إنشاء منتج جديد
```php
php artisan tinker

$product = App\Models\Product::create([
    'category_id' => 1,
    'name' => 'iPhone 15 Pro',
    'slug' => 'iphone-15-pro',
    'sku' => 'IP15P-001',
    'price' => 50000,
    'sale_price' => 45000,
    'stock' => 10,
    'is_active' => true,
    'is_featured' => true
]);

echo "Product created: {$product->name}";
echo "Final Price: {$product->final_price} EGP";
echo "Discount: {$product->discount_percentage}%";
```

### مثال 2: الحصول على منتجات فئة معينة
```php
$phones = App\Models\Category::where('slug', 'phones')
    ->first()
    ->products()
    ->active()
    ->inStock()
    ->get();

foreach ($phones as $phone) {
    echo "{$phone->name} - {$phone->final_price} EGP\n";
}
```

### مثال 3: منتجات في تخفيض
```php
$onSale = App\Models\Product::whereNotNull('sale_price')
    ->active()
    ->inStock()
    ->get();

foreach ($onSale as $product) {
    echo "{$product->name}: was {$product->price}, now {$product->final_price}\n";
    echo "Save {$product->discount_percentage}%!\n\n";
}
```

---

## 🚀 **الخطوة التالية للاختبار الكامل**

**نحتاج المرحلة 3 لـ:**
1. Controllers (للتعامل مع HTTP Requests)
2. Routes (لتوجيه الطلبات)
3. Form Requests (للـ Validation)
4. Policies (للـ Authorization)

**بعدها يمكن:**
- ✅ اختبار API Endpoints
- ✅ اختبار CRUD Operations
- ✅ اختبار Permissions على العمليات
- ✅ اختبار Validation Rules

---

## 📝 **ملاحظات مهمة**

1. ✅ **قاعدة البيانات جاهزة** - 39 جدول
2. ✅ **Models جاهزة** - 23 نموذج مع علاقات
3. ✅ **Seeders جاهزة** - بيانات تجريبية
4. ⏳ **Controllers** - تحتاج المرحلة 3
5. ⏳ **Views** - تحتاج المرحلة 4
6. ⏳ **API** - تحتاج المرحلة 3

**الخلاصة:** يمكنك اختبار Database و Models الآن، والواجهات بعد المرحلة 3-4! 🎯
