# تعليمات تحديث ReturnService لإضافة Email Notifications

## الملف: `app/Services/ReturnService.php`

### ✅ تم بالفعل: إضافة EmailService في Constructor (السطر 18)
```php
public function __construct(
    protected StockMovementService $stockMovementService,
    protected EmailService $emailService  // ← تم إضافته
) {
}
```

---

## المطلوب: إضافة استدعاءات الإيميلات في 4 Methods

### 1. في `createReturnRequest()` - بعد السطر 105

**ابحث عن:**
```php
            $order->update(['return_status' => 'requested']);

            return $return->fresh(['items', 'order']);
```

**استبدله بـ:**
```php
            $order->update(['return_status' => 'requested']);

            // Send email notifications
            try {
                // Send email to customer
                $this->emailService->sendReturnRequestReceived($return->fresh(['items', 'order']));
                
                // Send email to admin
                $this->emailService->sendAdminNewReturnNotification($return->fresh(['items', 'order']));
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                \Log::error('Failed to send return request emails', [
                    'return_id' => $return->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $return->fresh(['items', 'order']);
```

---

### 2. في `approveReturn()` - ابحث عن `$return->order->update`

**ابحث عن:**
```php
            $return->order->update(['return_status' => 'approved']);

            return $return->fresh();
```

**استبدله بـ:**
```php
            $return->order->update(['return_status' => 'approved']);

            // Send email notification
            try {
                $this->emailService->sendReturnApproved($return->fresh());
            } catch (\Exception $e) {
                \Log::error('Failed to send return approved email', [
                    'return_id' => $return->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $return->fresh();
```

---

### 3. في `rejectReturn()` - ابحث عن `$return->order->update`

**ابحث عن:**
```php
            $return->order->update(['return_status' => 'none']);

            return $return->fresh();
```

**استبدله بـ:**
```php
            $return->order->update(['return_status' => 'none']);

            // Send email notification
            try {
                $this->emailService->sendReturnRejected($return->fresh());
            } catch (\Exception $e) {
                \Log::error('Failed to send return rejected email', [
                    'return_id' => $return->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $return->fresh();
```

---

### 4. في `processReturn()` - ابحث عن `$return->order->update`

**ابحث عن:**
```php
            $return->order->update(['return_status' => 'completed']);

            return $return->fresh();
```

**استبدله بـ:**
```php
            $return->order->update(['return_status' => 'completed']);

            // Send email notification
            try {
                $this->emailService->sendReturnCompleted($return->fresh());
            } catch (\Exception $e) {
                \Log::error('Failed to send return completed email', [
                    'return_id' => $return->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $return->fresh();
```

---

## ملخص التعديلات:
- ✅ Constructor: تم إضافة `EmailService`
- ⏳ `createReturnRequest()`: إضافة 2 إيميلات (customer + admin)
- ⏳ `approveReturn()`: إضافة 1 إيميل (customer)
- ⏳ `rejectReturn()`: إضافة 1 إيميل (customer)
- ⏳ `processReturn()`: إضافة 1 إيميل (customer)

**إجمالي الإيميلات:** 5 إيميلات في 4 methods
