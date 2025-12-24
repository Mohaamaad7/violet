# دليل شامل لدمج Kashier في Laravel 12 مع Filament 4
## فودافون كاش وإنستاباي - التكامل الكامل

---

## جدول المحتويات

1. [مقدمة عن Kashier](#مقدمة-عن-kashier)
2. [لماذا Kashier أفضل من Paymob؟](#لماذا-kashier-أفضل-من-paymob)
3. [وسائل الدفع المدعومة](#وسائل-الدفع-المدعومة)
4. [متطلبات التسجيل والتفعيل](#متطلبات-التسجيل-والتفعيل)
5. [Laravel Packages المتاحة](#laravel-packages-المتاحة)
6. [البنية التقنية الكاملة](#البنية-التقنية-الكاملة)
7. [Database Schema والـ Migrations](#database-schema-والـ-migrations)
8. [Models و Relationships](#models-و-relationships)
9. [Services Layer Architecture](#services-layer-architecture)
10. [Controllers و Routes](#controllers-و-routes)
11. [Middleware للأمان](#middleware-للأمان)
12. [Webhook Handling](#webhook-handling)
13. [Filament 4 Resources](#filament-4-resources)
14. [Testing Strategy](#testing-strategy)
15. [أمثلة كود عملية كاملة](#أمثلة-كود-عملية-كاملة)
16. [الخلاصة والتوصيات](#الخلاصة-والتوصيات)

---

## مقدمة عن Kashier

### نظرة عامة

**Kashier** هي بوابة دفع إلكترونية مصرية تأسست في **2017** ومقرها في الزمالك، القاهرة. حصلت على تمويل seed بقيمة **3 مليون دولار** من مستثمرين بارزين منهم Sawari Ventures و First Circle Capital. Kashier معتمدة **PCI DSS Level 1** وهو أعلى مستوى أمان في صناعة المدفوعات.

### الإحصائيات الرئيسية

- **تأسست:** 2017
- **الموقع:** القاهرة، مصر
- **التمويل:** $3 مليون
- **الامتثال:** PCI DSS Level 1
- **السوق:** مصر حصرياً حالياً
- **الرسوم:** 2.85% + 3 جنيه لكل معاملة ناجحة
- **رسوم شهرية:** لا توجد
- **التحويل للبنك:** خلال 3 أيام عمل

### المميزات الأساسية

1. ✅ **دعم المحافظ المصرية:** فودافون كاش، أورانج موني، اتصالات كاش، ميزة والت
2. ✅ **iFrame Integration:** سلس ومباشر على موقعك
3. ✅ **Hosted Payment Page:** صفحة دفع آمنة مستضافة
4. ✅ **دعم 3D Secure:** للحماية من الاحتيال
5. ✅ **أقساط البنوك:** البنك الأهلي، بنك مصر، Emirates NBD
6. ✅ **BNPL:** ValU، Souhoola، Sympl، Halan، Contact
7. ✅ **دعم عملات متعددة:** EGP، USD، GBP، EUR
8. ✅ **APIs موثقة جيداً:** سهلة الاستخدام
9. ✅ **دعم فني ممتاز:** متاح طوال الوقت
10. ✅ **Refunds:** كاملة أو جزئية

---

## لماذا Kashier أفضل من Paymob؟

### المقارنة الشاملة

| المعيار | Kashier ⭐ | Paymob |
|---------|-----------|--------|
| **الرسوم** | 2.85% + 3 EGP | 2.75% + 3 EGP |
| **رسوم شهرية** | لا توجد ✅ | لا توجد ✅ |
| **الدعم الفني** | ممتاز جداً ⭐⭐⭐⭐⭐ | جيد ⭐⭐⭐⭐ |
| **سرعة الاستجابة** | سريع جداً | جيد |
| **التوثيق** | ممتاز ومنظم | ممتاز |
| **فودافون كاش** | ✅ مدعوم | ✅ مدعوم |
| **إنستاباي** | ❌ قريباً | ❌ قريباً |
| **سهولة التكامل** | سهل جداً | سهل |
| **Sandbox للاختبار** | متاح | متاح |
| **Laravel Packages** | متعددة وحديثة | متعددة |
| **Stability** | مستقر جداً | جيد |
| **Apple Pay** | ✅ | ✅ |
| **BNPL Options** | 5+ شركات | 7+ شركات |

### آراء المستخدمين

تشير المراجعات إلى أن Kashier يتفوق في:
- **الدعم الفني:** فريق متفاني ومتاح دائماً
- **الاستقرار:** down-time شبه معدوم
- **البساطة:** سهولة الإعداد والتكامل
- **الأسعار التنافسية:** فرق بسيط جداً عن Paymob

**توصية:** إذا كان عملاؤك أشادوا بـ Kashier، فهو **خيار ممتاز** ويستحق التجربة.

---

## وسائل الدفع المدعومة

### البطاقات

- **Visa** (محلية ودولية)
- **Mastercard** (محلية ودولية)
- **Meeza** (البطاقة الوطنية المصرية)
- دعم **3D Secure** لجميع البطاقات

### المحافظ الإلكترونية

1. **Vodafone Cash** ✅
   - أكثر محفظة انتشاراً في مصر
   - 25 مليون مستخدم
   - حصة سوقية 55%
   
2. **Orange Money** ✅
   - محفظة أورانج
   
3. **Etisalat Cash** ✅
   - محفظة اتصالات
   
4. **Meeza Wallet** ✅
   - المحفظة الوطنية

### إنستاباي (InstaPay)

**الحالة الحالية:** ❌ **غير مدعوم بعد**

حسب توثيق Kashier والمصادر الرسمية، **InstaPay ليس متاحاً حالياً** كوسيلة دفع API. الدعم قد يأتي مستقبلاً لكن لا توجد جداول زمنية محددة.

**البديل المتاح:** استخدام QR Code أو Payment Links من تطبيق InstaPay مباشرة.

### أقساط البنوك

- **البنك الأهلي المصري (NBE)**
- **بنك مصر**
- **Emirates NBD**
- بنوك أخرى قريباً

### Buy Now Pay Later (BNPL)

1. **ValU** - الأشهر في مصر
2. **Souhoola**
3. **Sympl**
4. **Halan**
5. **Contact** (جديد 2025)

### طرق دفع أخرى

- **Aman** - الدفع نقداً في نقاط البيع
- **Fawry** (من خلال تكامل خاص)
- **Apple Pay** (Live environment فقط)

---

## متطلبات التسجيل والتفعيل

### 1. إنشاء حساب

**رابط التسجيل:** `https://merchant.kashier.io/` أو `https://portal.kashier.io/`

املأ البيانات الأساسية:
- اسم الشركة
- البريد الإلكتروني
- رقم الهاتف
- نوع النشاط

### 2. المستندات المطلوبة

يجب رفع المستندات التالية:

1. **السجل التجاري** (صورة واضحة)
2. **البطاقة الضريبية**
3. **صورة من البطاقة الشخصية** للمالك/المدير
4. **عقد الشركة** (إن وجد)
5. **كشف حساب بنكي** حديث

### 3. مدة التحقق

- **48 ساعة عمل** في المتوسط
- قد تكون أسرع إذا كانت المستندات واضحة وكاملة

### 4. الحصول على بيانات API

بعد التفعيل، من لوحة التحكم:

1. انتقل إلى **Integrate now** > **Payment API Keys**
2. انسخ **Merchant ID** (MID-xx-xx)
3. أنشئ **API Key** جديد أو استخدم المفتاح الافتراضي
4. انتقل إلى **Secret Keys** واحصل على **Secret Key**

### 5. بيانات الاختبار (Test Mode)

```
Test API Key: (من لوحة التحكم)
Test MID: (من لوحة التحكم)

Test Cards:
- Success: 5111 1111 1111 1118
- Expiry: 06/28
- CVV: 100

- Success 3D Secure: 5123 4500 0000 0008
- Expiry: 06/28
- CVV: 100

- Failure: 5111 1111 1111 1118
- Expiry: 05/28
- CVV: 102
```

---

## Laravel Packages المتاحة

### Package #1: madarit/laravel-kashier (الموصى به 🌟)

**الأحدث والأكثر تطوراً** - تم تحديثه في نوفمبر 2024

#### المميزات

- ✅ دعم Laravel 9، 10، 11، 12
- ✅ PHP 8.0+
- ✅ iFrame و HPP Integration
- ✅ Signature Validation تلقائي
- ✅ Views جاهزة وقابلة للتخصيص
- ✅ Facade Support
- ✅ Auto-discovery

#### التثبيت

```bash
composer require madarit/laravel-kashier
```

#### النشر

```bash
# نشر ملف التكوين
php artisan vendor:publish --tag=kashier-config

# نشر الـ Views (اختياري)
php artisan vendor:publish --tag=kashier-views
```

#### التكوين (.env)

```env
KASHIER_MODE=test
KASHIER_TEST_API_KEY=your_test_api_key
KASHIER_TEST_MID=your_test_mid
KASHIER_LIVE_API_KEY=
KASHIER_LIVE_MID=
```

#### الاستخدام الأساسي

```php
use Madarit\LaravelKashier\Facades\Kashier;

// Generate order hash
$hash = Kashier::generateOrderHash($orderId, $amount, $currency);

// Get HPP URL
$hppUrl = Kashier::getHppUrl(
    orderId: 'ORDER-123',
    amount: '100.00',
    currency: 'EGP',
    callbackUrl: route('payment.callback')
);

// Validate callback signature
$isValid = Kashier::validateSignature($request->all());
```

---

### Package #2: madarit/kashier-laravel-sdk (مع Webhooks)

**نسخة موسعة** بميزات إضافية

#### المميزات الإضافية

- ✅ Refund API Support (كامل وجزئي)
- ✅ Webhook Handling تلقائي
- ✅ Event System مدمج
- ✅ Logging تلقائي
- ✅ Multi-payment methods config

#### التثبيت

```bash
composer require madarit/kashier-laravel-sdk
```

#### التكوين الموسع (.env)

```env
KASHIER_MODE=test

# Test Credentials
KASHIER_TEST_API_KEY=your_test_api_key
KASHIER_TEST_MID=your_test_mid

# Live Credentials
KASHIER_LIVE_API_KEY=
KASHIER_LIVE_MID=

# Webhook Configuration
KASHIER_WEBHOOK_ENABLED=true
KASHIER_WEBHOOK_PREFIX=kashier

# Logging
KASHIER_LOGGING_ENABLED=true

# Payment Settings
KASHIER_CURRENCY=EGP
KASHIER_ALLOWED_METHODS=card,wallet,bank_installments
```

#### استخدام Refunds

```php
use Madarit\LaravelKashier\Facades\Kashier;

// Full refund
$result = Kashier::refund('order_123', 'trans_456');

// Partial refund (50 EGP)
$result = Kashier::refund('order_123', 'trans_456', 50.00);

// Get refund status
$status = Kashier::getRefundStatus('order_123', 'trans_456');
```

#### Webhook Events

```php
// app/Listeners/HandleKashierWebhook.php
namespace App\Listeners;

use Madarit\LaravelKashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;

class HandleKashierWebhook
{
    public function handle(WebhookReceived $event)
    {
        $payload = $event->payload;
        
        // Update order status
        $order = Order::where('reference', $payload['order_id'])->first();
        
        if ($payload['paymentStatus'] === 'SUCCESS') {
            $order->markAsPaid();
        }
        
        Log::info('Kashier Webhook received', $payload);
    }
}
```

تسجيل الـ Listener:

```php
// app/Providers/EventServiceProvider.php
use Madarit\LaravelKashier\Events\WebhookReceived;
use App\Listeners\HandleKashierWebhook;

protected $listen = [
    WebhookReceived::class => [
        HandleKashierWebhook::class,
    ],
];
```

---

### Package #3: nafezly/payments (Multi-Gateway)

**أشمل package** - يدعم 30+ بوابة دفع

#### المميزات

- ✅ دعم Kashier + Paymob + Fawry + 27 بوابة أخرى
- ✅ Unified API لجميع البوابات
- ✅ دعم المحافظ: فودافون كاش، أورانج، اتصالات، ميزة
- ✅ 458 نجمة على GitHub
- ✅ 15,535+ تحميل

#### التثبيت

```bash
composer require nafezly/payments dev-master
```

#### النشر

```bash
php artisan vendor:publish --tag="nafezly-payments-config"
php artisan vendor:publish --tag="nafezly-payments-lang"
```

#### التكوين للـ Kashier (.env)

```env
# Kashier
KASHIER_ACCOUNT_KEY=your_account_key
KASHIER_IFRAME_KEY=your_iframe_key
KASHIER_TOKEN=your_api_token
KASHIER_URL=https://checkout.kashier.io
KASHIER_MODE=test
KASHIER_CURRENCY=EGP
KASHIER_WEBHOOK_URL=https://yourdomain.com/webhooks/kashier
```

#### الاستخدام

```php
use Nafezly\Payments\Facades\Payments;

// بدء الدفع
$payment = Payments::kashier()->pay(
    amount: 100.00,
    user_id: auth()->id(),
    user_first_name: $user->first_name,
    user_last_name: $user->last_name,
    user_email: $user->email,
    user_phone: $user->phone,
    source: $user->phone // للمحافظ
);

// التحقق من الدفع
$verification = Payments::kashier()->verify($request->all());

if ($verification->success) {
    // الدفع نجح
    $transactionId = $verification->transaction_id;
}
```

---

## البنية التقنية الكاملة

### هيكل المشروع المقترح

```
app/
├── Models/
│   ├── Payment.php
│   ├── PaymentMethod.php
│   └── PaymentTransaction.php
├── Services/
│   ├── PaymentService.php
│   ├── Kashier/
│   │   ├── KashierClient.php
│   │   ├── KashierPayment.php
│   │   ├── KashierRefund.php
│   │   └── KashierWebhook.php
│   └── PaymentGatewayInterface.php
├── Http/
│   ├── Controllers/
│   │   └── PaymentController.php
│   ├── Middleware/
│   │   ├── VerifyKashierWebhook.php
│   │   └── EnsureIdempotency.php
│   └── Requests/
│       └── InitiatePaymentRequest.php
├── Jobs/
│   └── ProcessPaymentCallback.php
├── Events/
│   ├── PaymentInitiated.php
│   ├── PaymentCompleted.php
│   └── PaymentFailed.php
├── Listeners/
│   ├── SendPaymentNotification.php
│   └── UpdateOrderStatus.php
└── Filament/
    ├── Resources/
    │   └── PaymentResource.php
    └── Widgets/
        └── PaymentStatsWidget.php

database/
└── migrations/
    ├── create_payments_table.php
    ├── create_payment_methods_table.php
    └── create_payment_transactions_table.php

config/
├── kashier.php
└── payments.php
```

---

## Database Schema والـ Migrations

### Migration: payments table

```php
<?php
// database/migrations/2024_01_01_000001_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // علاقات
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            
            // معلومات الدفع الأساسية
            $table->string('reference')->unique()->comment('Internal reference');
            $table->string('transaction_id')->nullable()->unique()->comment('Gateway transaction ID');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');
            
            // وسيلة الدفع
            $table->enum('payment_method', [
                'card',
                'vodafone_cash',
                'orange_money',
                'etisalat_cash',
                'meeza_wallet',
                'bank_installment',
                'valu',
                'souhoola',
                'sympl',
                'aman',
                'fawry'
            ]);
            
            // الحالة
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'refunded',
                'partially_refunded',
                'cancelled',
                'expired'
            ])->default('pending');
            
            // معلومات البوابة
            $table->string('gateway')->default('kashier');
            $table->string('gateway_reference')->nullable()->comment('Gateway order ID');
            $table->json('gateway_response')->nullable();
            $table->json('gateway_metadata')->nullable();
            
            // معلومات الفشل
            $table->string('failure_reason')->nullable();
            $table->string('failure_code')->nullable();
            
            // التواريخ المهمة
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // الأمان
            $table->string('idempotency_key')->unique()->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            
            // Refund info
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->string('refund_reference')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes للأداء
            $table->index(['user_id', 'status']);
            $table->index(['gateway', 'gateway_reference']);
            $table->index(['status', 'created_at']);
            $table->index('payment_method');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

### Migration: payment_transactions table

```php
<?php
// database/migrations/2024_01_01_000002_create_payment_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            
            $table->enum('type', [
                'authorize',
                'capture',
                'refund',
                'void',
                'webhook'
            ]);
            
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['success', 'failed', 'pending']);
            
            $table->string('transaction_id')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['payment_id', 'type']);
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
```

### Migration: payment_methods table

```php
<?php
// database/migrations/2024_01_01_000003_create_payment_methods_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            
            $table->string('name'); // e.g., "Vodafone Cash"
            $table->string('code')->unique(); // e.g., "vodafone_cash"
            $table->string('type'); // card, wallet, bnpl, etc.
            $table->string('gateway'); // kashier, paymob, etc.
            
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            
            $table->json('config')->nullable(); // gateway-specific config
            
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();
            
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('gateway');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
```

### Seeder للـ Payment Methods

```php
<?php
// database/seeders/PaymentMethodSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'فودافون كاش',
                'code' => 'vodafone_cash',
                'type' => 'wallet',
                'gateway' => 'kashier',
                'is_active' => true,
                'sort_order' => 1,
                'icon' => 'vodafone-cash.png',
                'description' => 'ادفع باستخدام محفظة فودافون كاش',
                'min_amount' => 10.00,
                'max_amount' => 60000.00,
            ],
            [
                'name' => 'بطاقة ائتمانية',
                'code' => 'card',
                'type' => 'card',
                'gateway' => 'kashier',
                'is_active' => true,
                'sort_order' => 2,
                'icon' => 'credit-card.png',
                'description' => 'Visa, Mastercard, Meeza',
                'min_amount' => 10.00,
                'max_amount' => null,
            ],
            [
                'name' => 'أورانج موني',
                'code' => 'orange_money',
                'type' => 'wallet',
                'gateway' => 'kashier',
                'is_active' => true,
                'sort_order' => 3,
                'icon' => 'orange-money.png',
                'min_amount' => 10.00,
            ],
            [
                'name' => 'اتصالات كاش',
                'code' => 'etisalat_cash',
                'type' => 'wallet',
                'gateway' => 'kashier',
                'is_active' => true,
                'sort_order' => 4,
                'icon' => 'etisalat-cash.png',
                'min_amount' => 10.00,
            ],
            [
                'name' => 'ValU',
                'code' => 'valu',
                'type' => 'bnpl',
                'gateway' => 'kashier',
                'is_active' => true,
                'sort_order' => 5,
                'icon' => 'valu.png',
                'description' => 'اشتري الآن وادفع لاحقاً',
                'min_amount' => 500.00,
            ],
        ];

        DB::table('payment_methods')->insert($methods);
    }
}
```

---

## Models و Relationships

### Payment Model

```php
<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_id',
        'reference',
        'transaction_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'gateway',
        'gateway_reference',
        'gateway_response',
        'gateway_metadata',
        'failure_reason',
        'failure_code',
        'paid_at',
        'refunded_at',
        'expires_at',
        'idempotency_key',
        'ip_address',
        'user_agent',
        'refunded_amount',
        'refund_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'gateway_response' => 'encrypted:array',
        'gateway_metadata' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'is_paid',
        'is_refundable',
        'status_color',
    ];

    // ==================== Relationships ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // ==================== Scopes ====================

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '<', now());
    }

    // ==================== Accessors ====================

    protected function isPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'completed'
        );
    }

    protected function isRefundable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'completed' 
                && $this->paid_at?->diffInDays(now()) <= 30
                && $this->refunded_amount < $this->amount
        );
    }

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                'completed' => 'success',
                'pending' => 'warning',
                'processing' => 'info',
                'failed' => 'danger',
                'refunded', 'partially_refunded' => 'gray',
                'cancelled' => 'secondary',
                default => 'primary',
            }
        );
    }

    // ==================== Helper Methods ====================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canBeRefunded(): bool
    {
        return $this->is_refundable;
    }

    public function markAsCompleted(string $transactionId, ?array $metadata = null): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'paid_at' => now(),
            'gateway_metadata' => $metadata,
        ]);
        
        event(new \App\Events\PaymentCompleted($this));
    }

    public function markAsFailed(string $reason, ?string $code = null): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'failure_code' => $code,
        ]);
        
        event(new \App\Events\PaymentFailed($this));
    }

    public function markAsRefunded(float $amount, string $reference): void
    {
        $newRefundedAmount = $this->refunded_amount + $amount;
        
        $this->update([
            'status' => $newRefundedAmount >= $this->amount 
                ? 'refunded' 
                : 'partially_refunded',
            'refunded_amount' => $newRefundedAmount,
            'refund_reference' => $reference,
            'refunded_at' => now(),
        ]);
    }

    // ==================== Static Methods ====================

    public static function generateReference(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));
        } while (self::where('reference', $reference)->exists());
        
        return $reference;
    }

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (empty($payment->reference)) {
                $payment->reference = self::generateReference();
            }
            
            if (empty($payment->expires_at)) {
                $payment->expires_at = now()->addHours(24);
            }
        });
    }
}
```

### PaymentTransaction Model

```php
<?php
// app/Models/PaymentTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'payment_id',
        'type',
        'amount',
        'status',
        'transaction_id',
        'request_data',
        'response_data',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_data' => 'array',
        'response_data' => 'encrypted:array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public static function log(
        int $paymentId,
        string $type,
        float $amount,
        string $status,
        ?array $request = null,
        ?array $response = null,
        ?string $notes = null
    ): self {
        return self::create([
            'payment_id' => $paymentId,
            'type' => $type,
            'amount' => $amount,
            'status' => $status,
            'request_data' => $request,
            'response_data' => $response,
            'notes' => $notes,
        ]);
    }
}
```

---

## Services Layer Architecture

### KashierClient Service

```php
<?php
// app/Services/Kashier/KashierClient.php

