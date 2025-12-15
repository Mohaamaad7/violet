# 📋 تقرير حالة نظام تنبيهات المرتجعات (Returns Notifications)

**التاريخ:** 15 ديسمبر 2025  
**المشروع:** Violet E-Commerce  
**الحالة:** ⚠️ **يحتاج تفعيل**

---

## 📊 ملخص تنفيذي

### ✅ **ما تم إنجازه (مكتمل 100%)**

1. **نظام الإيميلات الأساسي:**
   - ✅ EmailService كامل وجاهز
   - ✅ EmailTemplateService متكامل
   - ✅ TemplateMail (Mailable class)
   - ✅ 5 قوالب إيميل جاهزة (Order Confirmation, Status Update, Welcome, Password Reset, Admin New Order)
   - ✅ Email Templates في Admin Panel (Filament Resource)
   - ✅ Email Logs للتتبع

2. **نظام المرتجعات (Returns System):**
   - ✅ Database Schema كامل (returns, return_items tables)
   - ✅ ReturnService مع جميع الوظائف (create, approve, reject, process)
   - ✅ OrderReturn & ReturnItem Models
   - ✅ Filament Admin Panel للمرتجعات (List, View, Actions)
   - ✅ 4 مرتجعات موجودة في قاعدة البيانات
   - ✅ 5 return items موجودة

3. **Stock Management Integration:**
   - ✅ StockMovementService
   - ✅ BatchService
   - ✅ 11 stock movements مسجلة

---

## ❌ **ما لم يتم تفعيله (المطلوب)**

### 🔴 **المشكلة الرئيسية: تنبيهات المرتجعات غير مفعّلة**

على الرغم من وجود نظام الإيميلات الكامل ونظام المرتجعات الكامل، **لا يوجد تكامل بينهما**.

#### **الأدلة:**

1. **لا توجد قوالب إيميل للمرتجعات:**
   ```
   ❌ return-request-received (طلب مرتجع جديد - للعميل)
   ❌ return-approved (الموافقة على المرتجع - للعميل)
   ❌ return-rejected (رفض المرتجع - للعميل)
   ❌ return-completed (اكتمال المرتجع - للعميل)
   ❌ admin-new-return (طلب مرتجع جديد - للإدارة)
   ```

2. **ReturnService لا يستدعي EmailService:**
   - فحصت `app/Services/ReturnService.php` (389 سطر)
   - **لا يوجد** `use App\Services\EmailService`
   - **لا يوجد** استدعاء لـ `Mail::` أو `EmailService`
   - جميع الـ methods (createReturnRequest, approveReturn, rejectReturn, processReturn) **لا ترسل إيميلات**

3. **Admin Panel Actions تحتوي على Checkbox لكن لا تستخدمه:**
   ```php
   // في ViewOrderReturn.php - السطور 56-58, 93-95
   Checkbox::make('notify_customer')
       ->label('إرسال إشعار للعميل')
       ->default(true),
   ```
   - الـ checkbox موجود في الـ form
   - لكن `$data['notify_customer']` **لا يُستخدم** في الـ action
   - يتم تجاهله تماماً

---

## 🎯 **الخطة المطلوبة للتفعيل**

### **Phase 1: Email Templates (قوالب الإيميلات)**

#### **Task 1.1: إنشاء 5 قوالب HTML**

**الملفات المطلوبة:**
```
resources/views/emails/templates/
├── return-request-received.html     (للعميل - عند إنشاء طلب مرتجع)
├── return-approved.html             (للعميل - عند الموافقة)
├── return-rejected.html             (للعميل - عند الرفض)
├── return-completed.html            (للعميل - عند اكتمال الاسترداد)
└── admin-new-return.html            (للإدارة - عند طلب جديد)
```

**المتغيرات المطلوبة لكل قالب:**

**return-request-received.html:**
```php
[
    'return_number',        // RET-20251215-0001
    'order_number',         // ORD-20251215-0001
    'return_type',          // rejection / return_after_delivery
    'return_reason',        // defective / wrong_item / etc.
    'customer_notes',       // ملاحظات العميل
    'items_count',          // عدد الأصناف
    'total_amount',         // إجمالي المبلغ المتوقع
    'user_name',
    'track_url',            // رابط تتبع المرتجع
    'app_name',
    'support_email',
]
```

**return-approved.html:**
```php
[
    'return_number',
    'order_number',
    'admin_notes',          // ملاحظات المسؤول
    'approved_at',          // تاريخ الموافقة
    'next_steps',           // الخطوات القادمة
    'user_name',
    'app_name',
]
```

