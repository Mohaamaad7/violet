# 🔧 دليل حل المشاكل - نظام المؤثرين

## الفهرس

1. [مشاكل الـ Navigation](#مشاكل-الـ-navigation)
2. [مشاكل الـ Resources](#مشاكل-الـ-resources)
3. [مشاكل العمولات](#مشاكل-العمولات)
4. [مشاكل طلبات الصرف](#مشاكل-طلبات-الصرف)
5. [مشاكل الإشعارات](#مشاكل-الإشعارات)
6. [أخطاء قاعدة البيانات](#أخطاء-قاعدة-البيانات)

---

## مشاكل الـ Navigation

### ❌ المشكلة: قائمة "المؤثرين" لا تظهر في الـ Sidebar

**الأسباب المحتملة:**

1. **Cache قديم**
2. **مشكلة في الـ ChecksResourceAccess**
3. **صلاحيات المستخدم**

**الحل:**

```bash
# 1. مسح الكاش
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 2. إعادة تحميل الـ autoload
composer dump-autoload

# 3. مسح كاش Filament
php artisan filament:clear-cache
```

**إذا استمرت المشكلة:**

تحقق من أن الـ Resource مسجل صح:
```php
// في app/Providers/Filament/AdminPanelProvider.php
->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
```

---

### ❌ المشكلة: الـ Badge لا يعرض العدد الصحيح

**السبب:** Query خاطئ أو مشكلة في الـ scope

**الحل:**

تحقق من method `getNavigationBadge()` في الـ Resource:

```php
public static function getNavigationBadge(): ?string
{
    // تأكد من استخدام الـ scope الصحيح
    return static::getModel()::where('status', 'pending')->count();
}
```

---

## مشاكل الـ Resources

### ❌ المشكلة: Class Not Found Error

**رسالة الخطأ:**
```
Class 'App\Filament\Resources\Influencers\InfluencerApplicationResource' not found
```

**الحل:**
```bash
composer dump-autoload
php artisan cache:clear
```

---

### ❌ المشكلة: Actions لا تظهر في الجدول

**الأسباب:**
1. نسيت إضافة `->actions()` في Table definition
2. الـ Action معرفة بـ `visible` condition خاطئ

**الحل:**

تحقق من `ApplicationsTable.php`:
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->actions([
            // تأكد من وجود الـ Actions هنا
            self::approveAction(),
            self::rejectAction(),
        ]);
}
```

---

### ❌ المشكلة: زر "قبول" لا يعمل / لا يظهر Modal

**الأسباب:**
1. Missing `->requiresConfirmation()` أو `->form()`
2. خطأ في JavaScript

**الحل:**

1. تحقق من تعريف الـ Action:
```php
Tables\Actions\Action::make('approve')
    ->form([
        Forms\Components\TextInput::make('commission_rate')
            ->required()
            ->numeric(),
    ])
    ->action(function ($record, array $data) {
        // ...
    });
```

2. تحقق من Console في المتصفح للأخطاء JS

---

### ❌ المشكلة: خطأ "Method approveApplication not found"

**رسالة الخطأ:**
```
Call to undefined method App\Services\InfluencerService::approveApplication()
```

**الحل:**

تأكد من أن الـ method موجودة في `InfluencerService.php`:
```php
public function approveApplication(int $applicationId, float $commissionRate, ?int $reviewedBy = null): Influencer
```

---

## مشاكل العمولات

### ❌ المشكلة: العمولة لا تُسجل عند الدفع

**التشخيص:**
```sql
SELECT * FROM influencer_commissions WHERE order_id = [ORDER_ID];
```

**الأسباب المحتملة:**

1. **الطلب ليس له discount_code_id**
   - تحقق: `SELECT discount_code_id FROM orders WHERE id = [ORDER_ID];`

2. **الكود ليس مرتبط بمؤثر**
   - تحقق: `SELECT influencer_id FROM discount_codes WHERE id = [CODE_ID];`

3. **المؤثر غير نشط**
   - تحقق: `SELECT status FROM influencers WHERE id = [INFLUENCER_ID];`

4. **الكود غير نشط**
   - تحقق: `SELECT is_active FROM discount_codes WHERE id = [CODE_ID];`

5. **العمولة مسجلة مسبقاً**
   - تحقق من الـ Log:
   ```bash
   tail -100 storage/logs/laravel.log | grep "commission"
   ```

**الحل:**

تحقق من أن `updatePaymentStatus()` يستدعي `recordInfluencerCommission()`:
```php
// في OrderService.php
if ($paymentStatus === 'paid' && $previousPaymentStatus !== 'paid') {
    $this->recordInfluencerCommission($order->fresh());
}
```

---

### ❌ المشكلة: العمولة لا تُلغى عند إلغاء الطلب

**الحل:**

تحقق من `handleCancellation()`:
```php
protected function handleCancellation(Order $order, ?string $reason): void
{
    // ...
    $this->reverseInfluencerCommission($order);
}
```

---

### ❌ المشكلة: إحصائيات المؤثر لا تتحدث

**التشخيص:**
```sql
SELECT total_sales, total_commission_earned, balance 
FROM influencers WHERE id = [ID];
```

**الحل:**

تحقق من `recordInfluencerCommission()`:
```php
$influencer->increment('total_sales', $order->total);
$influencer->increment('total_commission_earned', $commissionAmount);
$influencer->increment('balance', $commissionAmount);
```

---

## مشاكل طلبات الصرف

### ❌ المشكلة: خطأ عند حفظ طلب الصرف

**رسالة الخطأ:**
```
SQLSTATE[HY000]: General error: 1364 Field 'status' doesn't have a default value
```

**الحل:**

تأكد من أن `CreatePayout.php` يضيف الـ status:
```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    $data['status'] = 'pending';
    return $data;
}
```

---

### ❌ المشكلة: زر "تم الدفع" لا يظهر

**السبب:** الـ visible condition يتطلب `status === 'approved'`

**الحل:**

1. تحقق من حالة الطلب في DB
2. تحقق من condition في `PayoutsTable.php`:
```php
->visible(fn ($record) => $record->status === 'approved')
```

---

### ❌ المشكلة: الرصيد لا يُخصم بعد الصرف

**التشخيص:**
```sql
SELECT balance FROM influencers WHERE id = [ID];
```

**الحل:**

تحقق من `processPayout()` في InfluencerService:
```php
$influencer->decrement('balance', $payout->amount);
$influencer->increment('total_commission_paid', $payout->amount);
```

---

## مشاكل الإشعارات

### ❌ المشكلة: الإشعارات لا تُرسل

**التشخيص:**
```sql
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5;
```

**الأسباب المحتملة:**

1. **الـ User ليس Notifiable**
   - تحقق من أن Model يستخدم `use Notifiable;`

2. **لم يتم استدعاء `$user->notify()`**

3. **خطأ في الـ Notification class**

**الحل:**

أضف استدعاء الإشعار بعد العملية:
```php
// بعد قبول الطلب
$application->user->notify(new ApplicationApprovedNotification($application, $code));
```

---

### ❌ المشكلة: Email لا يُرسل

**التشخيص:**
```bash
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com'));
```

**الحل:**

تحقق من إعدادات Mail في `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

---

## أخطاء قاعدة البيانات

### ❌ المشكلة: Column Not Found

**رسالة الخطأ:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'order_total'
```

**السبب:** الـ Model يستخدم اسم عمود خاطئ

**الحل:**

1. تحقق من الـ fillable في Model:
```php
// خطأ
'order_total' => 'decimal:2',

// صحيح
'order_amount' => 'decimal:2',
```

2. قارن مع أعمدة الجدول:
```sql
DESCRIBE influencer_commissions;
```

---

### ❌ المشكلة: Foreign Key Constraint Fails

**رسالة الخطأ:**
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row
```

**الأسباب:**
- محاولة ربط بـ ID غير موجود

**الحل:**

تحقق من وجود السجل المرتبط:
```sql
SELECT * FROM influencers WHERE id = [INFLUENCER_ID];
SELECT * FROM orders WHERE id = [ORDER_ID];
```

---

## أوامر مفيدة للتشخيص

```bash
# عرض آخر 100 سطر من الـ Log
tail -100 storage/logs/laravel.log

# البحث عن أخطاء معينة
grep -i "error" storage/logs/laravel.log | tail -20

# عرض الـ SQL queries
# أضف في .env: DB_LOG=true

# تشغيل Tinker للاختبار
php artisan tinker

# في Tinker:
>>> App\Models\Influencer::count()
>>> App\Models\InfluencerCommission::where('status', 'pending')->count()
```

---

## جدول ملخص الأخطاء الشائعة

| الخطأ | السبب | الحل |
|------|------|-----|
| Navigation لا يظهر | Cache | `php artisan cache:clear` |
| Class Not Found | Autoload | `composer dump-autoload` |
| Action لا يعمل | visible condition | تحقق من الـ condition |
| العمولة لا تُسجل | المؤثر/الكود غير نشط | تحقق من status |
| Column Not Found | اسم عمود خاطئ | قارن Model مع DB |
| Email لا يُرسل | إعدادات SMTP | تحقق من .env |