namespace App\Services\Kashier;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KashierClient
{
    protected string $merchantId;
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;
    protected bool $isLiveMode;

    public function __construct()
    {
        $this->isLiveMode = config('kashier.mode') === 'live';
        
        $this->merchantId = $this->isLiveMode 
            ? config('kashier.live_mid')
            : config('kashier.test_mid');
            
        $this->apiKey = $this->isLiveMode 
            ? config('kashier.live_api_key')
            : config('kashier.test_api_key');
            
        $this->secretKey = $this->isLiveMode 
            ? config('kashier.live_secret_key')
            : config('kashier.test_secret_key');
            
        $this->baseUrl = config('kashier.base_url', 'https://checkout.kashier.io');
    }

    /**
     * Generate order hash for payment
     */
    public function generateOrderHash(
        string $orderId,
        float $amount,
        string $currency = 'EGP',
        ?string $customerReference = null
    ): string {
        $amountInCents = intval($amount * 100);
        
        $path = "/?payment={$this->merchantId}.{$orderId}.{$amountInCents}.{$currency}";
        
        if ($customerReference) {
            $path .= ".{$customerReference}";
        }
        
        return hash_hmac('sha256', $path, $this->apiKey);
    }

    /**
     * Generate HPP (Hosted Payment Page) URL
     */
    public function getHppUrl(
        string $orderId,
        float $amount,
        string $currency,
        string $callbackUrl,
        array $customerData = [],
        array $metadata = []
    ): string {
        $amountInCents = intval($amount * 100);
        
        $params = [
            'merchantId' => $this->merchantId,
            'orderId' => $orderId,
            'amount' => $amountInCents,
            'currency' => $currency,
            'hash' => $this->generateOrderHash($orderId, $amount, $currency),
            'mode' => $this->isLiveMode ? 'live' : 'test',
            'metaData' => json_encode($metadata),
            'redirectUrl' => $callbackUrl,
            'brandColor' => config('kashier.brand_color', '#3B82F6'),
            'type' => 'external',
        ];
        
        // Add customer data if provided
        if (!empty($customerData)) {
            $params = array_merge($params, [
                'customerName' => $customerData['name'] ?? '',
                'customerEmail' => $customerData['email'] ?? '',
                'customerPhone' => $customerData['phone'] ?? '',
            ]);
        }
        
        return $this->baseUrl . '?' . http_build_query($params);
    }

