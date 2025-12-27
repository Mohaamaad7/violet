# نظام المواقع الجغرافية - Geographic Location System

**التاريخ:** 27 ديسمبر 2024  
**الإصدار:** 1.0  
**المطور:** Mohammad  
**المشروع:** Violet E-commerce Platform  

---

## 📋 جدول المحتويات

1. [نظرة عامة](#نظرة-عامة)
2. [الأهداف](#الأهداف)
3. [البنية التقنية](#البنية-التقنية)
4. [خطوات التنفيذ](#خطوات-التنفيذ)
5. [المشاكل والحلول](#المشاكل-والحلول)
6. [الملفات المنشأة](#الملفات-المنشأة)
7. [كيفية الاستخدام](#كيفية-الاستخدام)
8. [ملاحظات مهمة](#ملاحظات-مهمة)

---

## 🎯 نظرة عامة

### السياق
بعد اكتمال نظام الدفع (Kashier) ونظام الكوبونات، كانت هناك حاجة لتحسين تجربة المستخدم في صفحة Checkout من خلال:
- **منع الأخطاء الإملائية** في العناوين
- **حساب تكلفة الشحن تلقائياً** بدقة
- **تحديد أيام التوصيل** حسب المنطقة
- **تقارير جغرافية دقيقة** للمبيعات

### ما تم إنجازه
تم بناء نظام ERP-style كامل لإدارة المواقع الجغرافية على ثلاث مستويات:
```
دولة (Country) → محافظة (Governorate) → مدينة (City)
```

مع تكامل كامل في:
- ✅ لوحة التحكم (Filament Admin Panel)
- ✅ صفحة Checkout (Cascading Dropdowns)
- ✅ حساب تكلفة الشحن التلقائي
- ✅ تعدد اللغات (عربي/إنجليزي)

---

## 🎯 الأهداف

### الأهداف الأساسية
1. **تحسين جودة البيانات**: منع الأخطاء الإملائية في العناوين
2. **أتمتة الشحن**: حساب تكلفة الشحن تلقائياً حسب الموقع
3. **تجربة مستخدم أفضل**: Cascading Dropdowns سهلة الاستخدام
4. **تقارير دقيقة**: بيانات جغرافية موحدة للتحليلات

### الأهداف الثانوية
- دعم التوسع لدول أخرى مستقبلاً
- مرونة في تحديد تكاليف شحن مخصصة لكل مدينة
- تحديد أيام التوصيل المتوقعة
- Backward compatibility مع البيانات القديمة

---

## 🏗️ البنية التقنية

### قاعدة البيانات

#### 1. جدول Countries (الدول)
```sql
CREATE TABLE countries (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    code VARCHAR(2) NOT NULL UNIQUE,      -- ISO 3166-1 (مثال: EG)
    phone_code VARCHAR(10) NOT NULL,       -- مثال: +20
    currency_code VARCHAR(3) NOT NULL,     -- ISO 4217 (مثال: EGP)
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);
```

**الغرض:**
- تخزين بيانات الدول الأساسية
- دعم تعدد العملات
- إمكانية تعطيل دول مؤقتاً
- ترتيب العرض في القوائم

#### 2. جدول Governorates (المحافظات)
```sql
CREATE TABLE governorates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    country_id BIGINT UNSIGNED NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0,  -- تكلفة الشحن الافتراضية
    delivery_days INT UNSIGNED DEFAULT 0,            -- أيام التوصيل المتوقعة
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    INDEX idx_country_active (country_id, is_active),
    INDEX idx_sort_order (sort_order)
);
```

**الغرض:**
- تخزين المحافظات لكل دولة
- تحديد تكلفة شحن افتراضية لكل محافظة
- تحديد مدة التوصيل المتوقعة
- Cascade delete عند حذف الدولة

#### 3. جدول Cities (المدن)
```sql
CREATE TABLE cities (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    governorate_id BIGINT UNSIGNED NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    shipping_cost DECIMAL(10,2) NULL,     -- تكلفة مخصصة (اختياري)
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (governorate_id) REFERENCES governorates(id) ON DELETE CASCADE,
    INDEX idx_governorate_active (governorate_id, is_active),
    INDEX idx_sort_order (sort_order)
);
```

**الغرض:**
- تخزين المدن لكل محافظة
- تكلفة شحن مخصصة اختيارية (تتجاوز تكلفة المحافظة)
- Cascade delete عند حذف المحافظة

#### 4. تحديث جدول shipping_addresses
```sql
ALTER TABLE shipping_addresses ADD (
    country_id BIGINT UNSIGNED NULL,
    governorate_id BIGINT UNSIGNED NULL,
    city_id BIGINT UNSIGNED NULL,
    
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
    FOREIGN KEY (governorate_id) REFERENCES governorates(id) ON DELETE SET NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL,
    
    INDEX idx_location (country_id, governorate_id, city_id)
);

-- الحقول القديمة محفوظة للتوافق:
-- governorate VARCHAR(255)
-- city VARCHAR(255)
-- area VARCHAR(255)
```

**ملاحظة:** تم الحفاظ على الحقول القديمة لضمان التوافق مع البيانات الموجودة.

---

### Models (النماذج)

#### 1. Country Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name_ar', 'name_en', 'code', 
        'phone_code', 'currency_code',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Mutators للتحويل التلقائي لـ uppercase
    protected function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    protected function setCurrencyCodeAttribute($value)
    {
        $this->attributes['currency_code'] = strtoupper($value);
    }

    // Relationships
    public function governorates()
    {
        return $this->hasMany(Governorate::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function getLocalizedNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }
}
```

#### 2. Governorate Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    protected $fillable = [
        'country_id', 'name_ar', 'name_en',
        'shipping_cost', 'delivery_days',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'delivery_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }
}
```

#### 3. City Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'governorate_id', 'name_ar', 'name_en',
        'shipping_cost', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGovernorate($query, $governorateId)
    {
        return $query->where('governorate_id', $governorateId);
    }

    // Helpers - تكلفة فعلية (مخصصة أو من المحافظة)
    public function getEffectiveShippingCostAttribute()
    {
        return $this->shipping_cost ?? $this->governorate->shipping_cost ?? 0;
    }
}
```

---

### Data Seeding (البيانات الأولية)

#### EgyptLocationsSeeder
تم إنشاء Seeder شامل يحتوي على:

**الدولة:**
- مصر (Egypt) - Code: EG, Phone: +20, Currency: EGP

**المحافظات (27):**
| المحافظة | تكلفة الشحن | أيام التوصيل | عدد المدن |
|---------|------------|-------------|----------|
| القاهرة | 30 ج.م | 2 يوم | 10 |
| الجيزة | 30 ج.م | 2 يوم | 10 |
| الإسكندرية | 50 ج.م | 3 أيام | 10 |
| القليوبية | 35 ج.م | 2 يوم | 10 |
| الدقهلية | 60 ج.م | 3 أيام | 10 |
| الشرقية | 55 ج.م | 3 أيام | 10 |
| ... | ... | ... | ... |
| أسوان | 80 ج.م | 5 أيام | 6 |
| الوادي الجديد | 90 ج.م | 6 أيام | 5 |

**المدن (207):**
موزعة على كل المحافظات بأسماء عربية وإنجليزية.

**إجمالي البيانات:**
- 1 دولة
- 27 محافظة
- 207 مدينة
- **المجموع: 235 سجل**

---

## 📝 خطوات التنفيذ

### المرحلة 1: إعداد قاعدة البيانات

#### 1.1 إنشاء Migrations
```bash
php artisan make:migration create_countries_table
php artisan make:migration create_governorates_table
php artisan make:migration create_cities_table
php artisan make:migration add_location_foreign_keys_to_shipping_addresses_table
```

#### 1.2 تشغيل Migrations
```bash
php artisan migrate
```

**النتيجة:**
```
✅ create_countries_table - 151.48ms
✅ create_governorates_table - 150.70ms
✅ create_cities_table - 156.76ms
✅ add_location_foreign_keys_to_shipping_addresses - 365.53ms
```

---

### المرحلة 2: إنشاء Models

#### 2.1 إنشاء الملفات
```bash
# تم إنشاء يدوياً:
app/Models/Country.php
app/Models/Governorate.php
app/Models/City.php
```

#### 2.2 تحديث ShippingAddress Model
أضفنا:
- Relationships الجديدة (country, governorateRelation, cityRelation)
- Fillable fields (country_id, governorate_id, city_id)
- Helper method: `getEffectiveShippingCostAttribute()`

---

### المرحلة 3: Seeding البيانات

#### 3.1 إنشاء Seeder
```bash
# تم إنشاء:
database/seeders/EgyptLocationsSeeder.php
```

#### 3.2 تشغيل Seeder
```bash
php artisan db:seed --class=EgyptLocationsSeeder
```

**النتيجة:**
```
✅ القاهرة: 10 cities
✅ الجيزة: 10 cities
✅ الإسكندرية: 10 cities
... (24 محافظة أخرى)
✅ Egypt locations seeded successfully!
📊 Total: 1 Country, 27 Governorates, 207 Cities
```

---

### المرحلة 4: Filament Admin Resources

#### 4.1 إنشاء Resources
```bash
php artisan make:filament-resource Country --generate
php artisan make:filament-resource Governorate --generate
php artisan make:filament-resource City --generate
```

#### 4.2 هيكل الملفات
لكل Resource:
```
app/Filament/Resources/Countries/
├── CountryResource.php
├── Schemas/
│   └── CountryForm.php
├── Tables/
│   └── CountriesTable.php
└── Pages/
    ├── ListCountries.php
    ├── CreateCountry.php
    └── EditCountry.php
```

#### 4.3 الميزات المضافة
**CountryResource:**
- Form: 3 Sections (معلومات أساسية، اتصال وعملة، إعدادات)
- Table: عرض كل البيانات مع Badges ملونة
- Filters: حسب الحالة (نشط/غير نشط)

**GovernorateResource:**
- Form: تكلفة الشحن وأيام التوصيل
- Table: عرض اسم الدولة وعدد المدن
- Filters: حسب الدولة والحالة
- Default country: Egypt

**CityResource:**
- Form: تكلفة شحن مخصصة اختيارية
- Table: عرض هرمي (دولة → محافظة → مدينة)
- Filters: حسب المحافظة، الحالة، نوع التكلفة
- Description: يوضح إذا كانت التكلفة مخصصة أو من المحافظة

---

### المرحلة 5: تكامل Checkout

#### 5.1 تحديث CheckoutPage Component

**إضافة Properties:**
```php
public $country_id = null;
public $governorate_id = null;
public $city_id = null;
```

**إضافة Validation:**
```php
protected function rules()
{
    return [
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'email' => 'required|email|max:255',
        'phone' => 'required|regex:/^[0-9]{10,15}$/',
        'country_id' => 'required|exists:countries,id',
        'governorate_id' => 'required|exists:governorates,id',
        'city_id' => 'required|exists:cities,id',
        'address_details' => 'required|string|max:500',
    ];
}
```

**إضافة Lifecycle Hooks:**
```php
public function updatedCountryId($value)
{
    $this->governorate_id = null;
    $this->city_id = null;
    $this->calculateShippingCost();
}

public function updatedGovernorateId($value)
{
    $this->city_id = null;
    $this->calculateShippingCost();
}

public function updatedCityId($value)
{
    $this->calculateShippingCost();
}
```

**إضافة Computed Properties:**
```php
public function getCountriesProperty()
{
    return Country::where('is_active', true)
        ->orderBy('name_ar')
        ->get();
}

public function getGovernoratesProperty()
{
    if (!$this->country_id) return [];
    
    return Governorate::where('country_id', $this->country_id)
        ->where('is_active', true)
        ->orderBy('name_ar')
        ->get();
}

public function getCitiesProperty()
{
    if (!$this->governorate_id) return [];
    
    return City::where('governorate_id', $this->governorate_id)
        ->where('is_active', true)
        ->orderBy('name_ar')
        ->get();
}
```

**حساب تكلفة الشحن:**
```php
protected function calculateShippingCost(): void
{
    $this->shippingCost = 50; // Default
    
    if ($this->city_id) {
        $city = City::find($this->city_id);
        if ($city) {
            // استخدام تكلفة المدينة أو المحافظة
            $this->shippingCost = $city->shipping_cost 
                ?? $city->governorate->shipping_cost 
                ?? 50;
        }
    } elseif ($this->governorate_id) {
        $governorate = Governorate::find($this->governorate_id);
        if ($governorate) {
            $this->shippingCost = $governorate->shipping_cost ?? 50;
        }
    }
    
    $this->recalculateTotal();
}
```

#### 5.2 تحديث Blade Template

**إضافة Cascading Selects:**
```blade
{{-- Country Selection --}}
<select wire:model.live="country_id" required>
    <option value="">{{ __('messages.checkout.select_country') }}</option>
    @foreach($this->countries as $country)
        <option value="{{ $country->id }}">
            {{ app()->getLocale() === 'ar' ? $country->name_ar : $country->name_en }}
        </option>
    @endforeach
</select>

{{-- Governorate Selection --}}
<select wire:model.live="governorate_id" required>
    <option value="">{{ __('messages.checkout.select_governorate') }}</option>
    @foreach($this->governorates as $gov)
        <option value="{{ $gov->id }}">
            {{ app()->getLocale() === 'ar' ? $gov->name_ar : $gov->name_en }}
        </option>
    @endforeach
</select>

{{-- City Selection --}}
<select wire:model.live="city_id" 
        {{ !$governorate_id ? 'disabled' : '' }} 
        required>
    <option value="">{{ __('messages.checkout.select_city') }}</option>
    @if($governorate_id)
        @foreach($this->cities as $city)
            <option value="{{ $city->id }}">
                {{ app()->getLocale() === 'ar' ? $city->name_ar : $city->name_en }}
            </option>
        @endforeach
    @endif
</select>
```

#### 5.3 تحديث Order Creation

في `placeOrder()` method:
```php
// Get location names for display
$governorate = Governorate::find($this->governorate_id);
$city = City::find($this->city_id);

// Save with both IDs and names
ShippingAddress::create([
    'customer_id' => $customer->id,
    'country_id' => $this->country_id,
    'governorate_id' => $this->governorate_id,
    'city_id' => $this->city_id,
    'governorate' => $governorate?->name_ar ?? '',  // Backward compatibility
    'city' => $city?->name_ar ?? '',                // Backward compatibility
    // ... other fields
]);
```

---

### المرحلة 6: الترجمات

#### إضافة مفاتيح الترجمة

**ملف:** `lang/ar/messages.php`
```php
'checkout' => [
    // ... existing keys
    'country' => 'الدولة',
    'select_country' => 'اختر الدولة',
    'governorate' => 'المحافظة',
    'select_governorate' => 'اختر المحافظة',
    'city' => 'المدينة',
    'select_city' => 'اختر المدينة',
    'select_governorate_first' => 'اختر المحافظة أولاً',
],
```

**ملف:** `lang/en/messages.php`
```php
'checkout' => [
    // ... existing keys
    'country' => 'Country',
    'select_country' => 'Select Country',
    'governorate' => 'Governorate',
    'select_governorate' => 'Select Governorate',
    'city' => 'City',
    'select_city' => 'Select City',
    'select_governorate_first' => 'Select governorate first',
],
```

---

## ⚠️ المشاكل والحلول

### المشكلة 1: أخطاء أسماء الأيقونات

**الخطأ:**
```
Undefined constant Filament\Support\Icons\Heroicon::OutlineMapPin
```

**السبب:**
استخدام أسماء أيقونات غير موجودة في Filament 4.

**الحل:**
```php
// ❌ خطأ
protected static string $navigationIcon = Heroicon::OutlineMapPin;

// ✅ صحيح
protected static string $navigationIcon = Heroicon::OutlinedRectangleStack;
```

**التطبيق:**
- CountryResource: `OutlinedRectangleStack`
- GovernorateResource: `OutlinedRectangleStack`
- CityResource: `OutlinedRectangleStack`

---

### المشكلة 2: Import خاطئ للـ Section

**الخطأ:**
```
Class "Filament\Forms\Components\Section" not found
```

**السبب:**
في Filament 4، الـ `Section` موجود في namespace مختلف.

**الحل:**
```php
// ❌ Filament 3
use Filament\Forms\Components\Section;

// ✅ Filament 4
use Filament\Schemas\Components\Section;
```

**التطبيق:**
تم تصحيح الـ imports في:
- CountryForm.php
- GovernorateForm.php
- CityForm.php

---

### المشكلة 3: navigationGroup Type Error

**الخطأ:**
```
Type of CityResource::$navigationGroup must be UnitEnum|string|null
```

**السبب:**
في Filament 4، تغير نوع البيانات المطلوب.

**الحل:**
```php
// ❌ Filament 3
protected static ?string $navigationGroup = 'الإعدادات الجغرافية';

// ✅ Filament 4
protected static UnitEnum|string|null $navigationGroup = 'الإعدادات الجغرافية';
```

**التطبيق:**
تم إضافة:
```php
use UnitEnum;
```

في جميع الـ Resources (Country, Governorate, City).

---

### المشكلة 4: Redirect بعد الحفظ

**المشكلة:**
بعد حفظ السجل، البقاء في صفحة التعديل بدلاً من الرجوع للقائمة.

**الحل:**
إضافة `getRedirectUrl()` في صفحات Create و Edit:

```php
// في CreateCity.php
protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}

// في EditCity.php
protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
```

**التطبيق:**
تم التطبيق على:
- CreateCountry & EditCountry
- CreateGovernorate & EditGovernorate
- CreateCity & EditCity

---

### المشكلة 5: uppercase() Method غير موجود

**الخطأ:**
```
Method Filament\Forms\Components\TextInput::uppercase does not exist.
```

**السبب:**
في Filament 4، لا يوجد method اسمه `uppercase()`.

**المحاولة الأولى (فشلت):**
```php
->transform(fn ($value) => strtoupper($value))  // ❌ غير موجود أيضاً
```

**الحل النهائي:**
استخدام **Mutators في الـ Model**:

```php
// في Country Model
protected function setCodeAttribute($value)
{
    $this->attributes['code'] = strtoupper($value);
}

protected function setCurrencyCodeAttribute($value)
{
    $this->attributes['currency_code'] = strtoupper($value);
}
```

**التطبيق:**
تم إزالة `->uppercase()` و `->transform()` من CountryForm وإضافة Mutators في Country Model.

---

### المشكلة 6: محاولة استخدام Tom Select (فشلت)

**المحاولة:**
استخدام مكتبة Tom Select لإضافة Search في القوائم المنسدلة.

**المشاكل:**
1. تضارب مع `wire:model.live` في Livewire
2. Re-initialization مستمر يمسح الاختيارات
3. مربع البحث غير واضح
4. أخطاء متكررة

**الحل:**
إلغاء Tom Select بالكامل والعودة لـ `<select>` عادي مع `wire:model.live`.

**الدروس المستفادة:**
- البساطة أفضل من التعقيد
- تجنب المكتبات الخارجية مع Livewire إلا للضرورة القصوى
- الـ Native HTML elements تعمل بشكل أفضل مع Livewire

---

### المشكلة 7: استدعاء Lifecycle Hooks مباشرة

**الخطأ:**
```
Unable to call lifecycle method [updatedCountryId] directly on component
```

**السبب:**
في Livewire 3، لا يمكن استدعاء lifecycle hooks مباشرة عبر `@this.call()`.

**المحاولة الخاطئة:**
```javascript
@this.call('updatedCountryId', value)  // ❌ خطأ
```

**الحل:**
إنشاء methods عادية بدلاً من lifecycle hooks:

```php
// بدلاً من lifecycle hook
public function changeCountry($countryId)
{
    $this->country_id = $countryId;
    $this->governorate_id = null;
    $this->city_id = null;
    $this->calculateShippingCost();
}
```

**ملاحظة:**
في النهاية تم إلغاء هذا النهج بالكامل لأننا ألغينا Tom Select، والـ `wire:model.live` يتعامل مع lifecycle hooks تلقائياً.

---

## 📦 الملفات المنشأة والمعدّلة

### Migrations (4 ملفات)
```
database/migrations/
├── 2025_12_26_142211_create_countries_table.php
├── 2025_12_26_142504_create_governorates_table.php
├── 2025_12_26_142511_create_cities_table.php
└── 2025_12_26_142517_add_location_foreign_keys_to_shipping_addresses_table.php
```

### Models (4 ملفات - 3 جديد + 1 معدّل)
```
app/Models/
├── Country.php              (جديد)
├── Governorate.php          (جديد)
├── City.php                 (جديد)
└── ShippingAddress.php      (معدّل)
```

### Seeders (1 ملف)
```
database/seeders/
└── EgyptLocationsSeeder.php
```

### Filament Resources (12 ملف لكل 3 Resources)

**Countries (4 ملفات):**
```
app/Filament/Resources/Countries/
├── CountryResource.php
├── Schemas/CountryForm.php
├── Tables/CountriesTable.php
└── Pages/
    ├── ListCountries.php
    ├── CreateCountry.php
    └── EditCountry.php
```

**Governorates (4 ملفات):**
```
app/Filament/Resources/Governorates/
├── GovernorateResource.php
├── Schemas/GovernorateForm.php
├── Tables/GovernoratesTable.php
└── Pages/
    ├── ListGovernorates.php
    ├── CreateGovernorate.php
    └── EditGovernorate.php
```

**Cities (4 ملفات):**
```
app/Filament/Resources/Cities/
├── CityResource.php
├── Schemas/CityForm.php
├── Tables/CitiesTable.php
└── Pages/
    ├── ListCities.php
    ├── CreateCity.php
    └── EditCity.php
```

### Livewire Component (1 ملف معدّل)
```
app/Livewire/Store/
└── CheckoutPage.php         (معدّل)
```

### Blade Templates (1 ملف معدّل)
```
resources/views/livewire/store/
└── checkout-page.blade.php  (معدّل)
```

### Translations (2 ملف معدّل)
```
lang/
├── ar/messages.php          (معدّل)
└── en/messages.php          (معدّل)
```

---

## 📖 كيفية الاستخدام

### للمسؤول (Admin Panel)

#### 1. إضافة دولة جديدة
```
http://violet.test/admin/countries/create
```

**الحقول المطلوبة:**
- الاسم بالعربية
- الاسم بالإنجليزية
- رمز الدولة (ISO 3166-1)
- كود الهاتف
- رمز العملة (ISO 4217)

**اختياري:**
- حالة النشاط
- ترتيب العرض

#### 2. إضافة محافظة
```
http://violet.test/admin/governorates/create
```

**الحقول المطلوبة:**
- الدولة (يتم اختيار مصر افتراضياً)
- الاسم بالعربية
- الاسم بالإنجليزية
- تكلفة الشحن (ج.م)
- أيام التوصيل

#### 3. إضافة مدينة
```
http://violet.test/admin/cities/create
```

**الحقول المطلوبة:**
- المحافظة
- الاسم بالعربية
- الاسم بالإنجليزية

**اختياري:**
- تكلفة شحن مخصصة (إذا كانت مختلفة عن المحافظة)

---

### للعميل (Checkout)

#### تدفق المستخدم
```
1. المستخدم يفتح صفحة Checkout
   ↓
2. يختار الدولة (مصر محددة افتراضياً)
   ↓
3. يختار المحافظة
   ↓ (تظهر المدن المتاحة)
4. يختار المدينة
   ↓ (يتم حساب تكلفة الشحن تلقائياً)
5. يكمل البيانات الأخرى
   ↓
6. يضغط "إرسال الطلب"
```

#### Cascading Behavior
- **عند اختيار دولة:** تظهر محافظات هذه الدولة فقط
- **عند تغيير الدولة:** تُفرغ المحافظة والمدينة
- **عند اختيار محافظة:** تظهر مدن هذه المحافظة فقط + يتم حساب تكلفة الشحن
- **عند تغيير المحافظة:** تُفرغ المدينة
- **عند اختيار مدينة:** يتم إعادة حساب تكلفة الشحن (إذا كان لها تكلفة مخصصة)

#### حساب تكلفة الشحن
```
IF (City has custom shipping_cost)
    Use City shipping_cost
ELSE IF (Governorate has shipping_cost)
    Use Governorate shipping_cost
ELSE
    Use Default (50 EGP)
```

---

### للمطور (Development)

#### إضافة دولة جديدة برمجياً

```php
use App\Models\Country;
use App\Models\Governorate;
use App\Models\City;

// 1. إنشاء الدولة
$country = Country::create([
    'name_ar' => 'المملكة العربية السعودية',
    'name_en' => 'Saudi Arabia',
    'code' => 'SA',
    'phone_code' => '+966',
    'currency_code' => 'SAR',
    'is_active' => true,
    'sort_order' => 0,
]);

// 2. إضافة محافظات
$riyadh = Governorate::create([
    'country_id' => $country->id,
    'name_ar' => 'الرياض',
    'name_en' => 'Riyadh',
    'shipping_cost' => 25.00,
    'delivery_days' => 2,
    'is_active' => true,
]);

// 3. إضافة مدن
City::create([
    'governorate_id' => $riyadh->id,
    'name_ar' => 'الرياض',
    'name_en' => 'Riyadh',
    'is_active' => true,
]);
```

#### Query Examples

```php
// جلب كل الدول النشطة
$countries = Country::active()->orderBy('name_ar')->get();

// جلب محافظات دولة معينة
$governorates = Governorate::active()
    ->byCountry($countryId)
    ->orderBy('name_ar')
    ->get();

// جلب مدن محافظة معينة
$cities = City::active()
    ->byGovernorate($governorateId)
    ->orderBy('name_ar')
    ->get();

// حساب تكلفة الشحن لمدينة
$city = City::find($cityId);
$shippingCost = $city->effective_shipping_cost;

// جلب الاسم المحلي
$country = Country::find($countryId);
echo $country->localized_name;  // يعرض عربي أو إنجليزي حسب اللغة
```

---

## 📝 ملاحظات مهمة

### للتطوير المستقبلي

#### 1. إضافة دول جديدة
عند إضافة دول جديدة:
- استخدم Seeder منفصل لكل دولة
- تأكد من صحة أكواد ISO
- راجع تكاليف الشحن بعناية
- حدّث الـ validation rules إذا لزم الأمر

#### 2. تكاليف الشحن
- يمكن تحديث تكاليف الشحن من Admin Panel مباشرة
- التكلفة المخصصة للمدينة اختيارية
- إذا لم تُحدد تكلفة للمدينة، تُستخدم تكلفة المحافظة
- يمكن إضافة منطق أكثر تعقيداً (مثل الوزن، الحجم) لاحقاً

#### 3. Backward Compatibility
- الحقول القديمة (governorate, city, area) محفوظة
- عند إنشاء عنوان جديد، تُحفظ البيانات في:
  - الحقول الجديدة: country_id, governorate_id, city_id
  - الحقول القديمة: governorate (name), city (name)
- هذا يضمن عدم كسر التقارير القديمة

#### 4. Performance
- تم إضافة Indexes على:
  - is_active (لكل الجداول)
  - country_id, governorate_id (للعلاقات)
  - sort_order (للترتيب)
- استخدم `orderBy('sort_order')` للتحكم في ترتيب العرض
- فكّر في Caching للدول/المحافظات إذا زاد العدد كثيراً

#### 5. Validation
```php
// في حالة إضافة دول متعددة:
'country_id' => 'required|exists:countries,id',

// تأكد من أن المحافظة تتبع الدولة المختارة:
'governorate_id' => [
    'required',
    'exists:governorates,id',
    Rule::exists('governorates', 'id')
        ->where('country_id', $this->country_id)
],

// تأكد من أن المدينة تتبع المحافظة المختارة:
'city_id' => [
    'required',
    'exists:cities,id',
    Rule::exists('cities', 'id')
        ->where('governorate_id', $this->governorate_id)
],
```

#### 6. تعدد اللغات
- كل جدول يحتوي على name_ar و name_en
- استخدم `localized_name` attribute للحصول على الاسم حسب اللغة الحالية
- عند إضافة لغات جديدة، أضف أعمدة جديدة (name_fr, name_de, إلخ)

#### 7. الأمان
- كل الـ Foreign Keys بها `ON DELETE CASCADE` أو `SET NULL`
- عند حذف دولة → تُحذف محافظاتها ومدنها
- عند حذف محافظة → تُحذف مدنها
- عند حذف موقع مرتبط بعنوان شحن → يُضبط على NULL

---

### المميزات المستقبلية المقترحة

#### 1. حساب تكلفة شحن متقدم
```php
// إضافة أعمدة جديدة:
ALTER TABLE governorates ADD (
    min_order_free_shipping DECIMAL(10,2) NULL,  -- حد أدنى للشحن المجاني
    express_shipping_cost DECIMAL(10,2) NULL,     -- تكلفة شحن سريع
    express_delivery_days INT NULL                 -- أيام التوصيل السريع
);
```

#### 2. مناطق فرعية (Areas/Districts)
```sql
CREATE TABLE areas (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    city_id BIGINT UNSIGNED NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    shipping_cost DECIMAL(10,2) NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
);
```

#### 3. نطاقات البريد (Postal Codes)
```sql
ALTER TABLE cities ADD postal_code VARCHAR(10) NULL;
```

#### 4. إحصائيات وتقارير
```php
// في CityResource
public static function getWidgets(): array
{
    return [
        CitiesChart::class,
        TopCitiesByOrders::class,
        ShippingCostAnalysis::class,
    ];
}
```

#### 5. Geocoding Integration
```php
// إضافة إحداثيات GPS
ALTER TABLE cities ADD (
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL
);

// استخدام Google Maps API لحساب المسافات
```

---

## 🎓 الدروس المستفادة

### التقنية

1. **Filament 4 vs Filament 3:**
   - تحقق دائماً من الوثائق الرسمية
   - الـ namespace تغير في بعض Components
   - بعض Methods تم إزالتها أو تغييرها

2. **Livewire 3:**
   - لا يمكن استدعاء lifecycle hooks مباشرة
   - `wire:model.live` أفضل من استدعاء methods يدوياً
   - البساطة في الـ JavaScript أفضل من التعقيد

3. **Database Design:**
   - Cascade deletes مهمة للحفاظ على سلامة البيانات
   - Indexes ضرورية للأداء
   - Backward compatibility مهمة عند التحديثات

### التطوير

1. **البساطة أولاً:**
   - Native HTML elements تعمل بشكل أفضل
   - تجنب المكتبات الخارجية إلا للضرورة

2. **التوثيق:**
   - توثيق المشاكل والحلول يوفر وقتاً كثيراً
   - الأخطاء فرصة للتعلم

3. **Testing:**
   - اختبر كل خطوة قبل الانتقال للتالية
   - اختبر السيناريوهات السلبية أيضاً

---

## 📊 الإحصائيات النهائية

### الكود
- **إجمالي الملفات المنشأة:** ~25 ملف
- **إجمالي الملفات المعدّلة:** ~5 ملفات
- **إجمالي الأسطر المضافة:** ~2500 سطر
- **Migrations:** 4
- **Models:** 3 جديد + 1 معدّل
- **Filament Resources:** 3 (كل واحد = 4 ملفات)
- **Seeders:** 1

### البيانات
- **الدول:** 1 (مصر)
- **المحافظات:** 27
- **المدن:** 207
- **إجمالي السجلات:** 235

### تكاليف الشحن
- **الحد الأدنى:** 30 ج.م (القاهرة، الجيزة)
- **الحد الأقصى:** 90 ج.م (الوادي الجديد)
- **المتوسط:** ~55 ج.م

### أيام التوصيل
- **الأسرع:** 2 يوم (القاهرة، الجيزة)
- **الأبطأ:** 6 أيام (الوادي الجديد)
- **المتوسط:** ~3.5 يوم

---

## ✅ Checklist للتطوير المستقبلي

عند إضافة دول جديدة:

- [ ] إنشاء Seeder للدولة الجديدة
- [ ] التأكد من صحة أكواد ISO (Country Code, Currency Code)
- [ ] مراجعة تكاليف الشحن مع قسم اللوجستيات
- [ ] مراجعة أيام التوصيل المتوقعة
- [ ] اختبار Cascading Dropdowns
- [ ] اختبار حساب تكلفة الشحن
- [ ] تحديث الترجمات إذا لزم الأمر
- [ ] توثيق البيانات المضافة

عند إضافة ميزات جديدة:

- [ ] مراجعة تأثيرها على الأداء
- [ ] التأكد من Backward Compatibility
- [ ] كتابة Tests
- [ ] تحديث التوثيق
- [ ] مراجعة الأمان

---

## 📞 للدعم والاستفسارات

في حالة وجود مشاكل أو استفسارات:

1. راجع قسم "المشاكل والحلول" في هذا التوثيق
2. راجع الـ Stack Trace بعناية
3. تأكد من إصدار Filament (يجب أن يكون 4.x)
4. تأكد من إصدار Livewire (يجب أن يكون 3.x)
5. نظف الـ Cache:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   php artisan config:clear
   ```

---

## 📚 المراجع

- [Filament 4 Documentation](https://filamentphp.com/docs/4.x)
- [Livewire 3 Documentation](https://livewire.laravel.com/docs)
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [ISO 3166-1 Country Codes](https://en.wikipedia.org/wiki/ISO_3166-1)
- [ISO 4217 Currency Codes](https://en.wikipedia.org/wiki/ISO_4217)

---

**تم التوثيق بتاريخ:** 27 ديسمبر 2024  
**آخر تحديث:** 27 ديسمبر 2024  
**الحالة:** ✅ مكتمل ويعمل بنجاح
