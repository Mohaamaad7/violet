# 🌸 Violet — منصة تجارة إلكترونية عربية متكاملة

**Violet** ليست مجرد متجر إلكتروني — هي نظام متكامل لإدارة التجارة الإلكترونية، مصمم خصيصاً للسوق العربي مع دعم كامل للغة العربية والبوابات المصرية ونظام المؤثرين. مبنية على **Laravel 12.37** مع **Filament v4 Admin Panel** و **Livewire v3** التفاعلية.

| | |
|---|---|
| **المنصة** | Laravel 12.37 / PHP 8.3 / MySQL 8 |
| **لوحة الإدارة** | Filament v4.2 (Spatie Roles + Permissions) |
| **الواجهات التفاعلية** | Livewire v3.6 + Alpine.js + Tailwind CSS |
| **الـ UI** | متجاوب بالكامل، RTL/LTR، Dark Mode، Touch-ready |
| **الأداء** | PageSpeed 90+، LCP < 2.5s، Zero polyfills |
| **الأمان** | CSRF، RBAC، تشفير المفاتيح، سجل تدقيق |

> **الإنتاج:** [test.flowerviolet.com](https://test.flowerviolet.com)  
> **لوحة الإدارة:** [test.flowerviolet.com/admin](https://test.flowerviolet.com/admin)

---

## 🏗️ نظرة معمارية

```
                   ┌──────────────┐
                   │  المتجر (Store)│
                   │  Livewire v3  │
                   └──────┬───────┘
                          │
          ┌───────────────┼───────────────┐
          ▼               ▼               ▼
   ┌──────────┐    ┌──────────┐    ┌──────────────┐
   │ واجهة     │    │ لوحة      │    │ بوابة        │
   │ عملاء     │    │ إدارة    │    │ مؤثرين      │
   │ (Store)   │    │ (Filament)│    │ (Partners)  │
   └──────────┘    └──────────┘    └──────────────┘
          │               │               │
          └───────────────┼───────────────┘
                          ▼
                   ┌──────────────┐
                   │  Service Layer│
                   │  (Business    │
                   │   Logic)      │
                   └──────┬───────┘
                          ▼
                   ┌──────────────┐
                   │     MySQL    │
                   │    31 Tables │
                   └──────────────┘
```

---

## 🎯 مواصفات النظام كاملة

### 1. 🛍️ واجهة المتجر (Storefront)

#### الصفحات الرئيسية
| الصفحة | الوصف |
|--------|-------|
| **الرئيسية** | Hero Slider (Swiper.js)، منتجات مميزة، عروض، بانرات |
| **قائمة المنتجات** | شبكة منتجات مع فلاتر متقدمة، فرز، بحث |
| **صفحة المنتج** | معرض صور بتقنية Amazon-style zoom، سبيسيفيكيشن، تقييمات |
| **السلة** | AJAX بالكامل، تحديث فوري، كود خصم |
| **إتمام الطلب** | خطوة واحدة مع اختيار طريقة الدفع |
| **صفحة نجاح الطلب** | CTA للضيوف (إنشاء حساب / تتبع الطلب) |
| **حسابي** | لوحة تحكم عميل: طلباتي، المفضلة، العنوانين |
| **المدونة** | مقالات ببلوج |
| **من نحن** | صفحة ثابتة قابلة للتعديل من الإدارة |
| **اتصل بنا** | نموذج تواصل مع خريطة |
| **تقديم مؤثر** | نموذج تقديم متكامل مع السوشيال ميديا |

#### المكونات المتكررة
- **Header**: شريط علوي، لوجو ديناميكي، بحث لايف (AJAX)، سلة مع عداد، قائمة مفضلة، ميجا مينو
- **Footer**: روابط سريعة، نيوزليتر، أيقونات سوشيال ميديا
- **Product Card**: صورة WebP محسّنة، نجمة تقييم، Add to Cart، شارة الخصم، مؤشر المخزون، `fetchpriority="high"` للعناصر فوق الطيّ
- **Breadcrumbs**: فتات الخبز متجاوب مع الـ JSON-LD Schema
- **Language Switcher**: تبديل فوري بين العربية والإنجليزية

#### البحث المباشر (Live Search)
```
<input> → 300ms debounce → AJAX → JSON results (name, price, image, slug)
        → Dropdown منبثق مع نتائج وصور مصغرة
```

---

### 2. 🎛️ لوحة الإدارة (Filament Admin Panel)

#### إدارة المحتوى
| المورد | الميزات |
|--------|---------|
| **المنتجات** | CRUD كامل، صور متعددة مع Spatie Media Library، متغيرات (لون/مقاس)، SKU، باركود، حالة، مخزون، سعر/عرض |
| **الفئات** | هرمية (Parent/Child)، ترتيب Drag & Drop، أيقونات، صور |
| **السلآيدر** | صور متعددة، ترتيب، رابط، زر، إخفاء تلقائي على الموبايل |
| **البانرات** | مواضع متعددة (وسط/جانب/أسفل/منبثق)، تواريخ صلاحية |
| **المدونة** | محرر WYSIWYG RichEditor، صور مميزة، نشر/مسودة |
| **الصفحات** | صفحات ثابتة (من نحن، الشروط، الخصوصية) |
| **قوالب الإيميلات** | محرر Visual/HTML (Toggle)، 11 لون، 5 قوالب MJML جاهزة |
| **الترجمات** | نظام DB-backed مع Import/Export JSON، كاش، سجل تدقيق |

#### إدارة الطلبات
| المورد | الميزات |
|--------|---------|
| **الطلبات** | عرض/بحث/تصفية، تغيير الحالة (Pending → Processing → Shipped → Delivered → Cancelled)، تاريخ الحالات، إدارة المدفوعات |
| **الإرجاع** | طلبات إرجاع كاملة: إنشاء، موافقة، رفض، معالجة، استرجاع المخزون تلقائي، استرجاع العمولة |
| **حالات الطلب** | نظام Enum (PHP 8.3 Backed Enums) مع ألوان وتصنيف |
| **إشعارات الإرجاع** | 5 قوالب إيميل + إشعارات داخلية |

#### إدارة المؤثرين
| المورد | الميزات |
|--------|---------|
| **طلبات التقديم** | عرض/قبول/رفض مع سبب، إشعارات تلقائية |
| **المؤثرين** | إدارة كاملة: نسبة عمولة، رصيد، حسابات سوشيال ميديا، حالة |
| **أكواد الخصم** | لكل مؤثر كود خاص، خصم نسبة/قيمة، عمولة نسبة/قيمة، استخدامات محدودة |
| **العوْملات** | تسجيل تلقائي عند كل طلب مدفوع، تصفية، إجماليات |
| **طلبات الصرف** | إنشاء/موافقة/رفض، بنك/فودافون كاش/إنستاباي، أرقام مرجعية |

#### لوحة المعلومات (Dashboard)
- 10+ Widgets قابلة للتخصيص حسب الدور الوظيفي:
  - إيرادات اليوم، طلبات اليوم، إجمالي العملاء، المنتجات في المخزون
  - أقرب الطلبات، مخطط المبيعات، تنبيه المخزون المنخفض، طلبات الإرجاع المعلقة
  - قيمة المخزون، مخطط حركة المخزون
- **Zero-Config Role-Based Access**: لكل دور (Super Admin, Manager, Sales, Warehouse, Customer Service) رؤية مختلفة تلقائياً
- **Auto-Discovery**: الويدجتات الجديدة تُكتشف تلقائياً

#### النظام والإعدادات
| المورد | الميزات |
|--------|---------|
| **إعدادات الدفع** | Kashier أو Paymob (اختيار ديناميكي)، معاينة، اختبار اتصال |
| **إدارة الكاش** | مسح Response Cache / Application Cache / Blade Cache / الكل |
| **صلاحيات الأدوار** | Zero-Config: toggle لكل Widget/Resource/Page لكل Role |
| **سجل النشاط** | Spatie Activitylog مع تتبع كامل |
| **الإيميلات** | SMTP قابل للتعديل، معاينة القوالب، إرسال تجريبي |
| **السجلات** | Email Logs (حالة كل إيميل: sent/delivered/opened/clicked/failed) |

---

### 3. 🌟 بوابة المؤثرين (Partners Dashboard)

لوحة تحكم مخصصة للمؤثرين، منفصلة تماماً عن لوحة الإدارة.

**المسار:** `/partners/influencer-dashboard`

#### الميزات
- **Sidebar احترافية**: أيقونات Phosphor، عناوين مترجمة (عربي/إنجليزي)
- **إحصائيات فورية**: الرصيد الحالي، العمولات المعلقة، إجمالي الأرباح، المبيعات
- **أكواد الخصم**: عرض/نسخ (+ إشعار Clipboard)
- **جدول العمولات**: مرقم بألوان الحالة: Pending 🔶 Approved 🟢 Paid 🔵 Rejected 🔴
- **وضع الظلام**: متوافق مع Dark Mode بالكامل
- **متحرك (Responsive)**: سايدبار Overlay على الموبايل مع Burger Menu

#### الصفحات القادمة (Placeholder → قيد التطوير)
- Profile، Commissions، Discount Codes، Payouts (هيكل جاهز)
- إدارة الحساب البنكي
- طلبات صرف الأرباح

---

### 4. 💳 نظام الدفع

#### البوابات المدعومة
| البوابة | Visa/MC | Meeza | Vodafone Cash | InstaPay | Fawry/Kiosk | ValU |
|---------|---------|-------|---------------|----------|-------------|------|
| **Kashier** ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| **Paymob** 🆕 | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

#### المعمارية
```
PaymentGatewayInterface (Contract)
    ├── KashierGateway (موجود)
    └── PaymobGateway (جديد)

PaymentGatewayManager → يختار البوابة النشطة
PaymentService → CheckoutPage
```

#### الميزات
- بوابة واحدة نشطة في كل مرة (اختيار من الإدارة)
- إعدادات بوابة مشفرة في قاعدة البيانات
- Test/Live Modes
- HMAC Validation (Paymob)
- Callbacks و Webhooks منفصلة لكل بوابة
- Refund كامل/جزئي

---

### 5. 📧 نظام الإيميلات

#### القوالب الجاهزة (5 قوالب MJML → HTML)
| القالب | النوع | التصنيف |
|--------|-------|---------|
| تأكيد الطلب | Customer | Order |
| تحديث حالة الطلب | Customer | Order |
| رسالة ترحيب | Customer | Auth |
| استعادة كلمة المرور | Customer | Auth |
| إشعار طلب جديد | Admin | Order |
| إشعار قبول المؤثر | Customer | Influencer |
| إشعار رفض المؤثر | Customer | Influencer |
| إشعار عمولة | Customer | Influencer |
| إشعار صرف أرباح | Customer | Influencer |

#### الميزات
- محرر WYSIWYG مع Toggle Visual/HTML
- 11 لوناً جاهزاً مع أسماء عربية
- تتبع الحالة: Sent → Delivered → Opened → Clicked → Failed
- متغيرات ديناميكية: `{{ order_number }}`, `{{ user_name }}`, إلخ
- RTL/Dark Mode
- إرسال فوري (لا يحتاج Queue Worker)
- أمان تام: لا بيانات تخرج لطرف ثالث، القوالب محلية 100%

---

### 6. 👤 نظام العملاء والمستخدمين

#### أنواع المستخدمين
| النوع | الوصف |
|-------|-------|
| **Admin** | موظف الإدارة (Spatie Roles) |
| **Customer** | عميل المتجر (Guard منفصل) |
| **Influencer** | مؤثر (بوابة خاصة) |

#### ميزات العميل
- تسجيل/تسجيل دخول مع Guard مخصص (`auth:customer`)
- طلبات سابقة مع التتبع
- المفضلة (Wishlist)
- العنوانين (Address Book)
- مراجعات المنتجات
- ربط الطلبات كـ Guest بعد التسجيل (auto-fill email)

#### نظام الصلاحيات (RBAC)
- Spatie Laravel Permission v6
- 6 أدوار: Super Admin, Manager, Sales, Warehouse, Customer Service
- Zero-Config Dashboard: كل دور يرى فقط ما يخصه
- تخصيص Widgets/Resources/Pages لكل دور من واجهة واحدة
- Super Admin ي bypass جميع القيود

---

### 7. 🌐 نظام الترجمة DB-Backed

#### كيف يعمل
```
trans('messages.welcome')
    → CombinedLoader
        → DB Translation (active)
        → File Translation (lang/*.php)
        → Fallback Locale
        → Return key as-is
```

#### الميزات
- تعديل الترجمات من لوحة الإدارة بدون تعديل ملفات
- كاش لكل مفتاح (Redis-ready)
- استيراد/تصدير JSON
- سجل تدقيق (`updated_by`)
- تفعيل/تعطيل ترجمة بدون حذف
- اكتشاف اللغة: User → Cookie → Session → HTTP Header → App Default
- دعم Fluent: `trans_db('key', 'ar')`, `set_trans('key', 'ar', 'value')`

---

### 8. ⚡ تحسينات الأداء (PageSpeed 90+)

#### ما تم إنجازه
| التحسين | التأثير |
|---------|---------|
| **LCP Optimization** | `fetchpriority="high"` لأول 4 منتجات، WebP q70 مع Optimize |
| **Render-Blocking JS** | Facebook Pixel → Hybrid Delay (Interaction + 4s timeout) |
| **Render-Blocking CSS** | Font Awesome → `media="print" onload="this.media='all'"` |
| **Babel Polyfills** | Vite `build.target: es2020` → -8.6KB في `app.js` |
| **Image Delivery** | WebP tiered: Thumbnail/Card (q70+optimize) ↔ Preview (q95, no optimize) |
| **Caching Headers** | CSS/JS + Fonts → 1 year ExpiresByType + Cache-Control |
| **Livewire Bundle** | `inject_assets: false` → يمنع حقن `<script>` مكرر |
| **Touch Targets** | `min-h-[44px] min-w-[44px]` لجميع أزرار الموبايل |
| **Contrast** | `text-gray-500` → `text-gray-600` لـ WCAG AA Compliance |
| **Aria Labels** | 6 أزرار أيقونية + تسميات وصفية |

#### نتائج متوقعة
- **LCP:** ~2.5s (من 7.7s)
- **TBT:** < 50ms
- **Accessibility:** 95+
- **Performance:** 90+

---

### 9. 📱 دعم كامل للغة العربية (RTL)

- Cairo Font للعربية
- `dir="rtl"` ديناميكي حسب اللغة
- مرآة كاملة للـ Layout في RTL (sidebar يمين، أيقونات معكوسة)
- ألوان عربية (أسود، أحمر، برتقالي، أصفر، أخضر، أزرق، نيلي، بنفسجي، زهري، رمادي، أبيض)
- جميع الترجمات: `lang/ar/` + `lang/en/`
- ترجمة ديناميكية DB (تعديل بدون كود)

---

### 10. 🔒 الأمان

| الميزة | الحالة |
|--------|--------|
| CSRF Protection | ✅ على جميع النماذج |
| RBAC (Spatie Permission) | ✅ 6 أدوار + صلاحيات مخصصة |
| API Authentication (Sanctum) | ✅ |
| Guest Order Window (1 hour) | ✅ |
| مدخل بيانات مشفر (Payment Keys) | ✅ |
| Activity Log | ✅ Spatie Activitylog |
| Input Validation | ✅ على جميع الـ Forms |
| Soft Deletes | ✅ للمستخدمين، المنتجات، الفئات، المقالات |
| Nginx Security Headers | ✅ `.nginx.conf.example` جاهز |

---

### 11. 🗄️ قاعدة البيانات (31 جدولاً)

| المجموعة | الجداول |
|----------|---------|
| **المستخدمين** | users, roles, permissions, model_has_roles, model_has_permissions, role_has_permissions (6) |
| **المنتجات** | categories, products, product_images, product_variants, product_reviews, product_views (6) |
| **الطلبات** | orders, order_items, shipping_addresses, order_status_history, order_returns, return_items, return_policies, return_policy_items (8) |
| **المؤثرين** | influencers, influencer_applications, discount_codes, code_usages, influencer_commissions, commission_payouts (6) |
| **إضافية** | carts, cart_items, wishlists, notifications, settings, pages, blog_posts, sliders, banners (9) |
| **الإيميلات** | email_templates, email_logs (2) |

---

### 12. 🧪 الاختبارات

| النوع | العدد |
|-------|-------|
| **Feature Tests** | 21+ (Return workflows, Admin actions, Cache Manager, LCP verification) |
| **Integration Tests** | 13 (Stock restoration, Email notifications) |
| **Unit Tests** | 10 (Enum methods, Model casts, Translation service) |
| **Testing Framework** | PHPUnit 11 + Laravel RefreshDatabase (SQLite in-memory) |

---

### 13. 🚀 النشر والإنتاج

#### الخوادم المدعومة
- **Apache** (حالي) مع `.htaccess` محسّن للكاش
- **Nginx** → `.nginx.conf.example` جاهز مع:
  - Cache 1 year للملفات الثابتة
  - Gzip للـ fonts/CSS/JS
  - Security Headers (X-Frame-Options, X-Content-Type-Options, HSTS, Referrer-Policy)
  - Laravel Front Controller + PHP-FPM
  - HTTP → HTTPS Redirect

#### Deploy Checklist
1. `git pull`
2. `composer install --no-dev`
3. `php artisan migrate`
4. `npm run build` (Vite → es2020 target)
5. `php artisan optimize:clear`
6. `php artisan responsecache:clear` ← **حرجة جداً** بعد أي تعديل في الـ Views
7. `php artisan media:regenerate` ← لتحويل الصور لـ WebP

---

### 14. 🧩 خريطة الملفات الرئيسية

```
violet/
├── app/
│   ├── Contracts/
│   │   └── PaymentGatewayInterface.php
│   ├── Enums/
│   │   ├── OrderStatus.php
│   │   ├── ReturnStatus.php
│   │   └── ReturnType.php
│   ├── Filament/
│   │   ├── Pages/
│   │   │   ├── CacheManager.php
│   │   │   └── PaymentSettings.php
│   │   ├── Resources/
│   │   │   ├── Orders/
│   │   │   ├── Products/
│   │   │   ├── Influencers/
│   │   │   ├── EmailTemplates/
│   │   │   └── EmailLogs/
│   │   └── Widgets/
│   │       ├── StatsOverviewWidget.php
│   │       ├── RecentOrdersWidget.php
│   │       ├── SalesChartWidget.php
│   │       ├── PendingReturnsWidget.php
│   │       └── ... (10+ widgets)
│   ├── Livewire/
│   │   ├── Store/
│   │   │   ├── HeroSlider.php
│   │   │   ├── Home.php
│   │   │   ├── CheckoutPage.php
│   │   │   ├── ProductList.php
│   │   │   ├── FeaturedProducts.php
│   │   │   ├── BannersSection.php
│   │   │   └── Account/ ...
│   │   └── InfluencerApplicationForm.php
│   ├── Models/
│   │   ├── Product.php (Spatie Media Conversions)
│   │   ├── Order.php (Enum casts)
│   │   ├── Slider.php
│   │   ├── Influencer.php
│   │   ├── EmailTemplate.php
│   │   ├── EmailLog.php
│   │   └── ... (20+ models)
│   ├── Services/
│   │   ├── EmailService.php
│   │   ├── EmailTemplateService.php
│   │   ├── OrderService.php
│   │   ├── ReturnService.php
│   │   ├── CouponService.php
│   │   ├── InfluencerService.php
│   │   ├── TranslationService.php
│   │   ├── PaymentService.php
│   │   ├── PaymentGatewayManager.php
│   │   └── Gateways/
│   │       ├── KashierGateway.php
│   │       └── PaymobGateway.php
│   └── Providers/
│       ├── Filament/
│       │   ├── AdminPanelProvider.php
│       │   └── InfluencerPanelProvider.php
│       └── AppServiceProvider.php
├── config/
│   ├── livewire.php (inject_assets: false)
│   ├── responsecache.php
│   └── filament.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── store.blade.php
│       │   └── partners.blade.php
│       ├── components/
│       │   ├── store/
│       │   │   ├── header.blade.php (A11y + Touch Targets)
│       │   │   ├── footer.blade.php
│       │   │   ├── product-card.blade.php ($aboveFold prop)
│       │   │   └── breadcrumbs.blade.php
│       │   ├── store-layout.blade.php (Font Awesome non-blocking)
│       │   └── analytics/
│       │       └── facebook-pixel.blade.php (hybrid delay)
│       ├── livewire/
│       │   └── store/
│       │       ├── home.blade.php
│       │       ├── hero-slider.blade.php
│       │       ├── featured-products.blade.php
│       │       └── product-list.blade.php
│       └── emails/templates/
│           ├── order-confirmation.html
│           ├── order-status-update.html
│           ├── welcome.html
│           ├── password-reset.html
│           └── admin-new-order.html
├── lang/
│   ├── ar/
│   └── en/
├── public/
│   └── .htaccess (Caching Rules)
├── .nginx.conf.example
├── vite.config.js (target: es2020)
└── docs/
    └── CATALOG.md ← أنت هنا
```

---

## 🏆 خلاصة

Violet هو **أكثر من متجر إلكتروني** — هو منصة متكاملة تغطي كل حاجة:

- ✅ **تسوق سريع** مع PageSpeed 90+
- ✅ **إدارة كاملة** من Filament v4
- ✅ **نظام مؤثرين** متكامل مع عمولات وأكواد خصم
- ✅ **دفع مرن** مع Kashier و Paymob (ودعم معظم الطرق المصرية)
- ✅ **إيميلات احترافية** مع محرر WYSIWYG وتتبع
- ✅ **ترجمة ديناميكية** بدون لمس كود
- ✅ **صلاحيات محكمة** لكل دور وظيفي
- ✅ **عربي 100%** مع RTL ودعم كامل
- ✅ **جاهز للنشر** مع Apache و Nginx configurations

---

*آخر تحديث: يونيو 2026*  
*التقنيات: Laravel 12.37 · PHP 8.3 · Filament v4 · Livewire v3 · Alpine.js · Tailwind CSS · MySQL 8 · Redis*  

*توثيق شامل في `docs/` — 125+ ملفاً يغطون كل جوانب النظام.*
