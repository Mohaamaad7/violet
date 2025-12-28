# 🔴 تشخيص وحل مشكلة عدم تحديث حالة الطلب بعد الدفع

**التاريخ:** 28 ديسمبر 2025  
**المشكلة:** الدفع ناجح في Paymob لكن الموقع لا يحدث حالة الطلب ولا يرسل إيميلات

---

## 🔍 التشخيص

### المشكلة الرئيسية المكتشفة:

من خلال فحص صورة Integration Details (الصورة الثالثة)، وجدنا أن **Callback URLs في Paymob Dashboard خاطئة**:

```
❌ الموجود حالياً:
Transaction processed callback: https://accept.paymobsolutions.com/api/acceptance/post_pay
Transaction response callback: https://accept.paymobsolutions.com/api/acceptance/post_pay
```

هذه URLs موجهة لـ Paymob نفسها! لذلك:
- ✅ Paymob تسجل الدفع كـ "Successful"
- ❌ Paymob **لا ترسل إشعار** لموقعك
- ❌ موقعك **لا يعرف** أن الدفع نجح
- ❌ الطلب يظل "قيد الدفع"
- ❌ لا يتم إرسال إيميلات

---

## ✅ الحل - خطوات التنفيذ

### الخطوة 1: تحديث Callback URLs في Paymob Dashboard

يجب تعديل كل Integration على حدة (Online Card, Mobile Wallet, Kiosk):

#### 1. افتح Paymob Dashboard
https://accept.paymob.com/portal2/en/home

#### 2. اذهب إلى: Developers → Payment Integrations

#### 3. لكل Integration (3 integrations):
- اضغط على Integration ID (مثل 5443683)
- اضغط زر "Edit"
- ابحث عن قسم **"Integration Callbacks"**
- غيّر URLs كالتالي:

```
✅ URLs الصحيحة:

Transaction processed callback:
https://test.flowerviolet.com/payment/paymob/callback

Transaction response callback:
https://test.flowerviolet.com/payment/paymob/callback

Webhook URL (اختياري):
https://test.flowerviolet.com/payment/paymob/webhook
```

#### 4. احفظ التغييرات لكل Integration

---

### الخطوة 2: التحقق من Routes في الموقع

تأكد أن routes/web.php تحتوي على:

```php
Route::prefix('paymob')->name('paymob.')->group(function () {
    Route::match(['get', 'post'], '/callback', [PaymentController::class, 'paymobCallback'])
        ->name('callback')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    
    Route::post('/webhook', [PaymentController::class, 'paymobWebhook'])
        ->name('webhook')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
});
```

✅ **تم التحقق:** الـ Routes موجودة بشكل صحيح

---

### الخطوة 3: اختبار عملية دفع جديدة

بعد تحديث URLs في Paymob Dashboard:

1. **امسح الكاش:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

2. **ضع طلب جديد وادفع بالبطاقة**

3. **راقب Logs:**
```bash
tail -f storage/logs/laravel.log
```

4. **تحقق من النتائج:**
   - ✅ تحويل لصفحة النجاح: `/checkout/success/{order_id}`
   - ✅ حالة الطلب تتغير لـ "مدفوع"
   - ✅ إرسال إيميل للعميل
   - ✅ إرسال إيميل للأدمن

---

## 🔧 إصلاحات إضافية (إذا استمرت المشكلة)

### 1. التحقق من HMAC Secret

في Paymob Dashboard → Developers → Payment Integrations → Integration Details:
- انسخ **HMAC Secret**
- تأكد أنه مطابق لما في PaymentSettings في الموقع

### 2. التحقق من APP_URL

في `.env` الـ production:
```env
APP_URL=https://test.flowerviolet.com
```

يجب أن يكون **بدون** trailing slash

### 3. فحص Firewall/Security

تأكد أن السيرفر يسمح بـ incoming requests من Paymob IPs:
```
webhook IPs: 
- 197.34.35.0/24
- 197.34.36.0/24
```

---

## 📋 Checklist للتأكد

- [ ] تم تحديث Callback URLs في Paymob Dashboard (3 integrations)
- [ ] تم مسح الكاش (`php artisan cache:clear`)
- [ ] تم اختبار دفعة جديدة
- [ ] تم التحويل لصفحة النجاح
- [ ] تم تحديث حالة الطلب لـ "مدفوع"
- [ ] تم إرسال إيميل للعميل
- [ ] تم إرسال إيميل للأدمن

---

## 🐛 تتبع المشكلة (إذا استمرت)

إذا استمرت المشكلة بعد تحديث URLs، افتح `storage/logs/laravel.log` وابحث عن:

```log
[INFO] Paymob callback received
[INFO] Paymob: Processing callback
[INFO] Paymob: Payment completed
```

إذا **لم تظهر** هذه الرسائل → Paymob لا ترسل callback (تحقق من URLs مرة أخرى)

إذا **ظهرت** هذه الرسائل → المشكلة في منطق الكود (أخبرني لفحص السبب)

---

## 📝 ملاحظات هامة

### عن الطلبات القديمة (قيد الدفع):

الطلبات الموجودة حالياً (VLT-20251227-143347-000032 وغيرها) **لن تتحدث تلقائياً**، لأن:
1. Paymob **لن ترسل** callback مرة أخرى للمعاملات القديمة
2. الـ webhook يُرسل **مرة واحدة فقط** عند نجاح الدفع

**الحلول للطلبات القديمة:**

#### الخيار 1: تحديث يدوي (من Admin Panel)
1. افتح Order في Filament
2. غيّر `payment_status` من "pending" إلى "paid"
3. غيّر `status` من "pending_payment" إلى "pending"
4. أرسل إيميل يدوي للعميل

#### الخيار 2: تشغيل Script لتحديث الطلبات المدفوعة في Paymob:

```php
// في tinker أو route مؤقت
$paidTransactions = [
    '389201635', // Tmx ID من Paymob
    '389197572',
    '389191203',
];

foreach ($paidTransactions as $tmxId) {
    $payment = Payment::where('gateway_order_id', $tmxId)->first();
    
    if ($payment && $payment->status !== 'completed') {
        $payment->markAsCompleted($tmxId, []);
        $payment->order->update([
            'payment_status' => 'paid',
            'status' => 'pending',
            'paid_at' => now(),
        ]);
        
        // إرسال إيميلات
        $emailService = app(\App\Services\EmailService::class);
        $emailService->sendOrderConfirmation($payment->order);
        $emailService->sendAdminNewOrderNotification($payment->order);
    }
}
```

---

## 🎯 الخلاصة

**السبب:** Callback URLs خاطئة في Paymob Dashboard

**الحل:** 
1. تحديث URLs لكل Integration (3 integrations)
2. اختبار دفعة جديدة
3. تحديث الطلبات القديمة يدوياً أو بـ Script

**النتيجة المتوقعة:**
✅ دفعات جديدة ستعمل بشكل صحيح تلقائياً