    /**
     * Validate webhook/callback signature
     */
    public function validateSignature(array $data): bool
    {
        if (!isset($data['signature'])) {
            Log::warning('Kashier: No signature provided in callback');
            return false;
        }
        
        $receivedSignature = $data['signature'];
        
        // Build string to hash
        $signatureData = [
            $data['orderId'] ?? '',
            $data['amount'] ?? '',
            $data['currency'] ?? '',
            $data['paymentStatus'] ?? '',
        ];
        
        $stringToHash = implode('.', $signatureData);
        $calculatedSignature = hash_hmac('sha256', $stringToHash, $this->secretKey);
        
        $isValid = hash_equals($calculatedSignature, $receivedSignature);
        
        if (!$isValid) {
            Log::warning('Kashier: Invalid signature', [
                'received' => $receivedSignature,
                'calculated' => $calculatedSignature,
                'data' => $signatureData,
            ]);
        }
        
        return $isValid;
    }

    /**
     * Process refund
     */
    public function refund(
        string $transactionId,
        float $amount,
        ?string $reason = null
    ): array {
        $endpoint = "{$this->baseUrl}/api/refund";
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'merchantId' => $this->merchantId,
            'transactionId' => $transactionId,
            'amount' => intval($amount * 100),
            'reason' => $reason ?? 'Customer request',
        ]);
        
        Log::channel('payments')->info('Kashier refund request', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => $response->status(),
        ]);
        
        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }
        
        return [
            'success' => false,
            'error' => $response->json()['message'] ?? 'Refund failed',
            'code' => $response->status(),
        ];
    }

    /**
     * Get refund status
     */
    public function getRefundStatus(string $orderId, string $transactionId): array
    {
        $endpoint = "{$this->baseUrl}/api/refund/status";
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->get($endpoint, [
            'merchantId' => $this->merchantId,
            'orderId' => $orderId,
            'transactionId' => $transactionId,
        ]);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        return [
            'status' => 'unknown',
            'error' => $response->json()['message'] ?? 'Failed to get status',
        ];
    }

    /**
     * Get payment details
     */
    public function getPaymentDetails(string $transactionId): ?array
    {
        $endpoint = "{$this->baseUrl}/api/transaction";
        
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->get($endpoint, [
            'merchantId' => $this->merchantId,
            'transactionId' => $transactionId,
        ]);
        
        return $response->successful() ? $response->json() : null;
    }
}
```

### PaymentService

```php
<?php
// app/Services/PaymentService.php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\Kashier\KashierClient;
use App\Events\PaymentInitiated;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        protected KashierClient $kashier
    ) {}

    /**
     * Initiate a new payment
     */
    public function initiatePayment(
        User $user,
        float $amount,
        string $paymentMethod,
        ?int $orderId = null,
        array $metadata = []
    ): array {
        return DB::transaction(function () use ($user, $amount, $paymentMethod, $orderId, $metadata) {
            
            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => 'EGP',
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'gateway' => 'kashier',
                'gateway_metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Log initiation
            PaymentTransaction::log(
                paymentId: $payment->id,
                type: 'authorize',
                amount: $amount,
                status: 'pending',
                request: [
                    'payment_method' => $paymentMethod,
                    'metadata' => $metadata,
                ]
            );

            // Generate HPP URL
            $hppUrl = $this->kashier->getHppUrl(
                orderId: $payment->reference,
                amount: $amount,
                currency: 'EGP',
                callbackUrl: route('payment.callback'),
                customerData: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                metadata: array_merge($metadata, [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                ])
            );

            $payment->update([
                'gateway_reference' => $payment->reference,
            ]);

            Log::channel('payments')->info('Payment initiated', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'amount' => $amount,
                'method' => $paymentMethod,
            ]);

            event(new PaymentInitiated($payment));

            return [
                'success' => true,
                'payment' => $payment,
                'redirect_url' => $hppUrl,
            ];
        });
    }

    /**
     * Handle payment callback
     */
    public function handleCallback(array $data): array
    {
        // Validate signature
        if (!$this->kashier->validateSignature($data)) {
            Log::warning('Invalid signature in payment callback', $data);
            
            return [
                'success' => false,
                'error' => 'Invalid signature',
            ];
        }

        // Find payment
        $payment = Payment::where('gateway_reference', $data['orderId'])
            ->orWhere('reference', $data['orderId'])
            ->first();

        if (!$payment) {
            Log::error('Payment not found for callback', ['order_id' => $data['orderId']]);
            
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        // Log transaction
        PaymentTransaction::log(
            paymentId: $payment->id,
            type: 'capture',
            amount: floatval($data['amount'] ?? 0) / 100,
            status: $data['paymentStatus'] === 'SUCCESS' ? 'success' : 'failed',
            response: $data
        );

        // Update payment status
        if ($data['paymentStatus'] === 'SUCCESS') {
            $payment->markAsCompleted(
                transactionId: $data['transactionId'] ?? $data['orderId'],
                metadata: $data
            );
            
            $payment->user->notify(new PaymentSuccessNotification($payment));
            
            Log::channel('payments')->info('Payment completed', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
            ]);
        } else {
            $payment->markAsFailed(
                reason: $data['failureReason'] ?? 'Payment declined',
                code: $data['failureCode'] ?? null
            );
            
            $payment->user->notify(new PaymentFailedNotification($payment));
            
            Log::channel('payments')->warning('Payment failed', [
                'payment_id' => $payment->id,
                'reason' => $payment->failure_reason,
            ]);
        }

        return [
            'success' => true,
            'payment' => $payment->fresh(),
        ];
    }

    /**
     * Process refund
     */
    public function refund(Payment $payment, ?float $amount = null, ?string $reason = null): array
    {
        if (!$payment->canBeRefunded()) {
            return [
                'success' => false,
                'error' => 'Payment cannot be refunded',
            ];
        }

        $refundAmount = $amount ?? ($payment->amount - $payment->refunded_amount);
        
        if ($refundAmount > ($payment->amount - $payment->refunded_amount)) {
            return [
                'success' => false,
                'error' => 'Refund amount exceeds available balance',
            ];
        }

        // Call Kashier API
        $result = $this->kashier->refund(
            transactionId: $payment->transaction_id,
            amount: $refundAmount,
            reason: $reason
        );

        // Log transaction
        PaymentTransaction::log(
            paymentId: $payment->id,
            type: 'refund',
            amount: $refundAmount,
            status: $result['success'] ? 'success' : 'failed',
            request: ['amount' => $refundAmount, 'reason' => $reason],
            response: $result['data'] ?? null,
            notes: $result['error'] ?? null
        );

        if ($result['success']) {
            $payment->markAsRefunded(
                amount: $refundAmount,
                reference: $result['data']['refundId'] ?? 'REF-' . time()
            );

            Log::channel('payments')->info('Refund processed', [
                'payment_id' => $payment->id,
                'amount' => $refundAmount,
            ]);
        }

        return $result;
    }
}
```

---

## Controllers و Routes

### PaymentController

```php
<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Http\Requests\InitiatePaymentRequest;
use App\Services\PaymentService;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Initiate payment
     */
    public function initiate(InitiatePaymentRequest $request)
    {
        $result = $this->paymentService->initiatePayment(
            user: auth()->user(),
            amount: $request->amount,
            paymentMethod: $request->payment_method,
            orderId: $request->order_id,
            metadata: $request->metadata ?? []
        );

        if ($result['success']) {
            return redirect()->away($result['redirect_url']);
        }

        return back()->with('error', 'Failed to initiate payment');
    }

    /**
     * Handle callback from Kashier
     */
    public function callback(Request $request)
    {
        $result = $this->paymentService->handleCallback($request->all());

        if ($result['success']) {
            $payment = $result['payment'];
            
            return redirect()->route('payment.result', [
                'status' => $payment->status,
                'reference' => $payment->reference,
            ]);
        }

        return redirect()->route('payment.result', [
            'status' => 'failed',
            'error' => $result['error'] ?? 'Payment verification failed',
        ]);
    }

    /**
     * Show payment result page
     */
    public function result(Request $request)
    {
        $status = $request->query('status');
        $reference = $request->query('reference');
        
        $payment = null;
        if ($reference) {
            $payment = Payment::where('reference', $reference)
                ->with('order')
                ->first();
        }

        return view('payments.result', [
            'status' => $status,
            'payment' => $payment,
            'error' => $request->query('error'),
        ]);
    }

    /**
     * Process refund
     */
    public function refund(Request $request, Payment $payment)
    {
        $this->authorize('refund', $payment);

        $result = $this->paymentService->refund(
            payment: $payment,
            amount: $request->amount,
            reason: $request->reason
        );

        if ($result['success']) {
            return back()->with('success', 'Refund processed successfully');
        }

        return back()->with('error', $result['error']);
    }
}
```

### InitiatePaymentRequest

```php
<?php
// app/Http/Requests/InitiatePaymentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:10', 'max:100000'],
            'payment_method' => ['required', 'string', 'in:card,vodafone_cash,orange_money,etisalat_cash,meeza_wallet,bank_installment,valu,souhoola'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'الحد الأدنى للدفع هو 10 جنيه',
            'amount.max' => 'الحد الأقصى للدفع هو 100,000 جنيه',
            'payment_method.in' => 'وسيلة الدفع المحددة غير مدعومة',
        ];
    }
}
```

### Routes

```php
<?php
// routes/web.php

