# توثيق نظام الكوبونات والخصومات (Coupon System Documentation)

هذا المستند يوثق عملية بناء وتطوير نظام الكوبونات في مشروع Violet Store، بما في ذلك الأهداف، خطوات التنفيذ، التحديات التقنية، والحلول التي تم تطبيقها.

---

## 1. الفكرة والهدف (The Concept)
الهدف كان بناء نظام مرن وشامل لإدارة أكواد الخصم (Coupons) يتيح لمدير المتجر إنشاء عروض ترويجية متنوعة، ويتيح للعملاء استخدام هذه الأكواد بسهولة أثناء عملية الدفع (Checkout).

**المتطلبات الرئيسية:**
*   **لوحة تحكم (Admin)**: واجهة لإدارة الكوبونات (إنشاء، تعديل، حذف).
*   **أنواع خصم متعددة**: نسبة مئوية (%)، مبلغ ثابت (Fixed)، وشحن مجاني (Free Shipping).
*   **استهداف دقيق**: إمكانية تحديد منتجات أو أقسام محددة يشملها الخصم، مع إمكانية **استبعاد** منتجات أو أقسام أخرى.
*   **قواعد الاستبعاد (Exclude Wins)**: إذا كان المنتج مشمولاً في فئة "مسموحة" ولكنه موجود في قائمة "المستبعدات"، فإن الاستبعاد هو الذي يطبق (الأقوى).
*   **الواجهة الأمامية (Checkout)**: دمج النظام مع صفحة الدفع للتحقق من الكود وتطبيق الخصم فورياً.

---

## 2. خطوات التنفيذ (Implementation Steps)

### أ. قاعدة البيانات (Database)
1.  استخدام جدول `discount_codes` الموجود مسبقاً.
2.  إضافة تحسينات (Migrations) لدعم الميزات الجديدة:
    *   `exclude_products` (JSON): لتخزين معرفات المنتجات المستبعدة.
    *   `exclude_categories` (JSON): لتخزين معرفات الأقسام المستبعدة.
    *   `discount_type`: إضافة خيار `free_shipping`.
    *   `internal_notes`: لملاحظات الإدارة.

### ب. المنطق البرمجي (Back-end Logic)
1.  **DiscountCode Model**:
    *   تحديث الـ `fillable` و `casts` (لتحويل حقول JSON إلى Arrays تلقائياً).
    *   إضافة `scopes` مفيدة (`active`, `valid`).
    *   تطبيق "Default Values" لبعض الأعمدة لتجنب أخطاء قاعدة البيانات.
2.  **CouponService**:
    *   إنشاء Service منفصل لفصل منطق العمل (Business Logic) عن الـ Controller/Livewire.
    *   دالة `validateCoupon`: تتحقق من الصلاحية (التاريخ، الحد الأدنى للطلب، عدد مرات الاستخدام، الاستبعادات).
    *   دالة `calculateDiscount`: تحسب قيمة الخصم بناءً على نوعه (ثابت، نسبة، شحن مجاني) وتطبق المنطق المعقد لاستثناء المنتجات غير المشمولة من حساب الخصم النسبي.

### ج. لوحة التحكم (Filament Resource)
1.  إنشاء `CouponResource` وإعداده في القائمة الجانبية تحت قسم "المبيعات".
2.  **Schema الفورم**:
    *   تقسيم الفورم إلى أقسام (بيانات أساسية، شروط، حدود استخدام، استهداف).
    *   استخدام `Select` مع `multiple` لاختيار المنتجات والأقسام (المشمولة والمستبعدة).
3.  **Code Generator**: محاولة إضافة زر لتوليد كود عشوائي، وتم الاستقرار على السماح بالإدخال اليدوي أو التوليد البسيط للحفاظ على واجهة نظيفة.

### د. صفحة الدفع (Checkout Integration)
1.  **CheckoutPage (Livewire)**:
    *   حقن `CouponService`.
    *   إضافة دوال `applyCoupon` و `removeCoupon`.
    *   تحديث حساب `total` ليطرح قيمة `couponDiscount`.
2.  **Blade Template**:
    *   إضافة واجهة لإدخال الكود (Input + Button).
    *   عرض رسائل النجاح (بالأخضر) والخطأ (بالأحمر).
    *   عرض سطر "الخصم" في ملخص الطلب.

