# 🔀 خطة تنفيذ نظام بوابات الدفع الديناميكي

## هدف المشروع

بناء نظام مرن يتيح لمدير المتجر اختيار بوابة الدفع المفعّلة (Kashier أو Paymob) من لوحة التحكم، مع إعدادات ديناميكية لكل بوابة.

---

## 📌 ملاحظات عامة

> [!IMPORTANT]
> ### أوامر Terminal
> جميع أوامر الـ Terminal سيتم طلبها من المستخدم وهو من سينفذها ويعطي النتيجة.

> [!WARNING]
> ### Filament v4
> هذا المشروع يستخدم **Filament v4**، وهناك اختلافات في المسارات والـ namespaces عن v3.
> يجب مراجعة [توثيق Filament v4](https://filamentphp.com/docs/4.x) عند العمل على صفحة الإعدادات.
> 
> **أمثلة على الاختلافات:**
> - `Filament\Forms\Components\...` بدل `Filament\Forms\...`
> - طريقة تسجيل الـ Pages مختلفة
> - بعض الـ methods قد تكون deprecated

> [!NOTE]
> ### تتبع التقدم
> ملف التقدم الخاص بهذه المهمة: `docs/dynamic_payment_gateway/PROGRESS.md`
> سيتم تحديثه فوراً بعد انتهاء كل مرحلة.

---

## 📋 المتطلبات

### البوابات المدعومة:
- ✅ **Kashier** (موجود ويعمل)
- 🆕 **Paymob** (سيتم إضافته)

### طرق الدفع المطلوبة في Paymob:
| الطريقة | Integration Type |
|---------|-----------------|
| Visa/Mastercard | Card |
| Meeza | Card |
| Digital Wallets | Wallet (Vodafone Cash, Orange, Etisalat) |
| InstaPay | Bank Transfer |
| Fawry/Kiosk | Kiosk |

### قواعد العمل:
- ✅ بوابة واحدة نشطة فقط في نفس الوقت
- ✅ إعدادات كل بوابة محفوظة في قاعدة البيانات
- ✅ المفاتيح السرية مشفرة
- ✅ دعم Test و Live modes

---

## ⚠️ ملاحظات تقنية حرجة (Fine-tuning)

> [!CAUTION]
> ### 1. فخ "القروش" مقابل "الجنيه" (The Cents Trap)
> - **Kashier**: يتعامل بالجنيه (`150.00`)
> - **Paymob**: يتعامل بالقروش (`15000`)
> 
> **الحل:** داخل `PaymobGateway.php` فقط، نضرب المبلغ في 100:
> ```php
> "amount_cents" => (int) ($order->total * 100), // Paymob requires integers
> ```
> **لا تضع هذا المنطق في `PaymentService`!** كل بوابة تعالج المبلغ بطريقتها.

> [!IMPORTANT]
> ### 2. Intention API مع Integration IDs محددة
> عند استخدام Intention API، يجب إرسال الـ Integration ID المناسب لطريقة الدفع المختارة:
> - اختار العميل "بطاقة" → أرسل `integration_id_card`
> - اختار العميل "محفظة" → أرسل `integration_id_wallet`
> - اختار العميل "فوري" → أرسل `integration_id_kiosk`
> 
> هذا يفتح للعميل الصفحة الصحيحة مباشرة بدون خيارات إضافية.

> [!NOTE]
> ### 3. فصل الـ Routes تماماً
> بدلاً من `/payment/callback/{gateway}`، نستخدم:
> ```
> /payment/kashier/callback
> /payment/paymob/callback
> ```
> **السبب:** Paymob قد ترسل parameters تتعارض مع توقعات Kashier.

> [!WARNING]
> ### 4. HMAC Validation في Paymob
> Paymob حساسة جداً لترتيب الحقول (lexical order):
> - استبعاد الحقول الفارغة
> - استبعاد nested arrays
> - الترتيب الأبجدي للمفاتيح
> 
> **الحل:** نسخ دالة الـ Hashing الرسمية من توثيق Paymob.

> [!TIP]
> ### 5. Filament UX مع Reactive
> - استخدام `reactive()` على Select الـ Active Gateway
> - عرض Badge "(Active)" بجوار التبويب المفعّل
> - رسالة تنبيه عند التبديل

---

## 🏗️ الهيكل المقترح

```
┌─────────────────────────────────────────────────────────┐
│                    Checkout Page                         │
│                   (يختار طريقة الدفع)                    │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                   PaymentService                         │
│              (الخدمة الرئيسية للدفع)                     │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                PaymentGatewayManager                     │
│    يحدد البوابة النشطة ويوجه الطلب للخدمة المناسبة       │
└────────────────────────┬────────────────────────────────┘
                         │
         ┌───────────────┴───────────────┐
         ▼                               ▼
┌─────────────────┐             ┌─────────────────┐
│  KashierGateway │             │  PaymobGateway  │
│    (موجود)      │             │    (جديد)       │
│                 │             │                 │
│ Amount: EGP     │             │ Amount: Cents   │
│ (150.00)        │             │ (15000)         │
│                 │             │                 │
│ implements      │             │ implements      │
│ PaymentGateway  │             │ PaymentGateway  │
│ Interface       │             │ Interface       │
└─────────────────┘             └─────────────────┘
```

---

## 📁 الملفات المطلوب إنشاؤها

### [NEW] `app/Contracts/PaymentGatewayInterface.php`

العقد المشترك لكل بوابات الدفع:

```php
<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * الاسم التقني للبوابة (kashier, paymob)
     */
    public function getName(): string;
    
    /**
     * الاسم المعروض للمستخدم
     */
    public function getDisplayName(): string;
    
    /**
     * هل البوابة مُعدّة وجاهزة؟
     */
    public function isConfigured(): bool;
    
    /**
     * اختبار الاتصال بالبوابة
     * @return array{success: bool, message: string}
     */
    public function testConnection(): array;
    
    /**
     * طرق الدفع المدعومة من هذه البوابة
     * @return array<string, array{name: string, name_en: string, icon: string}>
     */
    public function getSupportedMethods(): array;
    
    /**
     * بدء عملية الدفع
     * @param Order $order الطلب
     * @param string $method طريقة الدفع (card, wallet, kiosk)
     * @return array{success: bool, redirect_url?: string, error?: string}
     */
    public function initiatePayment(Order $order, string $method): array;
    
    /**
     * معالجة الـ Callback (redirect من البوابة)
     */
    public function handleCallback(array $data): array;
    
    /**
     * معالجة الـ Webhook (server-to-server)
     */
    public function handleWebhook(array $data): array;
    
    /**
     * استرداد مبلغ
     */
    public function refund(Payment $payment, float $amount, ?string $reason = null): array;
    
    /**
     * التحقق من صحة التوقيع
     */
    public function validateSignature(array $data): bool;
    
    /**
     * جلب Callback URL لهذه البوابة
     */
    public function getCallbackUrl(): string;
    
    /**
     * جلب Webhook URL لهذه البوابة
     */
    public function getWebhookUrl(): string;
}
```

---

### [NEW] `app/Services/PaymentGatewayManager.php`

المدير الذي يختار البوابة النشطة:

```php
<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentSetting;
use App\Services\Gateways\KashierGateway;
use App\Services\Gateways\PaymobGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    protected array $gateways = [];
    
    public function __construct()
    {
        // تسجيل البوابات المتاحة
        $this->gateways = [
            'kashier' => KashierGateway::class,
            'paymob' => PaymobGateway::class,
        ];
    }
    
    /**
     * جلب البوابة النشطة
     */
    public function getActiveGateway(): PaymentGatewayInterface
    {
        $activeGateway = PaymentSetting::get('active_gateway', 'kashier');
        return $this->getGateway($activeGateway);
    }
    
    /**
     * جلب اسم البوابة النشطة
     */
    public function getActiveGatewayName(): string
    {
        return PaymentSetting::get('active_gateway', 'kashier');
    }
    
    /**
     * جلب بوابة محددة بالاسم
     */
    public function getGateway(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new InvalidArgumentException("Gateway '{$name}' not found");
        }
        
        return app($this->gateways[$name]);
    }
    
    /**
     * قائمة البوابات المتاحة
     */
    public function getAvailableGateways(): array
    {
        return [
            'kashier' => 'Kashier',
            'paymob' => 'Paymob (Accept)',
        ];
    }
    
    /**
     * هل البوابة النشطة مُعدّة؟
     */
    public function isActiveGatewayConfigured(): bool
    {
        return $this->getActiveGateway()->isConfigured();
    }
}
```

---

### [NEW] `app/Services/Gateways/PaymobGateway.php`

خدمة Paymob الجديدة:

**الإعدادات المطلوبة من Paymob Dashboard:**
| الإعداد | الوصف | مشفر؟ |
|---------|-------|-------|
| `paymob_secret_key` | المفتاح السري للـ API | ✅ |
| `paymob_public_key` | المفتاح العام للـ Checkout | ❌ |
| `paymob_hmac_secret` | مفتاح التحقق من callbacks | ✅ |
| `paymob_integration_id_card` | Integration ID للبطاقات | ❌ |
| `paymob_integration_id_wallet` | Integration ID للمحافظ | ❌ |
| `paymob_integration_id_kiosk` | Integration ID لفوري | ❌ |

**الـ Flow:**
```
1. العميل يختار "الدفع بالبطاقة"
   ↓
2. initiatePayment($order, 'card')
   - يحسب المبلغ بالقروش: $order->total * 100
   - يحدد Integration ID المناسب: integration_id_card
   ↓
3. POST /v1/intention/ → Paymob
   - إرسال amount_cents, currency, payment_methods[], billing_data
   - استلام client_secret
   ↓
4. Redirect to Checkout URL
   https://accept.paymob.com/unifiedcheckout/?publicKey={public_key}&clientSecret={client_secret}
   ↓
5. العميل يدفع
   ↓
6. Paymob تعيد توجيه العميل إلى /payment/paymob/callback
   ↓
7. handleCallback()
   - التحقق من HMAC (بترتيب الحقول الصحيح!)
   - تحديث حالة الطلب
   ↓
8. إرسال العميل لصفحة النجاح
```

---

### [MOVE + MODIFY] `app/Services/KashierService.php` → `app/Services/Gateways/KashierGateway.php`

نقل الملف الحالي وتطبيق الـ Interface عليه.

---

### [MODIFY] `app/Models/PaymentSetting.php`

إضافة:
- مفاتيح Paymob للتشفير
- `getActiveGateway()` method
- `getPaymobConfig()` method

---

### [MODIFY] `app/Filament/Pages/PaymentSettings.php`

إعادة هيكلة الصفحة:
- Select للبوابة النشطة مع `reactive()`
- Tabs للبوابات (Kashier / Paymob)
- Badge "(Active)" للتبويب المفعّل
- حقول Paymob الجديدة
- زر اختبار الاتصال لكل بوابة

---

### [MODIFY] `app/Services/PaymentService.php`

تعديل لاستخدام Gateway Manager بدل Kashier مباشرة.

---

### [MODIFY] `app/Http/Controllers/PaymentController.php`

تحديث لدعم callbacks منفصلة لكل بوابة.

---

## 🔄 Routes المطلوبة

```php
// routes/web.php

// ============ Kashier Routes ============
Route::prefix('payment/kashier')->name('payment.kashier.')->group(function () {
    Route::get('/callback', [PaymentController::class, 'kashierCallback'])
        ->name('callback');
    Route::post('/webhook', [PaymentController::class, 'kashierWebhook'])
        ->name('webhook')
        ->withoutMiddleware(['web', 'csrf']);
});

// ============ Paymob Routes ============
Route::prefix('payment/paymob')->name('payment.paymob.')->group(function () {
    Route::get('/callback', [PaymentController::class, 'paymobCallback'])
        ->name('callback');
    Route::post('/webhook', [PaymentController::class, 'paymobWebhook'])
        ->name('webhook')
        ->withoutMiddleware(['web', 'csrf']);
});

// Legacy route for backwards compatibility
Route::get('/payment/callback', function () {
    $activeGateway = \App\Models\PaymentSetting::getActiveGateway();
    return redirect()->route("payment.{$activeGateway}.callback", request()->all());
})->name('payment.callback');
```

---

## ✅ خطة التنفيذ المرحلية

### المرحلة 1: البنية التحتية (2-3 ساعات)
- [ ] إنشاء `app/Contracts/PaymentGatewayInterface.php`
- [ ] إنشاء `app/Services/PaymentGatewayManager.php`
- [ ] إنشاء مجلد `app/Services/Gateways/`
- [ ] نقل وتعديل `KashierService` → `Gateways/KashierGateway.php`
- [ ] تعديل `PaymentService` لاستخدام Manager
- [ ] تحديث Routes (فصل Kashier routes)
- [ ] **اختبار أن Kashier لا يزال يعمل** ✅

### المرحلة 2: خدمة Paymob (3-4 ساعات)
- [ ] إنشاء `app/Services/Gateways/PaymobGateway.php`
- [ ] تطبيق الـ Interface methods
- [ ] تطبيق `initiatePayment()` مع Intention API
- [ ] تطبيق `handleCallback()` مع HMAC validation
- [ ] تطبيق `handleWebhook()`
- [ ] تطبيق `refund()`
- [ ] تطبيق `testConnection()`

### المرحلة 3: واجهة الإعدادات (2-3 ساعات)
- [ ] إنشاء Migration للإعدادات الجديدة
- [ ] تحديث `PaymentSetting` model
- [ ] إعادة هيكلة `PaymentSettings` Filament page (v4)
- [ ] إضافة Tabs + reactive() + Badge

### المرحلة 4: التكامل والـ Routes (1-2 ساعة)
- [ ] إضافة Paymob Routes
- [ ] تحديث `PaymentController.php`
- [ ] مراجعة `CheckoutPage.php`

### المرحلة 5: الاختبار والتوثيق (2-3 ساعات)
- [ ] اختبار Kashier
- [ ] اختبار Paymob في Test Mode
- [ ] اختبار التبديل بين البوابات
- [ ] تحديث التوثيق

---

## 📊 الملفات النهائية

| الحالة | الملف | الوصف |
|--------|-------|-------|
| 🆕 NEW | `app/Contracts/PaymentGatewayInterface.php` | العقد المشترك |
| 🆕 NEW | `app/Services/PaymentGatewayManager.php` | مدير البوابات |
| 🆕 NEW | `app/Services/Gateways/PaymobGateway.php` | خدمة Paymob |
| 📦 MOVE | `app/Services/Gateways/KashierGateway.php` | نقل من Services |
| ✏️ MODIFY | `app/Services/PaymentService.php` | استخدام Manager |
| ✏️ MODIFY | `app/Models/PaymentSetting.php` | إضافة Paymob config |
| ✏️ MODIFY | `app/Filament/Pages/PaymentSettings.php` | UI جديد (v4) |
| ✏️ MODIFY | `app/Http/Controllers/PaymentController.php` | دعم البوابتين |
| ✏️ MODIFY | `routes/web.php` | Routes منفصلة |
| 🆕 NEW | Migration | إعدادات Paymob |

---

## ⏱️ الوقت المتوقع

| المرحلة | الوقت |
|---------|-------|
| البنية التحتية | 2-3 ساعات |
| خدمة Paymob | 3-4 ساعات |
| واجهة الإعدادات | 2-3 ساعات |
| التكامل | 1-2 ساعة |
| الاختبار | 2-3 ساعات |
| **المجموع** | **10-15 ساعة** |

---

## 🎯 النتيجة النهائية

بعد التنفيذ، سيتمكن مدير المتجر من:

1. ✅ اختيار البوابة النشطة من Dropdown
2. ✅ رؤية Badge "(Active)" على التبويب المفعّل
3. ✅ إدخال إعدادات كل بوابة من لوحة التحكم
4. ✅ اختبار الاتصال قبل التفعيل
5. ✅ تفعيل/تعطيل طرق الدفع المختلفة
6. ✅ التبديل بين البوابات دون تعديل الكود
7. ✅ كل بوابة تعالج العملة بطريقتها الخاصة