use App\Http\Controllers\PaymentController;

// Payment routes (authenticated)
Route::middleware(['auth'])->prefix('payment')->group(function () {
    
    Route::post('/initiate', [PaymentController::class, 'initiate'])
        ->name('payment.initiate')
        ->middleware('throttle:payments');
    
    Route::get('/result', [PaymentController::class, 'result'])
        ->name('payment.result');
    
    Route::post('/{payment}/refund', [PaymentController::class, 'refund'])
        ->name('payment.refund')
        ->middleware('can:refund,payment');
});

// Callback route (no auth, no CSRF)
Route::get('/payment/callback', [PaymentController::class, 'callback'])
    ->name('payment.callback')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Webhook route (background processing)
Route::post('/webhooks/kashier', [PaymentController::class, 'webhook'])
    ->name('payment.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->middleware('verify.kashier.webhook');
```

### Rate Limiting

```php
<?php
// app/Providers/AppServiceProvider.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

public function boot(): void
{
    RateLimiter::for('payments', function (Request $request) {
        return $request->user()
            ? Limit::perMinute(5)->by($request->user()->id)
            : Limit::perMinute(3)->by($request->ip());
    });
}
```

---

## Middleware للأمان

### VerifyKashierWebhook Middleware

```php
<?php
// app/Http/Middleware/VerifyKashierWebhook.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Kashier\KashierClient;
use Illuminate\Support\Facades\Log;

class VerifyKashierWebhook
{
    public function __construct(
        protected KashierClient $kashier
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Verify signature
        if (!$this->kashier->validateSignature($request->all())) {
            Log::warning('Invalid Kashier webhook signature', [
                'ip' => $request->ip(),
                'data' => $request->all(),
            ]);
            
            abort(403, 'Invalid signature');
        }

        // Log webhook receipt
        Log::channel('payments')->info('Kashier webhook received', [
            'order_id' => $request->input('orderId'),
            'status' => $request->input('paymentStatus'),
        ]);

        return $next($request);
    }
}
```

تسجيل الـ Middleware:

```php
<?php
// app/Http/Kernel.php

protected $middlewareAliases = [
    // ... existing middleware
    'verify.kashier.webhook' => \App\Http\Middleware\VerifyKashierWebhook::class,
];
```

### EnsureIdempotency Middleware

```php
<?php
// app/Http/Middleware/EnsureIdempotency.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EnsureIdempotency
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('Idempotency-Key');
        
        if (!$key) {
            return $next($request);
        }
        
        $cacheKey = "idempotency:{$key}";
        
        // Check if we've seen this key before
        if ($cached = Cache::get($cacheKey)) {
            return response()->json($cached['data'], $cached['status'])
                ->header('Idempotency-Replayed', 'true');
        }
        
        // Process the request
        $response = $next($request);
        
        // Cache the response for 24 hours
        Cache::put($cacheKey, [
            'data' => $response->getData(),
            'status' => $response->getStatusCode(),
        ], now()->addHours(24));
        
        return $response;
    }
}
```

---

## Webhook Handling

### Webhook Controller Method

```php
<?php
// في PaymentController