**return-rejected.html:**
```php
[
    'return_number',
    'order_number',
    'rejection_reason',     // سبب الرفض
    'rejected_at',
    'user_name',
    'support_email',        // للتواصل في حال الاعتراض
]
```

**return-completed.html:**
```php
[
    'return_number',
    'order_number',
    'refund_amount',        // المبلغ المسترد
    'refund_status',        // pending / completed
    'refund_method',        // طريقة الاسترداد
    'completed_at',
    'user_name',
]
```

**admin-new-return.html:**
```php
[
    'return_number',
    'order_number',
    'return_type',
    'return_reason',
    'customer_name',
    'customer_email',
    'customer_phone',
    'items_count',
    'total_amount',
    'admin_panel_url',      // رابط مباشر للمرتجع في Admin Panel
]
```

---

#### **Task 1.2: تحديث EmailTemplateSeeder**

**الملف:** `database/seeders/EmailTemplateSeeder.php`

**إضافة 5 قوالب جديدة:**

```php
// Return Request Received (Customer)
[
    'name' => 'استلام طلب المرتجع',
    'slug' => 'return-request-received',
    'type' => 'customer',
    'category' => 'return',
    'description' => 'يُرسل للعميل عند إنشاء طلب مرتجع جديد',
    'subject_ar' => 'تم استلام طلب المرتجع #{{ return_number }}',
    'subject_en' => 'Return Request #{{ return_number }} Received',
    'content_html' => $this->loadTemplate('return-request-received.html'),
    'available_variables' => [
        'return_number', 'order_number', 'return_type', 'return_reason',
        'customer_notes', 'items_count', 'total_amount', 'user_name',
        'track_url', 'app_name', 'support_email', 'current_year',
    ],
    'is_active' => true,
],

// Return Approved (Customer)
[
    'name' => 'الموافقة على المرتجع',
    'slug' => 'return-approved',
    'type' => 'customer',
    'category' => 'return',
    'description' => 'يُرسل للعميل عند الموافقة على طلب المرتجع',
    'subject_ar' => 'تمت الموافقة على طلب المرتجع #{{ return_number }}',
    'subject_en' => 'Return Request #{{ return_number }} Approved',
    'content_html' => $this->loadTemplate('return-approved.html'),
    'available_variables' => [
        'return_number', 'order_number', 'admin_notes', 'approved_at',
        'next_steps', 'user_name', 'app_name', 'support_email', 'current_year',
    ],
    'is_active' => true,
],

// Return Rejected (Customer)
[
    'name' => 'رفض المرتجع',
    'slug' => 'return-rejected',
    'type' => 'customer',
    'category' => 'return',
    'description' => 'يُرسل للعميل عند رفض طلب المرتجع',
    'subject_ar' => 'تم رفض طلب المرتجع #{{ return_number }}',
    'subject_en' => 'Return Request #{{ return_number }} Rejected',
    'content_html' => $this->loadTemplate('return-rejected.html'),
    'available_variables' => [
        'return_number', 'order_number', 'rejection_reason', 'rejected_at',
        'user_name', 'support_email', 'app_name', 'current_year',
    ],
    'is_active' => true,
],

// Return Completed (Customer)
[
    'name' => 'اكتمال المرتجع',
    'slug' => 'return-completed',
    'type' => 'customer',
    'category' => 'return',
    'description' => 'يُرسل للعميل عند اكتمال معالجة المرتجع',
    'subject_ar' => 'تم إكمال طلب المرتجع #{{ return_number }}',
    'subject_en' => 'Return #{{ return_number }} Completed',
    'content_html' => $this->loadTemplate('return-completed.html'),
    'available_variables' => [
        'return_number', 'order_number', 'refund_amount', 'refund_status',
        'refund_method', 'completed_at', 'user_name', 'app_name', 'current_year',
    ],
    'is_active' => true,
],

// Admin: New Return Notification
[
    'name' => 'إشعار مرتجع جديد (للإدارة)',
    'slug' => 'admin-new-return',
    'type' => 'admin',
    'category' => 'return',
    'description' => 'يُرسل للإدارة عند وجود طلب مرتجع جديد',
    'subject_ar' => '🔄 طلب مرتجع جديد #{{ return_number }}',
    'subject_en' => '🔄 New Return Request #{{ return_number }}',
    'content_html' => $this->loadTemplate('admin-new-return.html'),
    'available_variables' => [
        'return_number', 'order_number', 'return_type', 'return_reason',
        'customer_name', 'customer_email', 'customer_phone', 'items_count',
        'total_amount', 'admin_panel_url', 'app_name', 'current_year',
    ],
    'is_active' => true,
],
```

---

### **Phase 2: EmailService Enhancement**

