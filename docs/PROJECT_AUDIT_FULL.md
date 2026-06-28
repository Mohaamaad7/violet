# تقرير الفحص الشامل — مشروع Violet E-Commerce

> **تاريخ الفحص:** 27 يونيو 2026  
> **المسار:** `c:\laragon\www\violet`  
> **الإنتاج (حسب التوثيق):** [test.flowerviolet.com](https://test.flowerviolet.com)  
> **الغرض:** فحص كامل للمشروع — المميزات، العيوب، الاقتراحات، والإحصائيات الدقيقة

---

## فهرس المحتويات

1. [ملخص تنفيذي](#1-ملخص-تنفيذي)
2. [نظرة عامة على المشروع](#2-نظرة-عامة-على-المشروع)
3. [البنية التقنية والتبعيات](#3-البنية-التقنية-والتبعيات)
4. [هيكل المشروع](#4-هيكل-المشروع)
5. [المميزات الكاملة — واجهة المتجر](#5-المميزات-الكاملة--واجهة-المتجر)
6. [المميزات الكاملة — المصادقة والعملاء](#6-المميزات-الكاملة--المصادقة-والعملاء)
7. [المميزات الكاملة — لوحة الإدارة Filament](#7-المميزات-الكاملة--لوحة-الإدارة-filament)
8. [المميزات الكاملة — بوابة المؤثرين Partners](#8-المميزات-الكاملة--بوابة-المؤثرين-partners)
9. [المميزات الكاملة — نظام الدفع](#9-المميزات-الكاملة--نظام-الدفع)
10. [المميزات الكاملة — المخزون والإرجاع](#10-المميزات-الكاملة--المخزون-والإرجاع)
11. [المميزات الكاملة — التسويق والبريد الإلكتروني](#11-المميزات-الكاملة--التسويق-والبريد-الإلكتروني)
12. [المميزات الكاملة — الترجمة والمحتوى](#12-المميزات-الكاملة--الترجمة-والمحتوى)
13. [المميزات الكاملة — التحليلات والأداء](#13-المميزات-الكاملة--التحليلات-والأداء)
14. [المميزات الكاملة — الأمان والصلاحيات](#14-المميزات-الكاملة--الأمان-والصلاحيات)
15. [المميزات الكاملة — API والتكاملات](#15-المميزات-الكاملة--api-والتكاملات)
16. [قاعدة البيانات — الجداول والنماذج والعلاقات](#16-قاعدة-البيانات--الجداول-والنماذج-وعلاقات)
17. [المسارات الكاملة Routes](#17-المسارات-الكاملة-routes)
18. [طبقة الخدمات Services](#18-طبقة-الخدمات-services)
19. [الواجهة الأمامية Frontend](#19-الواجهة-الأمامية-frontend)
20. [الاختبارات Test Coverage](#20-الاختبارات-test-coverage)
21. [التوثيق الموجود](#21-التوثيق-الموجود)
22. [العيوب والمشاكل — مفصّلة](#22-العيوب-والمشاكل--مفصّلة)
23. [الميزات الناقصة أو غير المكتملة](#23-الميزات-الناقصة-أو-غير-المكتملة)
24. [اقتراحات الإضافة والتحسين](#24-اقتراحات-الإضافة-والتحسين)
25. [إحصائيات المشروع](#25-إحصائيات-المشروع)
26. [خطة الإطلاق Production Checklist](#26-خطة-الإطلاق-production-checklist)

---

## 1. ملخص تنفيذي

**Violet** منصة تجارة إلكترونية عربية متكاملة (Full-Stack E-Commerce) مبنية على **Laravel 12** مع **Filament v4** للإدارة و **Livewire v3** للواجهات التفاعلية. المشروع **ناضج وواسع النطاق** (~1071 ملفاً في المستودع) ويغطي دورة حياة التجارة الإلكترونية من التصفح حتى الدفع والإرجاع والعمولات.

### نقاط القوة الرئيسية
- فصل واضح بين **العملاء** (`customers` guard) و**الموظفين** (`users` / Filament)
- نظام **مؤثرين** متكامل (تقديم → موافقة → أكواد → عمولات → صرف)
- **3 بوابات دفع مصرية** (Kashier, Paymob, Fawry) بمعمارية قابلة للتوسع
- **مخزون متقدم** (مستودعات، حركات، جرد، دفعات)
- **تخصيص لوحة المعلومات** حسب الدور (Zero-Config RBAC)
- **ترجمة ديناميكية** من قاعدة البيانات
- **173 ملف توثيق** تقني

### نقاط تحتاج اهتماماً
- **المدونة (Blog)** — جدول + Model موجودان، لكن **لا CRUD في الإدارة ولا واجهة أمامية**
- **WhatsApp / SMS** — مذكوران في الخطط، **غير منفّذين**
- **OrderObserver معطّل** — حلقة لا نهائية
- **مسارات Debug** مفتوحة في `web.php`
- **Sanctum** مثبت لكن **غير مستخدم**
- **README.md** تالف/مدمج مع Laravel boilerplate
- **CATALOG.md** يحتوي معلومات **قديمة/غير دقيقة** (31 جدولاً بدل 60+، المدونة كميزة مكتملة، Sanctum كميزة فعّالة)

---

## 2. نظرة عامة على المشروع

| البند | التفاصيل |
|-------|----------|
| **النوع** | E-Commerce Platform (B2C) |
| **السوق المستهدف** | العربي / المصري (EGP، بوابات دفع مصرية، محافظات مصر) |
| **اللغات** | العربية (افتراضي) + الإنجليزية |
| **الاتجاه** | RTL/LTR ديناميكي |
| **البيئات** | Local (SQLite/Laragon) → Production (MySQL) |
| **الواجهات** | Storefront + Admin Panel + Partners Panel |

### المعمارية العامة

```
┌─────────────────────────────────────────────────────────────┐
│                     العميل (Browser)                        │
└──────────────┬──────────────────────┬───────────────────────┘
               │                      │
       ┌───────▼────────┐    ┌────────▼────────┐    ┌────────▼────────┐
       │  Storefront    │    │  Admin Panel    │    │ Partners Panel  │
       │  Livewire v3   │    │  Filament v4    │    │  Filament v4    │
       │  /             │    │  /admin         │    │  /partners      │
       └───────┬────────┘    └────────┬────────┘    └────────┬────────┘
               │                      │                      │
               └──────────────────────┼──────────────────────┘
                                      ▼
                            ┌──────────────────┐
                            │  Service Layer   │
                            │  26 Service      │
                            └────────┬─────────┘
                                     ▼
                            ┌──────────────────┐
                            │  Eloquent ORM    │
                            │  55 Model        │
                            │  101 Migration   │
                            └────────┬─────────┘
                                     ▼
                            ┌──────────────────┐
                            │  MySQL / SQLite  │
                            └──────────────────┘
```

---

## 3. البنية التقنية والتبعيات

### Backend (composer.json)

| الحزمة | الإصدار | الغرض |
|--------|---------|-------|
| PHP | ^8.2 | لغة التشغيل |
| Laravel Framework | ^12.0 | الإطار الأساسي |
| Filament | ^4.2 | لوحة الإدارة |
| filament/spatie-laravel-media-library-plugin | ^4.2 | رفع وسائط المنتجات |
| filament/tables | ^4.2 | جداول Filament |
| Livewire | ^3.6.4 | مكونات تفاعلية |
| Livewire Volt | ^1.7.0 | صفحات Auth |
| spatie/laravel-permission | ^6.0 | RBAC |
| spatie/laravel-activitylog | ^4.0 | سجل التدقيق |
| spatie/laravel-medialibrary | ^11.17 | إدارة الوسائط + WebP |
| spatie/laravel-responsecache | ^7.7 | كاش الصفحات العامة |
| spatie/laravel-backup | ^9.3 | نسخ احتياطي |
| spatie/laravel-analytics | ^5.7 | Google Analytics |
| laravel/sanctum | ^4.0 | **مثبت — غير مستخدم** |
| maatwebsite/excel | ^3.1 | استيراد/تصدير Excel |
| pxlrbt/filament-excel | ^3.3 | تصدير Filament |
| mpdf/mpdf | ^8.2 | تقارير PDF (جرد المخزون) |
| intervention/image-laravel | ^1.5 | معالجة الصور |
| doctrine/dbal | ^4.4 | تعديلات Schema |

### Dev Dependencies

| الحزمة | الغرض |
|--------|-------|
| laravel/breeze | قوالب Auth |
| barryvdh/laravel-debugbar | Debug في التطوير |
| laravel/pint | تنسيق الكود |
| laravel/pail | عرض Logs |
| laravel/sail | Docker |
| phpunit/phpunit ^11.5.3 | الاختبارات |

### Frontend (package.json)

| الحزمة | الإصدار | الغرض |
|--------|---------|-------|
| Vite | ^7.0.7 | Build tool |
| Tailwind CSS | ^4.1.17 | CSS framework |
| @tailwindcss/vite | ^4.1.17 | تكامل Vite |
| Alpine.js | ^3.15.1 | تفاعلية (عبر Livewire) |
| Swiper | ^12.0.3 | Hero Slider |
| drift-zoom | ^1.5.1 | تكبير صور المنتج |
| spotlight.js | ^0.7.8 | Lightbox المعرض |
| axios | ^1.11.0 | HTTP requests |

### Scripts Composer

- `composer setup` — تثبيت كامل (composer + migrate + npm build)
- `composer dev` — تشغيل server + queue + logs + vite concurrently
- `composer test` — تشغيل PHPUnit

---

## 4. هيكل المشروع

```
violet/
├── app/
│   ├── Console/Commands/          (5 أوامر Artisan)
│   ├── Contracts/                 (PaymentGatewayInterface)
│   ├── Enums/                     (7 enums)
│   ├── Exports/                   (2 ملف)
│   ├── Filament/
│   │   ├── Pages/                 (11 صفحة مخصصة)
│   │   ├── Partners/Pages/        (5 صفحات المؤثرين)
│   │   ├── Resources/             (35 Filament Resource)
│   │   ├── Traits/                (ExportableTable...)
│   │   └── Widgets/               (26 widget)
│   ├── Http/
│   │   ├── Controllers/           (16 controller)
│   │   └── Middleware/            (7 middleware)
│   ├── Imports/                   (2 ملف)
│   ├── Jobs/                      (3 jobs)
│   ├── Livewire/                  (36 component)
│   ├── Mail/                      (TemplateMail, CampaignMail)
│   ├── Models/                    (55 model)
│   ├── Notifications/             (5 إشعارات)
│   ├── Observers/                 (OrderObserver — معطّل)
│   ├── Policies/                  (7 policies)
│   ├── Providers/                 (App, Filament x2, ActivityLog...)
│   ├── Services/                  (26 service + Gateways/)
│   └── Traits/                    (HasFullAudit...)
├── bootstrap/
├── config/                        (16 ملف)
├── database/
│   ├── factories/
│   ├── migrations/                (101 migration)
│   └── seeders/                   (21 seeder)
├── docs/                          (173 ملف)
├── lang/                          (ar/, en/, vendor/)
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/                     (127+ blade)
├── routes/
│   ├── web.php
│   ├── auth.php
│   └── console.php
├── storage/
├── tests/                         (29 ملف)
└── vendor/
```

---

## 5. المميزات الكاملة — واجهة المتجر

### 5.1 الصفحات والمسارات

| المسار | المكون/Controller | الميزات |
|--------|-------------------|---------|
| `/` | `Livewire\Store\Home` | Hero Slider، منتجات مميزة، بانرات، Response Cache |
| `/products` | `ProductsController@index` | قائمة منتجات مع فلاتر وفرز |
| `/products/{slug}` | `ProductDetailsController@show` | تفاصيل، معرض صور، reviews |
| `/categories/{slug}` | `Livewire\Store\CategoryShow` | منتجات حسب الفئة |
| `/offers` | `Livewire\Store\OffersPage` | صفحة العروض |
| `/cosmetics` | `Livewire\Cosmetics\HomePage` | Landing theme منفصل للتجميل |
| `/cart` | `Livewire\Store\CartPage` | سلة AJAX، كود خصم |
| `/checkout` | `Livewire\Store\CheckoutPage` | Guest + Auth، اختيار عنوان ودفع |
| `/checkout/success/{order}` | `OrderSuccessPage` | نجاح الطلب، CTA للضيوف |
| `/track-order` | `Livewire\Store\TrackOrder` | تتبع طلب للضيوف |
| `/wishlist` | `WishlistPage` | يتطلب `auth:customer` |
| `/about` | Blade static | من نحن |
| `/contact` | Blade + `ContactForm` | نموذج تواصل |
| `/page/{slug}` | `StaticPage` | صفحات CMS ديناميكية |
| `/{slug}` (fallback) | `StaticPage` | terms, privacy, returns, cookies, shipping, faq, help |
| `/influencer/apply` | `InfluencerApplicationForm` | تقديم مؤثر |
| `/newsletter/unsubscribe/{token}` | `NewsletterController` | إلغاء اشتراك |

### 5.2 مكونات Livewire للمتجر (36 مكوناً)

**الصفحات الرئيسية:**
- `Home`, `HeroSlider`, `FeaturedProducts`, `BannersSection`
- `ProductList`, `ProductDetails`, `ProductReviews`
- `CategoryShow`, `OffersPage`
- `CartPage`, `CartManager`, `CheckoutPage`, `OrderSuccessPage`
- `WishlistPage`, `WishlistButton`, `WishlistCounter`
- `SearchBar` — بحث مباشر debounced 300ms
- `NewsletterSubscription`
- `StaticPage`, `TrackOrder`, `ContactForm`

**حساب العميل (`Account/`):**
- `Dashboard`, `Profile`, `Addresses`, `Orders`, `OrderDetails`, `MyReviews`

**ثيم Cosmetics:**
- `Cosmetics\HomePage`

**أخرى:**
- `InfluencerApplicationForm`
- `Filament\TopbarLanguages` — تبديل لغة في الإدارة
- `Admin\Dashboard`, `Admin\ProductImageGallery`, `Admin\Categories\Index`

### 5.3 مكونات UI المتكررة

| المكون | الميزات |
|--------|---------|
| **Header** | Logo ديناميكي، Live Search، عداد سلة، Wishlist، Mega Menu، Language Switcher |
| **Footer** | روابط، Newsletter، Social Media |
| **Product Card** | WebP، تقييم، Add to Cart، شارة خصم، مؤشر مخزون، `fetchpriority="high"` |
| **Breadcrumbs** | JSON-LD Schema |
| **Product Gallery** | Drift Zoom (hover) + Spotlight (lightbox)، إصلاحات RTL |

### 5.4 السلة (Cart)

- **CartService** — منطق الأعمال
- **CartManager** — Livewire component للتحديث الفوري
- دعم **Guest Cart** (Session) + **Authenticated Cart** (DB)
- **MergeCartOnLogin** — دمج السلة عند تسجيل الدخول
- AJAX كامل بدون reload

### 5.5 Checkout

- Guest checkout (بدون حساب)
- Authenticated checkout (عناوين محفوظة)
- اختيار طريقة الدفع: COD, Card, InstaPay, Wallet, Kiosk
- كود خصم (CouponService)
- خصم الشحن (Shipping Discount Settings)
- ربط الطلب بـ Customer تلقائياً عند Login

### 5.6 Response Cache

- Spatie ResponseCache على الصفحات العامة (Home, Products, Offers, Cosmetics, Product Details)
- `CacheManager` في الإدارة لمسح الكاش

---

## 6. المميزات الكاملة — المصادقة والعملاء

### 6.1 Guards المنفصلة

| Guard | Model | الاستخدام |
|-------|-------|-----------|
| `customer` | `Customer` | عملاء المتجر |
| `web` | `User` | موظفو الإدارة + المؤثرون |

### 6.2 مصادقة العملاء (routes/auth.php — Livewire Volt)

| المسار | الميزة |
|--------|--------|
| `/register` | تسجيل حساب جديد |
| `/login` | تسجيل دخول |
| `/forgot-password` | طلب استعادة كلمة المرور |
| `/reset-password/{token}` | **تعارض:** Volt + CustomerPasswordResetController في web.php |
| `/auth/google` | Google OAuth redirect |
| `/auth/google/callback` | Google OAuth callback |
| `/verify-email` | التحقق من البريد |
| `/confirm-password` | تأكيد كلمة المرور |

### 6.3 Google OAuth

- `GoogleController` — Social login للعملاء
- إعدادات: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`

### 6.4 حماية العملاء

- **EnsureCustomerIsActive** — منع العملاء المحظورين
- **Email Verification** — Breeze-style
- **Password Reset** — جدول `customer_password_reset_tokens` منفصل

### 6.5 منطقة حساب العميل (`/account/*`)

| الصفحة | الميزات |
|--------|---------|
| Dashboard | ملخص الطلبات والنشاط |
| Profile | تعديل البيانات الشخصية |
| Addresses | Address Book (CRUD) |
| Orders | قائمة الطلبات |
| Order Details | تفاصيل طلب واحد |
| Reviews | مراجعاتي للمنتجات |

### 6.6 Wishlist

- **WishlistService** — Add/Remove/Toggle
- عداد في Header (`WishlistCounter`)
- زر في بطاقة المنتج (`WishlistButton`)

### 6.7 المراجعات (Reviews)

- **ReviewService** — Verified Purchase reviews
- `ProductReviews` Livewire component
- `MyReviews` في حساب العميل

---

## 7. المميزات الكاملة — لوحة الإدارة Filament

**المسار:** `/admin`  
**Provider:** `AdminPanelProvider`  
**الخط:** Cairo | **اللون:** Violet | **Theme:** Vite custom CSS

### 7.1 Filament Resources (35 مورداً)

#### المحتوى والمنتجات
| Resource | الميزات |
|----------|---------|
| **ProductResource** | CRUD، variants، Spatie Media (WebP)، Excel import/export، low/out-of-stock views |
| **CategoryResource** | هرمي (parent/child)، Drag & Drop، أيقونات |
| **BannerResource** | مواضع متعددة، scheduling |
| **SliderResource** | Hero slider، mobile image |
| **PageResource** | CMS صفحات ثابتة |
| **HelpEntryResource** | مركز المساعدة |
| **TranslationResource** | ترجمات DB، import/export JSON |
| **SettingResource** | Key-value (logo, Facebook Pixel, policies...) |

#### الطلبات والمدفوعات
| Resource | الميزات |
|----------|---------|
| **OrderResource** | workflow حالات، history، stock integration |
| **OrderReturnResource** | إرجاع كامل lifecycle |
| **PaymentResource** | سجلات المدفوعات |
| **CouponResource** | أكواد خصم (general/influencer/campaign) |

#### العملاء والمستخدمين
| Resource | الميزات |
|----------|---------|
| **CustomerResource** | CRUD، wishlist view، send email action، reset password |
| **UserResource** | إدارة الموظفين |
| **RoleResource** | Spatie roles |
| **PermissionResource** | Spatie permissions |

#### المؤثرين
| Resource | الميزات |
|----------|---------|
| **InfluencerApplicationResource** | قبول/رفض workflow |
| **InfluencerResource** | profiles، social stats، commission |
| **CommissionPayoutResource** | bank/vodafone/instapay payouts |

#### المخزون
| Resource | الميزات |
|----------|---------|
| **WarehouseResource** | هرمي (parent_id) |
| **StockMovementResource** | audit trail |
| **StockCountResource** | جرد + PDF reports |
| **LowStockProductResource** | منتجات مخزون منخفض |
| **OutOfStockProductResource** | منتجات نفد مخزونها |

#### التسويق والبريد
| Resource | الميزات |
|----------|---------|
| **EmailTemplateResource** | WYSIWYG/HTML، MJML |
| **EmailLogResource** | sent/delivered/opened/clicked/failed |
| **EmailCampaignResource** | حملات bulk via queue |
| **NewsletterSubscriptionResource** | إدارة المشتركين |

#### الموقع الجغرافي
| Resource | الميزات |
|----------|---------|
| **CountryResource** | الدول |
| **GovernorateResource** | المحافظات + delivery_days |
| **CityResource** | المدن |

#### تخصيص Dashboard
| Resource | الميزات |
|----------|---------|
| **WidgetConfigurationResource** | إعداد Widgets |
| **ResourceConfigurationResource** | إعداد Resources |
| **NavigationGroupConfigurationResource** | مجموعات التنقل |

### 7.2 صفحات Filament المخصصة (11)

| الصفحة | الغرض |
|--------|-------|
| **AnalyticsDashboard** | Google Analytics widgets |
| **AnalyticsSettings** | إعداد GA (G-XXXXXXXXXX) |
| **SalesReport** | تقارير مبيعات |
| **CacheManager** | مسح Response/App/Blade cache |
| **BackupManager** | Spatie backup |
| **PaymentSettings** | إعداد بوابات الدفع (DB-driven) |
| **ShippingDiscountSettingsPage** | خصم الشحن |
| **RolePermissions** | Zero-Config RBAC toggles |
| **HelpCenter** | مركز مساعدة |
| **SystemReset** | reset انتقائي (super-admin only) |
| **BasePage** | صفحة أساس |

### 7.3 Widgets (26)

| Widget | الغرض |
|--------|-------|
| TodayRevenueWidget | إيرادات اليوم |
| NewOrdersTodayWidget | طلبات اليوم |
| TotalCustomersWidget | إجمالي العملاء |
| ProductsInStockWidget | منتجات في المخزون |
| TotalStockUnitsWidget | وحدات المخزون |
| StockValueWidget | قيمة المخزون |
| PotentialProfitWidget | ربح محتمل |
| LowStockAlertWidget | تنبيه مخزون منخفض |
| OutOfStockWidget | نفاد المخزون |
| CurrentOrdersWidget | الطلبات الحالية |
| RecentOrdersWidget | أحدث الطلبات |
| SalesChartWidget | مخطط المبيعات |
| StockMovementsChartWidget | مخطط حركة المخزون |
| PendingReturnsWidget | إرجاعات معلقة |
| ApprovedReturnsWidget | إرجاعات موافق عليها |
| MonthlyReturnsWidget | إرجاعات شهرية |
| AnalyticsVisitorsWidget | زوار Analytics |
| AnalyticsTopPagesWidget | أهم الصفحات |
| AnalyticsTopCountriesWidget | أهم الدول |
| AnalyticsTopReferrersWidget | مصادر الزيارات |
| SalesReportStatsWidget | إحصائيات تقرير المبيعات |
| StatsOverviewWidget | **@deprecated** — مقسّم لـ 4 widgets |
| BaseWidget, BaseStatsWidget, BaseChartWidget, BaseTableWidget | Classes أساس |

### 7.4 Zero-Config Dashboard RBAC

- **DashboardConfigurationService** — فلترة تلقائية
- **ApplyDashboardConfiguration** + **EnforcePageAccess** middleware
- Commands:
  - `dashboard:discover` — اكتشاف widgets/resources/pages
  - `dashboard:sync-roles` — مزامنة الأدوار
  - `dashboard:reset-user` — reset تفضيلات مستخدم
- جداول: `widget_configurations`, `user_widget_preferences`, `role_widget_defaults`, `resource_configurations`, `role_resource_access`, `role_page_access`, `navigation_group_configurations`, `role_navigation_groups`

### 7.5 أدوار Staff (Spatie Permission)

| الدور | الصلاحيات الرئيسية |
|-------|-------------------|
| **super-admin** | كل الصلاحيات + bypass |
| **admin** | معظم الصلاحيات |
| **manager** | منتجات، طلبات، تقارير |
| **sales** | عرض منتجات، إدارة حالة الطلب |
| **accountant** | طلبات، عمولات، صرف، تقارير |
| **content-manager** | منتجات، محتوى، blog، pages |
| **delivery** | مذكور في User model و DashboardSyncRoles |
| **customer** | دور فارغ (بدون صلاحيات admin) |
| **influencer** | يُعيَّن عند إنشاء مؤثر — للـ Partners panel |

**Permissions (35+):** view/create/edit/delete products, categories, orders, users, roles, permissions, influencers, discount codes, content, settings, reports...

### 7.6 Excel Import/Export للمنتجات

- **ProductTemplateExport** — قالب create/update
- **ProductImport** + **ProductImportValidator**
- Routes: `/admin/products/download-template`
- Filament Excel export via `pxlrbt/filament-excel`

### 7.7 Product Image Management

- Routes منفصلة: upload, set-primary, destroy, update-order
- **ProductImageUploader** service
- Spatie Media Library + WebP conversions
- **ProcessProductImage** job

---

## 8. المميزات الكاملة — بوابة المؤثرين Partners

**المسار:** `/partners`  
**Provider:** `InfluencerPanelProvider`  
**Guard:** `web` (User + role `influencer`)

### 8.1 الصفحات (5 — **مكتملة، ليس placeholder**)

| الصفحة | الميزات |
|--------|---------|
| **InfluencerDashboard** | إحصائيات: رصيد، عمولات معلقة، إجمالي أرباح، مبيعات |
| **ProfilePage** | الملف الشخصي + تحديث كلمة المرور |
| **DiscountCodesPage** | عرض/نسخ أكواد الخصم |
| **CommissionsPage** | جدول العمولات بألوان الحالة |
| **PayoutsPage** | طلبات الصرف |

### 8.2 دورة حياة المؤثر

```
/influencer/apply (نموذج عام)
    → InfluencerApplication (pending)
    → Admin: قبول/رفض
    → إنشاء User + Influencer + DiscountCode
    → إشعارات: ApplicationApproved/Rejected, InfluencerInvitation
    → Partners Panel: /partners
    → عمولة تلقائية عند طلب مدفوع (InfluencerCommission)
    → طلب صرف (CommissionPayout)
    → Admin: موافقة/رفض
    → PayoutProcessedNotification
```

### 8.3 الإشعارات (5)

- `ApplicationApprovedNotification`
- `ApplicationRejectedNotification`
- `InfluencerInvitationNotification`
- `CommissionEarnedNotification`
- `PayoutProcessedNotification`

### 8.4 API تحديث كلمة المرور

- `POST /partners/profile/update-password` — inline route في web.php (validation يدوي)

---

## 9. المميزات الكاملة — نظام الدفع

### 9.1 البوابات المدعومة

| البوابة | Callback | Webhook | Service/Gateway |
|---------|----------|---------|-----------------|
| **Kashier** | GET `/payment/kashier/callback` | POST `/payment/kashier/webhook` | `KashierGateway`, `KashierService` |
| **Paymob** | GET/POST `/payment/paymob/callback` | GET/POST `/payment/paymob/webhook` | `PaymobGateway` |
| **Fawry** | GET/POST `/payment/fawry/callback` | POST `/payment/fawry/webhook` | `FawryGateway` |

### 9.2 المعمارية

```
PaymentGatewayInterface (Contract)
    ├── KashierGateway
    ├── PaymobGateway
    └── FawryGateway

PaymentGatewayManager → اختيار البوابة النشطة من payment_settings
PaymentService → منطق Checkout
PaymentController → callbacks/webhooks
```

### 9.3 طرق الدفع

- **COD** — الدفع عند الاستلام
- **Card** — Visa/Mastercard/Meeza
- **Wallet** — Vodafone Cash وغيرها
- **InstaPay**
- **Kiosk** — Fawry

### 9.4 الميزات

- بوابة واحدة نشطة (DB-driven `payment_settings`)
- Test/Live modes
- HMAC Validation (Paymob)
- CSRF معطّل على webhooks (مقصود)
- Throttle: 5 requests/minute على `/payment/process`
- Legacy routes للتوافق العكسي (@deprecated)
- Refund كامل/جزئي (documented)
- صفحات success/failed/select method

### 9.5 جدول Payments

- Model: `Payment`
- Resource: `PaymentResource`
- ربط بـ Order

---

## 10. المميزات الكاملة — المخزون والإرجاع

### 10.1 المخزون

| الكيان | الميزات |
|--------|---------|
| **Warehouses** | هرمي، parent_id |
| **Batches** | تتبع الدفعات (BatchService) |
| **StockMovements** | audit trail (StockMovementService) |
| **StockCounts** | جرد فيزيائي + PDF (StockCountService, PdfService) |

**Stock Count Reports (PDF):**
- `/admin/stock-counts/{id}/count-sheet`
- `/admin/stock-counts/{id}/results`
- `/admin/stock-counts/{id}/shortage`
- `/admin/stock-counts/{id}/excess`

### 10.2 Enums المخزون

- `StockCountStatus`, `StockCountType`, `StockCountScope`, `VarianceReasonType`

### 10.3 الإرجاع (Returns)

| Enum/Model | القيم |
|------------|-------|
| **ReturnStatus** | pending, approved, rejected, processing, completed |
| **ReturnType** | refund, exchange |
| **OrderReturn** + **ReturnItem** | lifecycle كامل |

**ReturnService:**
- إنشاء طلب إرجاع
- موافقة/رفض
- استرجاع المخزون تلقائياً
- استرجاع العمولة
- إيميلات: return-request-received, return-approved, return-rejected, return-completed

### 10.4 Order Status Workflow

| الحالة | القيمة | الوصف |
|--------|--------|-------|
| PENDING | 0 | قيد الانتظار |
| PROCESSING | 1 | قيد التجهيز |
| SHIPPED | 2 | تم الشحن |
| DELIVERED | 3 | تم التسليم |
| CANCELLED | 4 | ملغي |
| REJECTED | 5 | مرفوض |
| PENDING_PAYMENT | 6 | في انتظار الدفع |

### 10.5 Order Stock Flow

- خصم مخزون عند تأكيد الطلب
- استرجاع عند الإلغاء/الإرجاع
- **OrderStockFlowTest** — 11 test
- **OrderObserver معطّل** — كان يسبب infinite loop

---

## 11. المميزات الكاملة — التسويق والبريد الإلكتروني

### 11.1 Email Templates

- **EmailTemplateResource** — WYSIWYG/HTML toggle
- 11 لون جاهز
- MJML API support (`config/services.php`)
- متغيرات ديناميكية: `{{ order_number }}`, `{{ user_name }}`...

### 11.2 قوالب HTML جاهزة (10)

| القالب | الغرض |
|--------|-------|
| order-confirmation.html | تأكيد الطلب |
| order-status-update.html | تحديث حالة |
| welcome.html | ترحيب |
| password-reset.html | استعادة كلمة مرور |
| admin-new-order.html | إشعار admin |
| admin-new-return.html | إرجاع جديد |
| return-request-received.html | استلام طلب إرجاع |
| return-approved.html | موافقة إرجاع |
| return-rejected.html | رفض إرجاع |
| return-completed.html | إكمال إرجاع |

### 11.3 Email Campaigns

- **EmailCampaignResource**
- **ProcessEmailCampaign** job
- **SendCampaignEmail** job
- **CampaignMail**
- Campaign offers + logs (`campaign_offers`, `campaign_logs`)

### 11.4 Newsletter

- **NewsletterSubscription** Livewire component (Store)
- **NewsletterSubscriptionResource** (Admin)
- Subscribe + Unsubscribe token
- **⚠️ Cosmetics newsletter banner** — TODO غير منفّذ

### 11.5 Email Logs

- تتبع: sent → delivered → opened → clicked → failed
- **EmailLogResource**

### 11.6 Facebook Pixel

- Dynamic ID من Settings
- Deferred loading (Hybrid Delay)
- توثيق: `docs/FACEBOOK_PIXEL_INTEGRATION.md`

---

## 12. المميزات الكاملة — الترجمة والمحتوى

### 12.1 نظام الترجمة DB-Backed

```
trans('messages.welcome')
    → CombinedLoader
        → DB Translation (active)
        → File Translation (lang/*.php)
        → Fallback Locale
        → Return key
```

**TranslationService + TranslationResource:**
- تعديل من الإدارة بدون كود
- Import/Export JSON
- Cache per key
- Audit (`updated_by`)
- Activate/deactivate
- Helpers: `trans_db()`, `set_trans()`

**Commands:** `TestTranslations`  
**Middleware:** `SetLocale` — User → Cookie → Session → Header → Default

### 12.2 ملفات اللغة

- `lang/ar/` — messages, admin, about...
- `lang/en/` — نفس الهيكل
- Seeders: `FrontendTranslationsSeeder`, `AdminTranslationsSeeder`, `InfluencerTranslationsSeeder`, `InventoryTranslationsSeeder`

### 12.3 CMS Pages

- **PageResource** — صفحات ديناميكية
- **StaticPage** Livewire — `/page/{slug}`
- Seeders: `TermsPageSeeder`, `ReturnsPageSeeder`

### 12.4 المدونة (Blog) — ⚠️ غير مكتملة

| موجود | غير موجود |
|--------|-----------|
| Migration `blog_posts` | Filament BlogPostResource |
| Model `BlogPost` (scopes, soft deletes) | Frontend routes |
| Permission `manage blog` | Views |
| Footer link (مذكور في CATALOG) | Tests |

---

## 13. المميزات الكاملة — التحليلات والأداء

### 13.1 Google Analytics

- Spatie Laravel Analytics
- **AnalyticsDashboard** + **AnalyticsSettings**
- Widgets: Visitors, Top Pages, Top Countries, Top Referrers

### 13.2 تحسينات الأداء

| التحسين | التفاصيل |
|---------|----------|
| LCP | `fetchpriority="high"` لأول 4 منتجات |
| WebP | Spatie Media conversions (q70 card, q95 preview) |
| Facebook Pixel | Hybrid Delay (interaction + 4s timeout) |
| Font Awesome | `media="print" onload="this.media='all'"` |
| Vite | `build.target: es2020` — بدون Babel polyfills |
| Response Cache | Spatie على الصفحات العامة |
| Livewire | `inject_assets: false` |
| Touch Targets | `min-h-[44px] min-w-[44px]` |
| Contrast | WCAG AA (`text-gray-600`) |
| Aria Labels | 6+ أزرار أيقونية |
| Cache Headers | `.htaccess` + `.nginx.conf.example` (1 year static) |

### 13.3 اختبارات الأداء

- **HeroSliderTest**
- **LcpImageTest**
- **CacheManagerTest**

---

## 14. المميزات الكاملة — الأمان والصلاحيات

| الميزة | الحالة | التفاصيل |
|--------|--------|----------|
| CSRF Protection | ✅ | على النماذج (معطّل على payment webhooks) |
| RBAC | ✅ | Spatie Permission + Zero-Config Dashboard |
| Guest Order Window | ✅ | 1 hour للضيوف |
| Payment Keys Encryption | ✅ | في `payment_settings` |
| Activity Log | ✅ | `HasFullAudit` trait + Spatie |
| Input Validation | ✅ | Forms + Livewire |
| Soft Deletes | ✅ | Users, Products, Categories, BlogPost |
| Policies | ✅ | 7 policies |
| Trust Proxies | ⚙️ | `trustProxies(at: '*')` — behind CDN |
| Sanctum API Auth | ❌ | مثبت غير مستخدم |
| Nginx Security Headers | ✅ | `.nginx.conf.example` |

### Middleware (7)

- `SetLocale`
- `EnsureCustomerIsActive`
- `ApplyDashboardConfiguration`
- `EnforcePageAccess`
- `TrustProxies`
- `LogUploadedFiles`
- `LogActivityContext`

### Policies (7)

- ProductPolicy, CategoryPolicy, OrderPolicy, UserPolicy, RolePolicy, PermissionPolicy, TranslationPolicy

### System Reset & Backup

- **SystemReset** — reset انتقائي قبل الإطلاق (super-admin)
- **BackupManager** — Spatie backup
- **CleanDeploySeeder** — بيانات نظيفة للإطلاق

---

## 15. المميزات الكاملة — API والتكاملات

### 15.1 Public API (في web.php — لا routes/api.php)

```
GET /api/categories
GET /api/categories/{id}
GET /api/products
GET /api/products/featured
GET /api/products/on-sale
GET /api/products/{id}
```

- Controllers: `Admin\CategoryController`, `Admin\ProductController`
- **بدون authentication**
- **بدون rate limiting مخصص**
- **Sanctum غير مستخدم**

### 15.2 التكاملات

| التكامل | الحالة |
|---------|--------|
| Kashier | ✅ |
| Paymob | ✅ |
| Fawry | ✅ |
| Google OAuth | ✅ |
| Google Analytics | ✅ |
| Facebook Pixel | ✅ |
| Email SMTP/Resend/SES | ✅ configurable |
| MJML API | ✅ configured |
| AWS S3 | ⚙️ config ready |
| Redis | ⚙️ config ready |
| Spatie Backup | ✅ |
| Spatie Activity Log | ✅ |
| Intervention Image | ✅ |
| Maatwebsite Excel | ✅ |
| WhatsApp | ❌ UI link فقط |
| SMS/Twilio | ❌ |

---

## 16. قاعدة البيانات — الجداول والنماذج والعلاقات

### 16.1 الإحصائيات

- **101 migration**
- **55 Eloquent model**
- **21 seeder**
- **~60+ جدول** (ليس 31 كما في CATALOG.md القديم)

### 16.2 Models (55)

```
Batch, Banner, BlogPost, CampaignLog, CampaignOffer, Cart, CartItem,
Category, City, CodeUsage, CommissionPayout, Country, Customer,
DiscountCode, EmailCampaign, EmailLog, EmailTemplate, Governorate,
HelpEntry, Influencer, InfluencerApplication, InfluencerCommission,
NavigationGroupConfiguration, NewsletterSubscription, Order, OrderItem,
OrderReturn, OrderStatusHistory, Page, Payment, PaymentSetting,
Permission, Product, ProductImage, ProductReview, ProductVariant,
ResourceConfiguration, ReturnItem, Role, RoleNavigationGroup,
RolePageAccess, RoleResourceAccess, RoleWidgetDefault, Setting,
ShippingAddress, Slider, StockCount, StockCountItem, StockMovement,
Translation, User, UserWidgetPreference, Warehouse, WidgetConfiguration, Wishlist
```

### 16.3 مجموعات الجداول

**Core E-commerce:**
`products`, `product_images`, `product_variants`, `product_reviews`, `product_views`, `categories`, `orders`, `order_items`, `order_status_histories`, `carts`, `cart_items`, `wishlists`, `discount_codes`, `code_usages`, `shipping_addresses`, `payments`, `payment_settings`, `returns`, `return_items`

**Users:**
`users`, `customers`, `password_reset_tokens`, `customer_password_reset_tokens`, `sessions`

**Influencers:**
`influencers`, `influencer_applications`, `influencer_commissions`, `commission_payouts`

**Content:**
`pages`, `blog_posts`, `banners`, `sliders`, `help_entries`, `settings`, `translations`

**Inventory:**
`warehouses`, `batches`, `stock_movements`, `stock_counts`, `stock_count_items`

**Email/Marketing:**
`email_templates`, `email_logs`, `email_campaigns`, `newsletter_subscriptions`, `campaign_offers`, `campaign_logs`

**Geography:**
`countries`, `governorates`, `cities`

**Dashboard Config:**
`widget_configurations`, `user_widget_preferences`, `role_widget_defaults`, `resource_configurations`, `role_resource_access`, `role_page_access`, `navigation_group_configurations`, `role_navigation_groups`

**Spatie/System:**
`permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`, `activity_log`, `media`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`

### 16.4 أهم العلاقات

```
Customer ──< Order, Cart, Wishlist, ProductReview, ShippingAddress
Product ──< ProductImage, ProductVariant, ProductReview, Batch, StockMovement
         ──> Category
Order ──< OrderItem, OrderStatusHistory, OrderReturn, Payment, ProductReview
      ──> Customer, DiscountCode, ShippingAddress, InfluencerCommission
Influencer ──> User
            ──< DiscountCode, InfluencerCommission
User ──> Influencer (HasOne)
     ──< BlogPost (author)
Category ──< Product, Category (self-referential)
Warehouse ──< StockMovement, StockCount (hierarchical)
OrderReturn ──< ReturnItem ──> Order, Product
```

### 16.5 Enums (7)

- `OrderStatus`, `ReturnStatus`, `ReturnType`
- `StockCountStatus`, `StockCountType`, `StockCountScope`, `VarianceReasonType`

### 16.6 Seeders (21)

`DatabaseSeeder`, `RolesAndPermissionsSeeder`, `AdminUserSeeder`, `EgyptLocationsSeeder`, `EmailTemplateSeeder`, `FrontendTranslationsSeeder`, `CosmeticsProductsSeeder`, `CosmeticsCategoriesSeeder`, `DemoDataSeeder`, `CleanDeploySeeder`, `HelpEntrySeeder`, `InfluencerTranslationsSeeder`, `InventoryTranslationsSeeder`, `OrdersSeeder`, `ReturnPolicySettingsSeeder`, `ReturnsPageSeeder`, `ShippingDiscountSettingsSeeder`, `TermsPageSeeder`, `TranslationSeeder`, `WarehouseSeeder`, `AdminTranslationsSeeder`

---

## 17. المسارات الكاملة Routes

### Store (Public + Cached)
`/` | `/offers` | `/cosmetics` | `/products` | `/products/{slug}` | `/categories/{slug}`

### Store (Public — No Cache)
`/cart` | `/checkout` | `/checkout/success/{order}` | `/track-order` | `/about` | `/contact` | `/page/{slug}` | `/{slug}` fallback | `/influencer/apply` | newsletter unsubscribe

### Store (Auth: customer)
`/wishlist` | `/account/*`

### Auth
`/register` | `/login` | `/forgot-password` | `/reset-password/{token}` | `/auth/google` | `/verify-email` | `/confirm-password`

### Admin (Filament auto)
`/admin/*`

### Partners
`/partners/*`

### Payment
`/payment/checkout/{order}` | `/payment/process/{order}` | gateway callbacks/webhooks | success/failed

### Admin Utilities (auth: web)
`/admin/products/*` | `/admin/stock-counts/*`

### API
`/api/categories` | `/api/products/*`

### Legacy/Redirects
`/dashboard` → `/admin` | `/orders` → `/account/orders`

### ⚠️ Debug (يجب حذفها)
`/test-cart-debug` | `/test-paymob-callback` | `/test-paymob-full`

### Language
`/language/{locale}` | `/locale/{locale}`

---

## 18. طبقة الخدمات Services

| Service | الغرض |
|---------|-------|
| **CartService** | منطق السلة |
| **OrderService** | إنشاء/تحديث الطلبات |
| **PaymentService** | معالجة الدفع |
| **PaymentGatewayManager** | اختيار البوابة |
| **CouponService** | أكواد الخصم |
| **ReturnService** | الإرجاع |
| **StockMovementService** | حركات المخزون |
| **StockCountService** | الجرد |
| **BatchService** | الدفعات |
| **ProductService** | المنتجات |
| **CategoryService** | الفئات |
| **WishlistService** | المفضلة |
| **ReviewService** | المراجعات |
| **InfluencerService** | المؤثرين |
| **EmailService** | إرسال البريد |
| **EmailTemplateService** | القوالب |
| **TranslationService** | الترجمة |
| **AnalyticsService** | Analytics |
| **DashboardConfigurationService** | RBAC Dashboard |
| **SystemResetService** | Reset النظام |
| **PdfService** | تقارير PDF |
| **ProductImageUploader** | رفع الصور |
| **KashierService** | Kashier legacy |
| **Gateways/KashierGateway** | Kashier |
| **Gateways/PaymobGateway** | Paymob |
| **Gateways/FawryGateway** | Fawry |

---

## 19. الواجهة الأمامية Frontend

### Layouts
- `layouts/store.blade.php` — المتجر الرئيسي
- `layouts/cosmetics.blade.php` — ثيم التجميل
- `layouts/auth.blade.php`, `layouts/guest.blade.php`, `layouts/admin.blade.php`
- `components/layouts/partners.blade.php` — المؤثرين

### Vite Inputs
```
resources/css/app.css
resources/js/app.js
resources/css/filament/admin/theme.css
```

### Views (~127 blade)
- `livewire/store/` — مكونات المتجر
- `livewire/pages/auth/` — Volt auth
- `filament/` — admin custom
- `filament/partners/` — partners panel
- `payment/` — success/failed/select
- `emails/templates/` — HTML emails
- `components/cosmetics/` — cosmetics theme
- `components/store/` — header, footer, product-card, breadcrumbs

---

## 20. الاختبارات Test Coverage

### الإحصائيات
- **29 ملف اختبار**
- PHPUnit 11 + SQLite in-memory

### Feature Tests (~25 ملف)

| المجال | الملفات |
|--------|---------|
| Auth | Authentication, Registration, PasswordReset, PasswordUpdate, PasswordConfirmation, EmailVerification, CartMerge |
| Checkout | AuthenticatedCheckoutTest |
| Orders/Stock | OrderStockFlowTest (11 tests) |
| Returns | ReturnServiceTest, ReturnResourceTest, ReturnPolicyTest |
| Wishlist | WishlistTest |
| Reviews | ProductReviewsTest |
| Products | ProductFilteringTest, ProductImageUploadTest |
| Track Order | GuestTrackOrderTest |
| Account | CustomerAccountTest |
| Performance | HeroSliderTest, LcpImageTest, CacheManagerTest |
| Profile | ProfileTest |

### Unit Tests (4)
- BatchServiceTest, StockMovementServiceTest, ReturnServiceTest, ProductServiceTest
- ExampleTest (placeholder)

### ⚠️ فجوات التغطية
- Payments / Gateways / Callbacks
- Influencer system
- Email campaigns
- Newsletter
- Filament admin (عدا CacheManager)
- API endpoints
- Google OAuth
- Translation system
- Blog

---

## 21. التوثيق الموجود

**173 ملف** في `docs/` — غني لكن **مجزّأ**

### التصنيفات

| الفئة | أمثلة |
|-------|-------|
| Catalog | `CATALOG.md`, `PROJECT_MAP.md` |
| Task Reports | `TASK_9_*`, `PHASE_*` |
| Bugfixes | `BUGFIX_*` |
| Features | `TRANSLATION_SYSTEM.md`, `EMAIL_SYSTEM_DOCUMENTATION.md`, `NEWSLETTER_CAMPAIGN_SYSTEM.md` |
| Payment | `docs/dynamic_payment_gateway/` |
| Influencer | `docs/influencer-system-2026-01-01/` |
| Dashboard RBAC | `docs/dashboard-customization/` |
| Inventory | `docs/inventory_and_returns/` |
| Guides for AI | Kashier, payment in Egypt |
| Production | `PRODUCTION_MIGRATION_GUIDE.md` |

### ملفات Root
- `README.md` — **⚠️ تالف** (مدمج مع Laravel boilerplate)
- `docs/CATALOG.md` — مرجع جيد لكن **بعض المعلومات outdated**
- `docs/violet-marketing.html` — untracked
- `test_translation.php` — untracked script

---

## 22. العيوب والمشاكل — مفصّلة

### 🔴 أمنية (Critical)

| # | المشكلة | الموقع | التأثير |
|---|---------|--------|---------|
| 1 | **Debug routes مفتوحة** | `web.php` lines 91-93, 200-232 | `/test-cart-debug`, `/test-paymob-*` تسجل request data في production |
| 2 | **Inline password route** | `web.php` lines 237-279 | Validation يدوي بدون FormRequest، JSON response |
| 3 | **`.env.example` production values** | lines 100-103 | `APP_ENV=production`, `APP_DEBUG=false` uncommented |
| 4 | **SUPER_ADMIN_PASSWORD=password** | `.env.example` line 98 | كلمة مرور ضعيفة في المثال |
| 5 | **GOOGLE_* keys مكررة** | `.env.example` lines 85-94 | تكرار مربك |
| 6 | **Public API بدون rate limit** | `/api/*` | إمكانية abuse |
| 7 | **CSRF disabled على webhooks** | payment routes | مقصود — يحتاج مراجعة signature verification |

### 🟠 وظيفية (Functional)

| # | المشكلة | الموقع | التأثير |
|---|---------|--------|---------|
| 8 | **OrderObserver معطّل** | `AppServiceProvider.php:50-51` | "causing infinite loop" — automation معطّل |
| 9 | **Blog incomplete** | Model exists, no Resource/route | ميزة مذكورة غير متاحة |
| 10 | **WhatsApp/SMS غير منفّذ** | docs only | مذكور في workflow |
| 11 | **Sanctum unused** | composer.json | dead dependency |
| 12 | **Duplicate password reset routes** | `auth.php` + `web.php` | تعارض محتمل `password.reset` |
| 13 | **Route order conflict** | catch-all `/{slug}` vs others | قد يلتقط routes غير مقصودة |
| 14 | **TODO: discount code logic** | `OrderService.php:433` | منطق غير مكتمل |
| 15 | **TODO: category_id null** | `CheckoutPage.php:346` | بيانات ناقصة في cart items |
| 16 | **TODO: return notifications** | `OrderReturnsTable.php:205,235` | إشعار العميل غير منفّذ |
| 17 | **TODO: best_sellers scope** | `Cosmetics/HomePage.php:37` | scope وهمي |
| 18 | **TODO: cosmetics newsletter** | `newsletter-banner.blade.php:34` | غير مفعّل |
| 19 | **StatsOverviewWidget deprecated** | still in codebase | dead code |
| 20 | **Order.user() deprecated** | `Order.php:82` | legacy user_id |

### 🟡 جودة الكود (Code Quality)

| # | المشكلة | التفاصيل |
|---|---------|----------|
| 21 | **README corrupted** | مدمج Violet + Laravel default |
| 22 | **Backup files in repo** | `store.blade.php.backup`, `ProductForm.php.backup` |
| 23 | **173 docs fragmented** | صعب الصيانة، بعض outdated |
| 24 | **CATALOG.md inaccurate** | 31 tables, blog complete, Sanctum active |
| 25 | **Utility scripts in root** | `check_tables.php`, `test_translation.php` |
| 26 | **No CI/CD** | `.github/` contains only `errors.md` |
| 27 | **Legacy payment routes** | @deprecated callbacks still active |

### 🟡 Testing Gaps

| # | المنطقة | الحالة |
|---|---------|--------|
| 28 | Payment callbacks | ❌ لا tests |
| 29 | Influencer lifecycle | ❌ لا tests |
| 30 | Email campaigns | ❌ لا tests |
| 31 | API endpoints | ❌ لا tests |
| 32 | Google OAuth | ❌ لا tests |

---

## 23. الميزات الناقصة أو غير المكتملة

| الميزة | الحالة | ما موجود | ما ناقص |
|--------|--------|----------|---------|
| **Blog** | 30% | Migration, Model, Permission | Filament Resource, Routes, Views, SEO |
| **WhatsApp Notifications** | 0% | Link in contact | API integration, templates |
| **SMS/Twilio** | 0% | Docs | Service, config, templates |
| **Sanctum API** | 10% | Package installed | HasApiTokens, protected routes, api.php |
| **Product Variants (Frontend)** | 50% | Admin CRUD | Variant selector in storefront |
| **Multi-currency** | 0% | EGP hardcoded | Currency model, conversion |
| **InstaPay Gateway** | 0% | Payment method enum | Dedicated gateway |
| **Cosmetics Newsletter** | 0% | UI banner | Backend integration |
| **Best Sellers** | 0% | Placeholder query | Scope based on order_items |
| **Return Customer Notification** | 0% | TODO comment | Email trigger on approve/reject |
| **Horizon Queue Monitor** | 0% | database queue | Laravel Horizon |
| **CI/CD Pipeline** | 0% | Manual deploy | GitHub Actions |
| **Mobile App API** | 20% | Public read-only API | Auth, cart, checkout API |

---

## 24. اقتراحات الإضافة والتحسين

### 🔴 أولوية عالية (قبل الإطلاق)

| # | الاقتراح | السبب | الجهد |
|---|----------|-------|-------|
| 1 | **حذف debug routes** | أمن production | منخفض |
| 2 | **إصلاح README.md** | أول انطباع للمطورين | منخفض |
| 3 | **تنظيف `.env.example`** | إزالة duplicates و production defaults | منخفض |
| 4 | **إصلاح/تفعيل OrderObserver** | automation المخزون | متوسط |
| 5 | **إكمال TODO items** | discount logic, return notifications | متوسط |
| 6 | **Payment gateway tests** | callbacks/webhooks critical | متوسط |
| 7 | **Rate limiting على API** | منع abuse | منخفض |
| 8 | **نقل password update route** | إلى Controller + FormRequest | منخفض |

### 🟠 أولوية متوسطة (Post-Launch)

| # | الاقتراح | الفائدة |
|---|----------|---------|
| 9 | **إكمال Blog module** | SEO + content marketing |
| 10 | **WhatsApp notifications** | Order updates للسوق المصري |
| 11 | **SMS OTP/Notifications** | Verification + delivery updates |
| 12 | **API layer proper** | Mobile app readiness |
| 13 | **Sanctum activation** | أو إزالة dependency |
| 14 | **Newsletter/Campaign tests** | Queue reliability |
| 15 | **Consolidate docs** | `docs/INDEX.md` master index |
| 16 | **CI/CD pipeline** | GitHub Actions: test + pint + deploy |
| 17 | **Redis for production** | queue/cache/session performance |
| 18 | **Product variants frontend** | UX improvement |
| 19 | **Influencer system tests** | Commission accuracy |
| 20 | **حذف backup files** | repo cleanliness |

### 🟢 أولوية منخفضة (Future)

| # | الاقتراح | الفائدة |
|---|----------|---------|
| 21 | **Multi-currency** | expansion beyond Egypt |
| 22 | **Blog SEO** | schema.org Article markup |
| 23 | **Laravel Horizon** | queue monitoring dashboard |
| 24 | **Pest migration** | modern testing syntax |
| 25 | **PWA support** | mobile experience |
| 26 | **Abandoned cart emails** | conversion recovery |
| 27 | **Product comparison** | UX feature |
| 28 | **Advanced analytics** | custom events, funnel |
| 29 | **Multi-warehouse frontend** | stock by location |
| 30 | **Subscription products** | recurring revenue |
| 31 | **Live chat integration** | customer support |
| 32 | **Affiliate program expansion** | beyond influencers |
| 33 | **Export orders to accounting** | ERP integration |
| 34 | **Barcode scanner (admin)** | warehouse operations |
| 35 | **Dark mode storefront** | user preference |

---

## 25. إحصائيات المشروع

| البند | العدد |
|-------|-------|
| إجمالي الملفات (تقريبي) | ~1071 |
| Eloquent Models | 55 |
| Filament Resources | 35 |
| Filament Pages | 11 |
| Filament Widgets | 26 |
| Livewire Components | 36 |
| Services | 26 |
| Enums | 7 |
| Migrations | 101 |
| Seeders | 21 |
| Test Files | 29 |
| Middleware | 7 |
| Policies | 7 |
| Notifications | 5 |
| Jobs | 3 |
| Console Commands | 5 |
| Email HTML Templates | 10 |
| Config Files | 16 |
| Blade Views | 127+ |
| Docs Files | 173 |
| Payment Gateways | 3 |
| Staff Roles | 9 |
| Permissions | 35+ |
| Languages | 2 (ar, en) |

---

## 26. خطة الإطلاق Production Checklist

### قبل الإطلاق (Must Do)

- [ ] حذف `/test-cart-debug`, `/test-paymob-callback`, `/test-paymob-full`
- [ ] مراجعة CSRF exceptions على webhooks + signature verification
- [ ] تنظيف `.env.example`
- [ ] إصلاح README.md
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] MySQL بدل SQLite
- [ ] Redis للـ queue/cache (موصى)
- [ ] Queue worker running (`php artisan queue:listen`)
- [ ] SSL/HTTPS configured
- [ ] `.nginx.conf.example` أو `.htaccess` applied
- [ ] `php artisan migrate --force`
- [ ] `npm run build`
- [ ] `php artisan optimize:clear`
- [ ] `php artisan responsecache:clear`
- [ ] `php artisan media:regenerate` (WebP)
- [ ] Spatie Backup scheduled
- [ ] Payment gateway Test → Live mode
- [ ] Google OAuth redirect URI updated
- [ ] Facebook Pixel ID set
- [ ] Email SMTP configured
- [ ] SUPER_ADMIN password changed from default

### Deploy Commands

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan responsecache:clear
php artisan queue:restart
```

---

## ملحق: مقارنة CATALOG.md vs الواقع

| البند | CATALOG.md | الواقع (2026-06-27) |
|-------|------------|---------------------|
| عدد الجداول | 31 | ~60+ |
| Blog | مكتمل | Model فقط — لا admin/frontend |
| Sanctum | ✅ فعّال | مثبت غير مستخدم |
| Partners pages | "قيد التطوير" | 5 صفحات مكتملة |
| Fawry | غير مذكور | Gateway مكتمل |
| عدد Tests | 44+ claimed | 29 files |
| Stock/Inventory | غير مذكور بالتفصيل | نظام متكامل |
| Email Campaigns | غير مذكور | مكتمل |
| Help Center | غير مذكور | مكتمل |
| System Reset | غير مذكور | مكتمل |

---

*تم إعداد هذا التقرير بناءً على فحص مباشر للكود المصدري — Models, Routes, Services, Tests, Migrations, Config, Docs.*  
*آخر تحديث: 27 يونيو 2026*