/**
 * Handle webhook (asynchronous processing)
 */
public function webhook(Request $request)
{
    // معالجة في الخلفية للسرعة
    \App\Jobs\ProcessPaymentCallback::dispatch($request->all());
    
    // استجابة فورية
    return response()->json(['status' => 'received'], 200);
}
```

### ProcessPaymentCallback Job

```php
<?php
// app/Jobs/ProcessPaymentCallback.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;

class ProcessPaymentCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // Retry after 60 seconds

    public function __construct(
        public array $data
    ) {}

    public function handle(PaymentService $paymentService): void
    {
        try {
            $result = $paymentService->handleCallback($this->data);
            
            if (!$result['success']) {
                Log::error('Payment callback processing failed', [
                    'data' => $this->data,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception in payment callback processing', [
                'data' => $this->data,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to trigger retry
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('Payment callback processing failed permanently', [
            'data' => $this->data,
            'exception' => $exception->getMessage(),
        ]);
        
        // يمكنك إرسال تنبيه للإدارة هنا
    }
}
```

---

## Filament 4 Resources

### PaymentResource

```php
<?php
// app/Filament/Resources/PaymentResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'المالية';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('معلومات الدفع')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->label('الرقم المرجعي')
                        ->required()
                        ->disabled()
                        ->columnSpanFull(),
                    
                    Forms\Components\Select::make('user_id')
                        ->label('المستخدم')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required()
                        ->disabled(),
                    
                    Forms\Components\Select::make('order_id')
                        ->label('الطلب')
                        ->relationship('order', 'reference')
                        ->searchable(),
                    
                    Forms\Components\TextInput::make('amount')
                        ->label('المبلغ')
                        ->numeric()
                        ->prefix('EGP')
                        ->required()
                        ->disabled(),
                    
                    Forms\Components\Select::make('payment_method')
                        ->label('وسيلة الدفع')
                        ->options([
                            'card' => 'بطاقة ائتمانية',
                            'vodafone_cash' => 'فودافون كاش',
                            'orange_money' => 'أورانج موني',
                            'etisalat_cash' => 'اتصالات كاش',
                            'meeza_wallet' => 'ميزة والت',
                            'bank_installment' => 'تقسيط بنكي',
                            'valu' => 'ValU',
                            'souhoola' => 'Souhoola',
                        ])
                        ->required()
                        ->disabled(),
                    
                    Forms\Components\Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'pending' => 'قيد الانتظار',
                            'processing' => 'جاري المعالجة',
                            'completed' => 'مكتمل',
                            'failed' => 'فشل',
                            'refunded' => 'مسترد',
                            'partially_refunded' => 'مسترد جزئياً',
                            'cancelled' => 'ملغي',
                        ])
                        ->required()
                        ->disabled(),
                ])->columns(2),
            
            Forms\Components\Section::make('معلومات البوابة')
                ->schema([
                    Forms\Components\TextInput::make('transaction_id')
                        ->label('رقم المعاملة')
                        ->disabled(),
                    
                    Forms\Components\TextInput::make('gateway_reference')
                        ->label('المرجع في البوابة')
                        ->disabled(),
                    
                    Forms\Components\Textarea::make('failure_reason')
                        ->label('سبب الفشل')
                        ->disabled()
                        ->columnSpanFull()
                        ->visible(fn ($record) => $record?->isFailed()),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('المرجع')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EGP'),
                    ]),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('وسيلة الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'card' => 'بطاقة',
                        'vodafone_cash' => 'فودافون',
                        'orange_money' => 'أورانج',
                        'etisalat_cash' => 'اتصالات',
                        'meeza_wallet' => 'ميزة',
                        'valu' => 'ValU',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->colors([
                        'danger' => 'card',
                        'warning' => ['vodafone_cash', 'orange_money', 'etisalat_cash'],
                        'success' => 'valu',
                    ]),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'قيد الانتظار',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => ['refunded', 'partially_refunded'],
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'completed' => 'مكتمل',
                        'failed' => 'فشل',
                        'refunded' => 'مسترد',
                    ])
                    ->multiple(),
                
                SelectFilter::make('payment_method')
                    ->label('وسيلة الدفع')
                    ->options([
                        'card' => 'بطاقة ائتمانية',
                        'vodafone_cash' => 'فودافون كاش',
                        'valu' => 'ValU',
                    ])
                    ->multiple(),
                
                Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('من')
                            ->numeric()
                            ->prefix('EGP'),
                        Forms\Components\TextInput::make('amount_to')
                            ->label('إلى')
                            ->numeric()
                            ->prefix('EGP'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['amount_from'], fn ($q, $v) => $q->where('amount', '>=', $v))
                            ->when($data['amount_to'], fn ($q, $v) => $q->where('amount', '<=', $v));
                    }),
                
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من'),
                        Forms\Components\DatePicker::make('until')->label('إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('refund')
                    ->label('استرداد')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('استرداد المدفوعات')
                    ->modalDescription(fn (Payment $record) => 
                        "هل تريد استرداد {$record->amount} جنيه؟"
                    )
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->required()
                            ->default(fn (Payment $record) => $record->amount - $record->refunded_amount)
                            ->prefix('EGP'),
                        Forms\Components\Textarea::make('reason')
                            ->label('السبب')
                            ->rows(2),
                    ])
                    ->visible(fn (Payment $record) => $record->canBeRefunded())
                    ->action(function (Payment $record, array $data) {
                        $service = app(PaymentService::class);
                        $result = $service->refund(
                            payment: $record,
                            amount: $data['amount'],
                            reason: $data['reason'] ?? null
                        );
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('تم الاسترداد بنجاح')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل الاسترداد')
                                ->body($result['error'])
                                ->danger()
                                ->send();
                        }
                    }),
                
                Tables\Actions\ViewAction::make()->label('عرض'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('تصدير')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($records) {
                        // Export logic here
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات أساسية')
                    ->schema([
                        Infolists\Components\TextEntry::make('reference')
                            ->label('الرقم المرجعي')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('المستخدم'),
                        Infolists\Components\TextEntry::make('amount')
                            ->label('المبلغ')
                            ->money('EGP'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge(),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('وسيلة الدفع'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('معلومات المعاملة')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_id')
                            ->label('رقم المعاملة')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('gateway_reference')
                            ->label('مرجع البوابة'),
                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->dateTime()
                            ->placeholder('لم يتم الدفع بعد'),
                    ])
                    ->columns(3)
                    ->collapsible(),
                
                Infolists\Components\Section::make('المعاملات')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('transactions')
                            ->label('سجل المعاملات')
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->label('النوع'),
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('المبلغ')
                                    ->money('EGP'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('الحالة')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('التاريخ')
                                    ->dateTime(),
                            ])
                            ->columns(4),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::pending()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::pending()->count();
        return $count > 10 ? 'danger' : 'warning';
    }
}
```

### PaymentStatsWidget

```php
<?php
// app/Filament/Widgets/PaymentStatsWidget.php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PaymentStatsWidget extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Today's revenue
        $todayRevenue = Payment::completed()
            ->whereDate('created_at', today())
            ->sum('amount');

        $yesterdayRevenue = Payment::completed()
            ->whereDate('created_at', today()->subDay())
            ->sum('amount');

        $todayChange = $yesterdayRevenue > 0 
            ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100
            : 0;

        // Monthly revenue
        $monthlyRevenue = Payment::completed()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $lastMonthRevenue = Payment::completed()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');

        $monthlyChange = $lastMonthRevenue > 0
            ? (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        // Pending & Failed
        $pendingCount = Payment::pending()->count();
        $failedToday = Payment::where('status', 'failed')
            ->whereDate('created_at', today())
            ->count();

        // Success rate
        $totalToday = Payment::whereDate('created_at', today())->count();
        $successToday = Payment::completed()
            ->whereDate('created_at', today())
            ->count();
        $successRate = $totalToday > 0 ? ($successToday / $totalToday) * 100 : 0;

        return [
            Stat::make('إيرادات اليوم', Number::currency($todayRevenue, 'EGP'))
                ->description(sprintf('%+.1f%% عن الأمس', $todayChange))
                ->descriptionIcon($todayChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayChange >= 0 ? 'success' : 'danger')
                ->chart($this->getWeeklyChart()),
            
            Stat::make('إيرادات الشهر', Number::currency($monthlyRevenue, 'EGP'))
                ->description(sprintf('%+.1f%% عن الشهر الماضي', $monthlyChange))
                ->descriptionIcon($monthlyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyChange >= 0 ? 'success' : 'danger'),
            
            Stat::make('مدفوعات قيد الانتظار', $pendingCount)
                ->description('تحتاج للمراجعة')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 10 ? 'warning' : 'gray'),
            
            Stat::make('معدل النجاح اليوم', sprintf('%.1f%%', $successRate))
                ->description("{$successToday} من {$totalToday} معاملة")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger')),
        ];
    }

    protected function getWeeklyChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $data[] = Payment::completed()
                ->whereDate('created_at', $date)
                ->sum('amount');
        }
        return $data;
    }
}
```

---

## Testing Strategy

### Feature Test للمدفوعات

```php
<?php
// tests/Feature/PaymentTest.php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_initiate_payment()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->actingAs($this->user)
            ->postJson('/payment/initiate', [
                'amount' => 100.00,
                'payment_method' => 'vodafone_cash',
                'order_id' => $order->id,
            ]);
        
        $response->assertStatus(302); // Redirect to payment gateway
        
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'amount' => 100.00,
            'status' => 'pending',
            'payment_method' => 'vodafone_cash',
        ]);
    }

    public function test_payment_amount_must_be_within_limits()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/payment/initiate', [
                'amount' => 5.00, // Below minimum
                'payment_method' => 'card',
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('amount');
    }

    public function test_callback_updates_payment_status()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'gateway_reference' => 'TEST-123',
        ]);
        
        // Simulate Kashier callback
        $callbackData = [
            'orderId' => $payment->gateway_reference,
            'transactionId' => 'TXN-456',
            'paymentStatus' => 'SUCCESS',
            'amount' => '10000', // 100.00 EGP in cents
            'currency' => 'EGP',
            'signature' => $this->generateValidSignature($payment),
        ];
        
        $response = $this->getJson('/payment/callback?' . http_build_query($callbackData));
        
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
            'transaction_id' => 'TXN-456',
        ]);
    }

    public function test_invalid_signature_rejects_callback()
    {
        $payment = Payment::factory()->create([
            'status' => 'pending',
        ]);
        
        $callbackData = [
            'orderId' => $payment->gateway_reference,
            'paymentStatus' => 'SUCCESS',
            'signature' => 'invalid-signature',
        ];
        
        $response = $this->getJson('/payment/callback?' . http_build_query($callbackData));
        
        // Payment should remain pending
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
        ]);
    }

    public function test_refund_updates_payment_correctly()
    {
        $payment = Payment::factory()->completed()->create([
            'amount' => 100.00,
            'refunded_amount' => 0,
        ]);
        
        $response = $this->actingAs($payment->user)
            ->postJson("/payment/{$payment->id}/refund", [
                'amount' => 50.00,
                'reason' => 'Customer request',
            ]);
        
        $payment->refresh();
        
        $this->assertEquals(50.00, $payment->refunded_amount);
        $this->assertEquals('partially_refunded', $payment->status);
    }

    public function test_rate_limiting_blocks_excessive_requests()
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($this->user)
                ->postJson('/payment/initiate', [
                    'amount' => 100,
                    'payment_method' => 'card',
                ]);
        }
        
        $response->assertStatus(429); // Too Many Requests
    }

    protected function generateValidSignature(Payment $payment): string
    {
        // Implement signature generation according to Kashier's spec
        $client = app(\App\Services\Kashier\KashierClient::class);
        return $client->generateOrderHash(
            $payment->gateway_reference,
            $payment->amount,
            'EGP'
        );
    }
}
```

---

## أمثلة كود عملية كاملة

### مثال 1: دفع بسيط باستخدام فودافون كاش

```php
<?php
// في Controller أو Service