#### **Task 2.1: إضافة Methods للمرتجعات**

**الملف:** `app/Services/EmailService.php`

**إضافة 5 methods جديدة:**

```php
/**
 * Send return request received email (to customer).
 */
public function sendReturnRequestReceived(
    \App\Models\OrderReturn $return,
    ?string $locale = null
): ?EmailLog {
    $order = $return->order;
    $recipientEmail = $order->user?->email ?? $order->guest_email;
    $recipientName = $order->user?->name ?? $order->guest_name;

    if (!$recipientEmail) {
        Log::warning('No email address for return request', ['return_id' => $return->id]);
        return null;
    }

    $variables = $this->getReturnVariables($return);

    return $this->send(
        templateSlug: 'return-request-received',
        recipientEmail: $recipientEmail,
        variables: $variables,
        recipientName: $recipientName,
        related: $return,
        locale: $locale ?? $order->user?->locale ?? 'ar'
    );
}

/**
 * Send return approved email (to customer).
 */
public function sendReturnApproved(
    \App\Models\OrderReturn $return,
    ?string $locale = null
): ?EmailLog {
    $order = $return->order;
    $recipientEmail = $order->user?->email ?? $order->guest_email;
    $recipientName = $order->user?->name ?? $order->guest_name;

    if (!$recipientEmail) {
        return null;
    }

    $variables = $this->getReturnVariables($return);

    return $this->send(
        templateSlug: 'return-approved',
        recipientEmail: $recipientEmail,
        variables: $variables,
        recipientName: $recipientName,
        related: $return,
        locale: $locale ?? $order->user?->locale ?? 'ar'
    );
}

/**
 * Send return rejected email (to customer).
 */
public function sendReturnRejected(
    \App\Models\OrderReturn $return,
    ?string $locale = null
): ?EmailLog {
    $order = $return->order;
    $recipientEmail = $order->user?->email ?? $order->guest_email;
    $recipientName = $order->user?->name ?? $order->guest_name;

    if (!$recipientEmail) {
        return null;
    }

    $variables = $this->getReturnVariables($return);

    return $this->send(
        templateSlug: 'return-rejected',
        recipientEmail: $recipientEmail,
        variables: $variables,
        recipientName: $recipientName,
        related: $return,
        locale: $locale ?? $order->user?->locale ?? 'ar'
    );
}

/**
 * Send return completed email (to customer).
 */
public function sendReturnCompleted(
    \App\Models\OrderReturn $return,
    ?string $locale = null
): ?EmailLog {
    $order = $return->order;
    $recipientEmail = $order->user?->email ?? $order->guest_email;
    $recipientName = $order->user?->name ?? $order->guest_name;

    if (!$recipientEmail) {
        return null;
    }

    $variables = $this->getReturnVariables($return);

    return $this->send(
        templateSlug: 'return-completed',
        recipientEmail: $recipientEmail,
        variables: $variables,
        recipientName: $recipientName,
        related: $return,
        locale: $locale ?? $order->user?->locale ?? 'ar'
    );
}

/**
 * Send admin notification for new return request.
 */
public function sendAdminNewReturnNotification(
    \App\Models\OrderReturn $return
): ?EmailLog {
    // Get admin email from config or use a default
    $adminEmail = config('mail.admin_email', config('mail.from.address'));

    if (!$adminEmail) {
        Log::warning('No admin email configured for return notifications');
        return null;
    }

    $variables = $this->getReturnVariables($return);

    return $this->send(
        templateSlug: 'admin-new-return',
        recipientEmail: $adminEmail,
        variables: $variables,
        recipientName: 'Admin',
        related: $return,
        locale: 'ar'
    );
}

/**
 * Get return variables for email templates.
 */
protected function getReturnVariables(\App\Models\OrderReturn $return): array
{
    $order = $return->order;
    
    return [
        'return_number' => $return->return_number,
        'order_number' => $order->order_number,
        'return_type' => $return->type?->label() ?? 'غير محدد',
        'return_reason' => $return->reason,
        'customer_notes' => $return->customer_notes ?? '',
        'admin_notes' => $return->admin_notes ?? '',
        'rejection_reason' => $return->admin_notes ?? '', // Same as admin_notes for rejection
        'items_count' => (string) $return->items->count(),
        'total_amount' => number_format($return->refund_amount, 2) . ' ج.م',
        'refund_amount' => number_format($return->refund_amount, 2) . ' ج.م',
        'refund_status' => $return->refund_status ?? 'pending',
        'refund_method' => 'نفس طريقة الدفع الأصلية', // TODO: Make dynamic
        'approved_at' => $return->approved_at?->format('Y/m/d h:i A') ?? '',
        'rejected_at' => $return->rejected_at?->format('Y/m/d h:i A') ?? '',
        'completed_at' => $return->completed_at?->format('Y/m/d h:i A') ?? '',
        'next_steps' => 'سيتم التواصل معك لتحديد موعد استلام المنتجات.',
        'customer_name' => $order->user?->name ?? $order->guest_name,
        'customer_email' => $order->user?->email ?? $order->guest_email,
        'customer_phone' => $order->user?->phone ?? $order->guest_phone,
        'user_name' => $order->user?->name ?? $order->guest_name,
        'track_url' => config('app.url') . '/account/returns/' . $return->id,
        'admin_panel_url' => route('filament.admin.resources.order-returns.view', $return),
        'app_name' => config('app.name'),
        'app_url' => config('app.url'),
        'support_email' => config('mail.from.address'),
        'current_year' => date('Y'),
    ];
}
```

