<?php

/**
 * تحديث حالة الطلبات المدفوعة في Paymob ولكنها لا تزال "قيد الدفع" في الموقع
 * 
 * هذا الـ Script يُستخدم **مرة واحدة فقط** لتحديث الطلبات التي تم دفعها قبل إصلاح Callback URLs
 * 
 * الاستخدام:
 * 1. راجع قائمة الطلبات في Paymob Dashboard وسجل Tmx IDs للمعاملات الناجحة
 * 2. أدخل Tmx IDs في المصفوفة $paidTransactions أدناه
 * 3. نفذ: php update_paid_orders.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// ==========================================
// ✏️ ضع Tmx IDs من Paymob Dashboard هنا
// ==========================================
$paidTransactions = [
    '389201635', // من الصورة: 300 EGP - 27 Dec 2025
    '389197572', // من الصورة: 115 EGP - 27 Dec 2025
    '389191203', // من الصورة: 95 EGP - 27 Dec 2025
];

echo "🔄 بدء تحديث الطلبات المدفوعة...\n\n";

$updated = 0;
$alreadyPaid = 0;
$notFound = 0;
$errors = 0;

foreach ($paidTransactions as $tmxId) {
    echo "معالجة Tmx ID: {$tmxId}...\n";
    
    try {
        // البحث عن الدفعة بـ Tmx ID
        $payment = \App\Models\Payment::where(function ($query) use ($tmxId) {
            $query->where('gateway_order_id', $tmxId)
                  ->orWhere('transaction_id', $tmxId)
                  ->orWhereJsonContains('metadata->intention_id', $tmxId);
        })->first();
        
        if (!$payment) {
            echo "   ❌ لم يتم العثور على دفعة بهذا الـ ID\n";
            $notFound++;
            continue;
        }
        
        // التحقق من حالة الدفعة الحالية
        if ($payment->status === 'completed') {
            echo "   ℹ️  الدفعة مُحدَّثة مسبقاً (ID: {$payment->id})\n";
            $alreadyPaid++;
            continue;
        }
        
        // تحديث الدفعة
        $payment->markAsCompleted($tmxId, [
            'updated_by_script' => true,
            'script_date' => now()->toDateTimeString(),
        ]);
        
        // تحديث الطلب
        $order = $payment->order;
        $order->update([
            'payment_status' => 'paid',
            'status' => \App\Enums\OrderStatus::PENDING,
            'payment_transaction_id' => $tmxId,
            'paid_at' => $payment->created_at, // نستخدم تاريخ إنشاء الدفعة
        ]);
        
        echo "   ✅ تم تحديث الطلب #{$order->order_number}\n";
        echo "      - Payment ID: {$payment->id}\n";
        echo "      - Order ID: {$order->id}\n";
        echo "      - Amount: {$payment->amount} EGP\n";
        
        // إرسال الإيميلات
        try {
            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendOrderConfirmation($order);
            $emailService->sendAdminNewOrderNotification($order);
            echo "      - ✉️  تم إرسال الإيميلات\n";
        } catch (\Exception $e) {
            echo "      - ⚠️  فشل إرسال الإيميلات: {$e->getMessage()}\n";
        }
        
        $updated++;
        echo "\n";
        
    } catch (\Exception $e) {
        echo "   ❌ خطأ: {$e->getMessage()}\n\n";
        $errors++;
    }
}

// الملخص النهائي
echo "========================================\n";
echo "📊 ملخص التحديث:\n";
echo "========================================\n";
echo "✅ تم التحديث: {$updated}\n";
echo "ℹ️  مُحدَّث مسبقاً: {$alreadyPaid}\n";
echo "❌ لم يُعثر عليه: {$notFound}\n";
echo "⚠️  أخطاء: {$errors}\n";
echo "========================================\n";

if ($updated > 0) {
    echo "\n✨ تم تحديث {$updated} طلب بنجاح!\n";
    echo "يمكنك الآن التحقق من الطلبات في Admin Panel.\n";
} else {
    echo "\n⚠️  لم يتم تحديث أي طلب.\n";
    echo "تحقق من Tmx IDs والـ logs.\n";
}