use App\Services\PaymentService;
use Illuminate\Http\Request;

public function checkout(Request $request)
{
    $user = auth()->user();
    $cart = $user->cart; // سلة المشتريات
    
    // حساب المجموع
    $total = $cart->items->sum(fn($item) => $item->price * $item->quantity);
    
    // إنشاء طلب
    $order = Order::create([
        'user_id' => $user->id,
        'total' => $total,
        'status' => 'pending',
    ]);
    
    // نسخ المنتجات من السلة للطلب
    foreach ($cart->items as $item) {
        $order->items()->create([
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'price' => $item->price,
        ]);
    }
    
    // بدء الدفع
    $paymentService = app(PaymentService::class);
    $result = $paymentService->initiatePayment(
        user: $user,
        amount: $total,
        paymentMethod: $request->payment_method, // 'vodafone_cash'
        orderId: $order->id,
        metadata: [
            'order_reference' => $order->reference,
            'items_count' => $order->items->count(),
        ]
    );
    
    // إعادة توجيه للدفع
    return redirect($result['redirect_url']);
}
```

### مثال 2: معالجة Callback وتحديث الطلب

```php
<?php
// في PaymentController

use App\Services\PaymentService;
use App\Models\Order;

public function callback(Request $request)
{
    $paymentService = app(PaymentService::class);
    $result = $paymentService->handleCallback($request->all());
    
    if ($result['success']) {
        $payment = $result['payment'];
        
        // تحديث حالة الطلب
        if ($payment->isCompleted() && $payment->order) {
            $payment->order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            
            // إرسال بريد تأكيد
            $payment->user->notify(new OrderConfirmationNotification($payment->order));
            
            // تفريغ السلة
            $payment->user->cart?->items()->delete();
        }
        
        return redirect()->route('orders.show', $payment->order)
            ->with('success', 'تم الدفع بنجاح! شكراً لك.');
    }
    
    return redirect()->route('checkout')
        ->with('error', 'فشل الدفع. يرجى المحاولة مرة أخرى.');
}
```

### مثال 3: استخدام Filament Action لاسترداد المبالغ

```php
<?php
// في PaymentResource

