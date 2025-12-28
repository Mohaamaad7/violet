# 🚨 تشخيص فوري - Paymob لا ترسل Callback

## المشكلة الحالية

**لا يوجد أي log** عند الدفع = Paymob **لا ترسل callback للموقع أصلاً**

---

## ✅ خطوات التشخيص الفوري

### الخطوة 1: تحقق من Integration ID المُستخدَم

نحتاج معرفة **أي Integration ID** يستخدمه الموقع حالياً.

**نفّذ هذا الأمر:**

```bash
php artisan tinker
```

ثم:

```php
\App\Models\PaymentSetting::getPaymobConfig();
```

أرسل النتيجة (ستكون مثل):
```php
[
  "api_key" => "...",
  "integration_id_card" => "5443683",  // ← هذا هو المهم
  "integration_id_wallet" => "...",
  "integration_id_kiosk" => "...",
]
```

---

### الخطوة 2: تأكد من URLs في Integration الصحيح

بناءً على Integration ID من الخطوة السابقة:

1. افتح [Paymob Dashboard - Payment Integrations](https://accept.paymob.com/portal2/en/paymentIntegrations)

2. ابحث عن Integration **بنفس الـ ID** (مثلاً: 5443683)

3. اضغط عليه → **Edit**

4. في قسم **"Integration Callbacks"**، تأكد أن:

```
Transaction processed callback:
https://test.flowerviolet.com/payment/paymob/callback

Transaction response callback:  
https://test.flowerviolet.com/payment/paymob/callback
```

5. احفظ التغييرات

---

### الخطوة 3: اختبار مباشر للـ Route

تأكد أن الموقع يستقبل requests على callback URL:

**نفّذ هذا الأمر:**

```bash
curl -X GET "https://test.flowerviolet.com/payment/paymob/callback?test=123"
```

**النتيجة المتوقعة:**
- إذا نجح: ستظهر صفحة أو رسالة خطأ (طبيعي)
- إذا فشل: Connection refused / Timeout

---

### الخطوة 4: تفعيل Route Logging المؤقت

أضف route تجريبي لتأكيد أن الموقع يستقبل:

**نفّذ هذا:**

```bash
cat >> routes/web.php << 'EOF'

// تجريبي - حذف بعد الاختبار
Route::get('/test-paymob-callback', function() {
    \Illuminate\Support\Facades\Log::info('TEST: Paymob callback route is accessible');
    return response()->json(['status' => 'ok', 'time' => now()]);
})->name('test.paymob.callback');
EOF
```

ثم افتح في المتصفح:
```
https://test.flowerviolet.com/test-paymob-callback
```

يجب أن تظهر:
```json
{"status":"ok","time":"2025-12-28..."}
```

وفي الـ Log:
```bash
tail -5 storage/logs/laravel.log
```

يجب أن تظهر:
```
[INFO] TEST: Paymob callback route is accessible
```

---

## 🎯 النتائج المتوقعة

### ✅ السيناريو 1: Route يعمل
إذا ظهر `{"status":"ok"}` → المشكلة في **Callback URLs في Paymob Dashboard**

**الحل:**
- تأكد من تحديث URLs في Integration الصحيح
- تأكد من الحفظ

### ❌ السيناريو 2: Route لا يعمل
إذا لم يفتح الرابط → مشكلة في **Server/Routes**

**الحل:**
- تحقق من `.htaccess`
- تحقق من `php artisan route:list | grep paymob`

---

## 📤 المطلوب منك الآن

أرسل نتائج:

1. **Integration IDs من Tinker:**
```bash
php artisan tinker
>>> \App\Models\PaymentSetting::getPaymobConfig();
```

2. **اختبار Route:**
```bash
curl https://test.flowerviolet.com/test-paymob-callback
```

3. **Screenshot من Paymob Dashboard:**
- صورة من Integration Callbacks section للـ Integration المُستخدَم

---

## ⏱️ الوقت المتوقع

5 دقائق للتشخيص الكامل