---

### **Phase 3: ReturnService Integration**

#### **Task 3.1: تحديث ReturnService**

**الملف:** `app/Services/ReturnService.php`

**التعديلات المطلوبة:**

```php
// في بداية الملف - إضافة use statement
use App\Services\EmailService;

// في __construct - إضافة EmailService
public function __construct(
    protected StockMovementService $stockMovementService,
    protected EmailService $emailService  // ← إضافة جديدة
) {
}

// في createReturnRequest() - بعد السطر 106
public function createReturnRequest(int $orderId, array $data): OrderReturn
{
    return DB::transaction(function () use ($orderId, $data) {
        // ... الكود الموجود ...
        
        // Update order return status
        $order->update(['return_status' => 'requested']);
        
        // ← إضافة جديدة: إرسال إيميلات
        try {
            // Send email to customer
            $this->emailService->sendReturnRequestReceived($return);
            
            // Send email to admin
            $this->emailService->sendAdminNewReturnNotification($return);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
            \Log::error('Failed to send return request emails', [
                'return_id' => $return->id,
                'error' => $e->getMessage()
            ]);
        }

        return $return->fresh(['items', 'order']);
    });
}

// في approveReturn() - بعد السطر 129
public function approveReturn(int $returnId, int $adminId, ?string $adminNotes = null): OrderReturn
{
    return DB::transaction(function () use ($returnId, $adminId, $adminNotes) {
        // ... الكود الموجود ...
        
        $return->order->update(['return_status' => 'approved']);
        
        // ← إضافة جديدة: إرسال إيميل
        try {
            $this->emailService->sendReturnApproved($return);
        } catch (\Exception $e) {
            \Log::error('Failed to send return approved email', [
                'return_id' => $return->id,
                'error' => $e->getMessage()
            ]);
        }

        return $return->fresh();
    });
}

// في rejectReturn() - بعد السطر 154
public function rejectReturn(int $returnId, int $adminId, string $reason): OrderReturn
{
    return DB::transaction(function () use ($returnId, $adminId, $reason) {
        // ... الكود الموجود ...
        
        $return->order->update(['return_status' => 'none']);
        
        // ← إضافة جديدة: إرسال إيميل
        try {
            $this->emailService->sendReturnRejected($return);
        } catch (\Exception $e) {
            \Log::error('Failed to send return rejected email', [
                'return_id' => $return->id,
                'error' => $e->getMessage()
            ]);
        }

        return $return->fresh();
    });
}

// في processReturn() - بعد السطر 200
public function processReturn(int $returnId, array $itemConditions, int $adminId): OrderReturn
{
    return DB::transaction(function () use ($returnId, $itemConditions, $adminId) {
        // ... الكود الموجود ...
        
        $return->order->update(['return_status' => 'completed']);
        
        // ← إضافة جديدة: إرسال إيميل
        try {
            $this->emailService->sendReturnCompleted($return);
        } catch (\Exception $e) {
            \Log::error('Failed to send return completed email', [
                'return_id' => $return->id,
                'error' => $e->getMessage()
            ]);
        }

        return $return->fresh();
    });
}
```

---

### **Phase 4: Admin Panel Integration (Optional)**

#### **Task 4.1: استخدام notify_customer Checkbox**

**الملف:** `app/Filament/Resources/OrderReturns/Pages/ViewOrderReturn.php`

**التعديلات المطلوبة:**