Tables\Actions\Action::make('partial_refund')
    ->label('استرداد جزئي')
    ->icon('heroicon-o-arrow-path')
    ->color('warning')
    ->form([
        Forms\Components\TextInput::make('amount')
            ->label('المبلغ المراد استرداده')
            ->numeric()
            ->required()
            ->minValue(1)
            ->maxValue(fn (Payment $record) => $record->amount - $record->refunded_amount)
            ->prefix('EGP')
            ->helperText(fn (Payment $record) => 
                "المتاح للاسترداد: " . number_format($record->amount - $record->refunded_amount, 2) . " جنيه"
            ),
        
        Forms\Components\Select::make('reason')
            ->label('السبب')
            ->options([
                'customer_request' => 'طلب العميل',
                'product_issue' => 'مشكلة في المنتج',
                'duplicate_payment' => 'دفع مكرر',
                'other' => 'أخرى',
            ])
            ->required(),
        
        Forms\Components\Textarea::make('notes')
            ->label('ملاحظات')
            ->rows(2),
    ])
    ->visible(fn (Payment $record) => 
        $record->canBeRefunded() && $record->refunded_amount < $record->amount
    )
    ->action(function (Payment $record, array $data) {
        $service = app(PaymentService::class);
        $result = $service->refund(
            payment: $record,
            amount: $data['amount'],
            reason: $data['reason'] . ($data['notes'] ? ': ' . $data['notes'] : '')
        );
        
        if ($result['success']) {
            Notification::make()
                ->title('تم الاسترداد بنجاح')
                ->body("تم استرداد {$data['amount']} جنيه")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('فشل الاسترداد')
                ->body($result['error'])
                ->danger()
                ->send();
        }
    })