---

## 3. الأخطاء والتحديات وطريقة حلها (Challenges & Solutions)

واجهنا عدة عقبات تقنية أثناء التنفيذ، وتم حلها كالتالي:

### 1. خطأ القيم الافتراضية (Default Value Error)
*   **الخطأ**: `SQLSTATE[HY000]: General error: 1364 Field 'commission_value' doesn't have a default value`
*   **السبب**: عند إنشاء كوبون جديد، لم نكن نرسل قيمة لحقل `commission_value` (الخاص بنظام المسوقين القديم)، وقاعدة البيانات لم يكن لها قيمة افتراضية.
*   **الحل**:
    *   حاولنا عمل Migration لتعديل العمود، لكن واجهنا مشاكل في Syntax الـ SQL الخاص بـ MySQL.
    *   **الحل النهائي**: قمنا بتعيين القيم الافتراضية (`attributes`) داخل الـ **Model** (`DiscountCode.php`) مباشرة:
      ```php
      protected $attributes = [
          'commission_value' => 0,
          'commission_type' => 'percentage',
          'times_used' => 0,
      ];
      ```

### 2. خطأ علاقات Filament (Select Relationship Error)
*   **الخطأ**: `Call to a member function getForeignKeyName() on null`
*   **السبب**: في `CouponForm.php`، استخدمنا دالة `->relationship('', 'name')` مع حقل `applies_to_categories`. هذا خطأ لأن هذا الحقل هو مجرد عمود `JSON` يخزن IDs، وليس علاقة `HasMany` أو `BelongsToMany` حقيقية في قاعدة البيانات.
*   **الحل**: إزالة دالة `->relationship()` والاكتفاء بـ `->options(...)` مع `->multiple()`:
    ```php
    Select::make('applies_to_categories')
        ->multiple()
        ->options(Category::pluck('name', 'id'))
        ...
    ```

### 3. خطأ نوع البيانات في الترجمة (Data Type Error in Checkout)
*   **الخطأ**: `htmlspecialchars(): Argument #1 ($string) must be of type string, array given`
*   **السبب**: قمنا بتغيير هيكل ملف الترجمة `messages.php` لمفتاح `currency` من نص `'ج.م'` إلى مصفوفة `['egp' => 'ج.م']` استعداداً لتعدد العملات، ولكن صفحة الـ Checkout كانت تستدعي `__('messages.currency')` مباشرة، مما أدى لخطأ لأنها تتوقع نصاً وليس مصفوفة.
*   **الحل**: أعدنا مفتاح `currency` إلى نوع **String** بسيط في ملفات اللغة (العربية والإنجليزية) لإصلاح الانهيار فوراً.

### 4. زر توليد الكود (Code Generation Button)
*   **التحدي**: في Filament 4، طريقة إضافة زر تفاعلي داخل `TextInput` (بواسطة `suffixAction`) تغيرت وتسببت في خطأ `Class not found`.
*   **الحل**: بسّطنا التصميم وأزلنا الزر المعقد، واعتمدنا على واجهة إدخال واضحة ونظيفة، حيث يمكن للمستخدم كتابة أي كود يريده.

---

## 4. الملفات التي تم العمل عليها (Files Modified)

*   `database/migrations/...` (تعديلات الجدول)
*   `app/Models/DiscountCode.php` (المنطق والخصائص)
*   `app/Services/CouponService.php` (منطق الخصم والتحقق)
*   `app/Filament/Resources/Coupons/...` (صفحات ولوحات التحكم)
*   `app/Livewire/Store/CheckoutPage.php` (كنترولر الدفع)
*   `resources/views/livewire/store/checkout-page.blade.php` (واجهة الدفع)
*   `lang/ar/messages.php` & `lang/en/messages.php` (الترجمات)

---

## الخلاصة
تم بحمد الله بناء نظام كوبونات قوي ومتكامل، جاهز للاستخدام الفعلي. النظام يدعم كافة سيناريوهات الخصم الشائعة (نسبة، ثابت، شحن مجاني) ومحمي من الأخطاء المنطقية والبرمجية، مع واجهة مستخدم سلسة سواء للمدير أو للعميل.
