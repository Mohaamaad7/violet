# 🔍 خطوات التشخيص - Paymob Callback Debug

## ✅ التحديثات المطبقة

تم تحسين الكود لدعم Paymob Unified Checkout بشكل أفضل:

1. **Enhanced Logging** في `PaymentController.php`
2. **Better Data Extraction** من payment_key_claims
3. **Multi-Step Search** للعثور على Payment

---

## 📋 خطوات الاختبار (5 دقائق)

### 1. تأكد من تحديث Callback URLs في Paymob Dashboard

يجب أن تكون:
```
https://test.flowerviolet.com/payment/paymob/callback
```

### 2. امسح Log السابق (اختياري)

```bash
> storage/logs/laravel.log
```

أو احتفظ بالـ log القديم:
```bash
mv storage/logs/laravel.log storage/logs/laravel.log.backup
```

### 3. ضع طلب جديد وادفع

1. اذهب للموقع: https://test.flowerviolet.com
2. أضف منتج للسلة
3. اذهب للـ Checkout
4. اكمل بيانات الشحن
5. اضغط "Place Order"
6. اختر طريقة دفع "بطاقة ائتمانية"
7. استخدم بطاقة تجريبية:

**بطاقة Paymob الاختبارية:**
```
Card Number: 4987654321098769
CVV: 123
Expiry: أي تاريخ مستقبلي (مثلاً 12/25)
```

8. اضغط Pay

### 4. بعد الدفع مباشرة، أرسل الـ Log

```bash
tail -100 storage/logs/laravel.log
```

---

## 🔎 ما نبحث عنه في الـ Log

يجب أن تظهر رسائل مثل:

```log
[INFO] Paymob callback - FULL DEBUG
{
  "query_params": {...},
  "all_data": {...},
  "url": "..."
}

[INFO] Paymob: Raw callback data
{
  "all_data": {...},
  "keys": [...]
}

[INFO] Paymob: Parsed callback values
{
  "success": ...,
  "transactionId": ...,
  "merchantOrderId": ...
}
```

---

## 🎯 السيناريوهات المتوقعة

### ✅ السيناريو الأفضل
```log
[INFO] Paymob: Found payment by reference
[INFO] Paymob: Payment completed
```
→ **الحل نجح!** 🎉

### ⚠️ السيناريو المتوسط
```log
[INFO] Paymob: Found payment by amount fallback
[INFO] Paymob: Payment completed
```
→ يعمل ولكن يحتاج تحسين

### ❌ السيناريو السيئ
```log
[ERROR] Paymob: Payment not found - ALL ATTEMPTS FAILED
{
  "sample_payments": [...]
}
```
→ نحتاج فحص البيانات المرسلة

---

## 📤 المطلوب منك

أرسل:
1. آخر 100 سطر من `storage/logs/laravel.log`
2. رقم الطلب (Order Number) الجديد
3. حالة الطلب في Admin Panel

سأحلل البيانات وأعطيك الحل النهائي.

---

## ⏱️ الوقت المتوقع

- الاختبار: 3 دقائق
- تحليل الـ Log: 2 دقيقة
- **المجموع: 5 دقائق**