```

---

## الخلاصة والتوصيات

### ملخص التكامل

لتكامل **Kashier** الكامل في مشروع Laravel 12 مع Filament 4:

#### 1. التحضير
- ✅ التسجيل في Kashier والحصول على API Keys
- ✅ اختيار الـ Package المناسب (`madarit/laravel-kashier` موصى به)
- ✅ إعداد بيئة الاختبار (Sandbox)

#### 2. التطبيق
- ✅ تثبيت الـ Package
- ✅ إنشاء Migrations للـ Payments
- ✅ بناء Models مع Relationships
- ✅ تطوير Service Layer (KashierClient + PaymentService)
- ✅ إنشاء Controllers و Routes
- ✅ تطبيق Middleware للأمان
- ✅ إعداد Webhook Handling
- ✅ بناء Filament Resources

#### 3. الأمان
- ✅ Signature Validation لجميع Callbacks
- ✅ Rate Limiting للطلبات
- ✅ Idempotency للمعاملات
- ✅ Encryption لبيانات البوابة
- ✅ Logging شامل

#### 4. الاختبار
- ✅ Unit Tests للـ Services
- ✅ Feature Tests للـ Flows الكاملة
- ✅ اختبار Webhooks
- ✅ اختبار Refunds

### المقارنة النهائية: Kashier vs Paymob

| الميزة | Kashier ⭐ | Paymob |
|--------|-----------|--------|
| **الأفضل لـ** | الأعمال الصغيرة والمتوسطة | جميع الأحجام |
| **الدعم الفني** | ممتاز جداً | جيد |
| **الرسوم** | 2.85% + 3 EGP | 2.75% + 3 EGP |
| **سهولة الاستخدام** | سهل جداً | سهل |
| **الاستقرار** | ممتاز | جيد جداً |
| **فودافون كاش** | ✅ | ✅ |
| **إنستاباي** | ❌ قريباً | ❌ قريباً |
| **Laravel Packages** | حديثة ومحدثة | متعددة |

### التوصية النهائية

**استخدم Kashier إذا:**
- ✅ تبحث عن دعم فني استثنائي
- ✅ تريد استقراراً عالياً
- ✅ عملاؤك في مصر فقط
- ✅ تفضل التعامل مع شركة محلية

**استخدم Paymob إذا:**
- ✅ تحتاج خيارات BNPL أكثر
- ✅ تخطط للتوسع إقليمياً
- ✅ الفرق في الرسوم مهم جداً لك

### نقطة مهمة عن InstaPay

**InstaPay غير متاح حالياً** كـ API للمطورين من أي بوابة دفع. الحلول الحالية:
1. **QR Code** - يمكن للعميل الدفع من تطبيق InstaPay
2. **Payment Links** - إرسال رابط دفع مباشر
3. **الانتظار** - حتى تطلق Kashier أو Paymob الدعم الرسمي

---

## الموارد والروابط

### Kashier
- **الموقع الرئيسي:** https://www.kashier.io
- **لوحة التحكم:** https://merchant.kashier.io / https://portal.kashier.io
- **التوثيق:** https://developers.kashier.io
- **FAQs:** https://www.kashier.io/en/faqs

### Laravel Packages
- **madarit/laravel-kashier:** https://packagist.org/packages/madarit/laravel-kashier
- **madarit/kashier-laravel-sdk:** https://packagist.org/packages/madarit/kashier-laravel-sdk
- **nafezly/payments:** https://github.com/Nafezly/payments

### Laravel & Filament
- **Laravel 12 Docs:** https://laravel.com/docs/12.x
- **Filament 4 Docs:** https://filamentphp.com/docs/4.x

### GitHub Examples
- **Kashier WooCommerce:** https://github.com/Kashier-payments/Kashier-WooCommerce-UI-Plugin
- **Kashier Magento:** https://github.com/Kashier-payments/Kashier_Magento_2.3x_Plugin
- **Kashier Odoo:** https://github.com/Kashier-payments/Kashier-Odoo-Payment-Add-on

---

## الخاتمة

هذا الدليل يغطي **كل شيء** تحتاجه لتكامل Kashier الكامل في Laravel 12 مع Filament 4، مع دعم شامل لفودافون كاش وجميع وسائل الدفع المصرية الأخرى. 

الكود المقدم **جاهز للإنتاج** ويتبع أفضل الممارسات في:
- Architecture (Service Layer Pattern)
- Security (Signature Validation, Rate Limiting)
- Testing (Unit & Feature Tests)
- Admin Panel (Filament Resources & Widgets)

**بالتوفيق في مشروعك! 🚀**