```php
// في approve action - السطر 60-65
->action(function (array $data) {
    app(ReturnService::class)->approveReturn(
        $this->record->id,
        auth()->id(),
        $data['admin_notes'] ?? null
    );
    
    // ← إضافة جديدة: إرسال إيميل يدوي إذا لم يتم إرساله تلقائياً
    if ($data['notify_customer'] ?? true) {
        try {
            app(\App\Services\EmailService::class)->sendReturnApproved($this->record->fresh());
        } catch (\Exception $e) {
            \Log::error('Manual email send failed', ['error' => $e->getMessage()]);
        }
    }

    Notification::make()
        ->success()
        ->title('تمت الموافقة')
        ->body('تمت الموافقة على طلب المرتجع. يمكنك الآن معالجته.')
        ->send();

    $this->redirect(ViewOrderReturn::getUrl(['record' => $this->record]));
})

// نفس الشيء في reject action - السطر 97-102
```

**ملاحظة:** هذا اختياري لأن الإيميلات ستُرسل تلقائياً من ReturnService.

---

## 📝 **ملخص الملفات المطلوب إنشاؤها/تعديلها**

### **ملفات جديدة (5):**
```
✅ resources/views/emails/templates/return-request-received.html
✅ resources/views/emails/templates/return-approved.html
✅ resources/views/emails/templates/return-rejected.html
✅ resources/views/emails/templates/return-completed.html
✅ resources/views/emails/templates/admin-new-return.html
```

### **ملفات للتعديل (3):**
```
📝 database/seeders/EmailTemplateSeeder.php          (إضافة 5 قوالب)
📝 app/Services/EmailService.php                     (إضافة 6 methods)
📝 app/Services/ReturnService.php                    (إضافة EmailService integration)
```

### **ملفات اختيارية (1):**
```
📝 app/Filament/Resources/OrderReturns/Pages/ViewOrderReturn.php  (استخدام notify_customer)
```

---

## 🧪 **خطة الاختبار**

### **Test Case 1: طلب مرتجع جديد**
```
1. إنشاء طلب مرتجع من Frontend
2. التحقق من:
   ✓ إرسال إيميل للعميل (return-request-received)
   ✓ إرسال إيميل للإدارة (admin-new-return)
   ✓ تسجيل في email_logs table
```

### **Test Case 2: الموافقة على المرتجع**
```
1. الموافقة على مرتجع من Admin Panel
2. التحقق من:
   ✓ إرسال إيميل للعميل (return-approved)
   ✓ تسجيل في email_logs
```

### **Test Case 3: رفض المرتجع**
```
1. رفض مرتجع من Admin Panel
2. التحقق من:
   ✓ إرسال إيميل للعميل (return-rejected)
   ✓ عرض سبب الرفض في الإيميل
```

### **Test Case 4: معالجة المرتجع**
```
1. معالجة مرتجع موافق عليه
2. التحقق من:
   ✓ إرسال إيميل للعميل (return-completed)
   ✓ عرض مبلغ الاسترداد
```

---

## 📊 **الإحصائيات الحالية**

### **قاعدة البيانات:**
- ✅ 4 مرتجعات موجودة
- ✅ 5 return items
- ✅ 0 email logs للمرتجعات (لأن النظام غير مفعّل)

### **Email Templates:**
- ✅ 5 قوالب موجودة (Orders, Auth, Admin)
- ❌ 0 قوالب للمرتجعات

---

## ⏱️ **التقدير الزمني**

| المرحلة | المهمة | الوقت المقدر |
|---------|--------|---------------|
| Phase 1 | إنشاء 5 قوالب HTML | 2-3 ساعات |
| Phase 1 | تحديث EmailTemplateSeeder | 30 دقيقة |
| Phase 2 | إضافة Methods لـ EmailService | 1 ساعة |
| Phase 3 | تحديث ReturnService | 1 ساعة |
| Phase 4 | Admin Panel Integration (اختياري) | 30 دقيقة |
| Testing | اختبار شامل | 1-2 ساعات |
| **الإجمالي** | | **6-8 ساعات** |

---

## 🎯 **الخلاصة**

### **الوضع الحالي:**
- ✅ نظام الإيميلات: **100% جاهز**
- ✅ نظام المرتجعات: **100% جاهز**
- ❌ التكامل بينهما: **0% مفعّل**

### **المطلوب:**
1. إنشاء 5 قوالب HTML للمرتجعات
2. إضافة 6 methods لـ EmailService
3. ربط ReturnService مع EmailService
4. اختبار شامل

### **النتيجة المتوقعة:**
- ✅ إرسال تلقائي لإيميلات المرتجعات في جميع المراحل
- ✅ تتبع كامل في email_logs
- ✅ تجربة مستخدم احترافية

---

**تاريخ التقرير:** 15 ديسمبر 2025 - 22:15  
**المُعد:** Antigravity AI Assistant  
**الحالة:** جاهز للتنفيذ ✅
