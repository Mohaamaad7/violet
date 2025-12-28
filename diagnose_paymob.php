<?php

/**
 * Paymob Integration Diagnostics
 * 
 * هذا السكريبت يساعد في تشخيص مشاكل Paymob Callback
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 تشخيص Paymob Integration\n";
echo str_repeat('=', 60) . "\n\n";

// 1. Check Paymob Configuration
echo "1️⃣ إعدادات Paymob:\n";
echo str_repeat('-', 60) . "\n";

$config = \App\Models\PaymentSetting::getPaymobConfig();

echo "✅ Active Gateway: " . \App\Models\PaymentSetting::get('active_gateway', 'N/A') . "\n";
echo "✅ API Key: " . (empty($config['api_key']) ? '❌ NOT SET' : '✓ SET (' . substr($config['api_key'], 0, 10) . '...)') . "\n";
echo "✅ Secret Key: " . (empty($config['secret_key']) ? '❌ NOT SET' : '✓ SET (' . substr($config['secret_key'], 0, 10) . '...)') . "\n";
echo "✅ Public Key: " . (empty($config['public_key']) ? '❌ NOT SET' : '✓ SET (' . substr($config['public_key'], 0, 10) . '...)') . "\n";
echo "✅ HMAC Secret: " . (empty($config['hmac_secret']) ? '❌ NOT SET' : '✓ SET (' . substr($config['hmac_secret'], 0, 10) . '...)') . "\n\n";

echo "Integration IDs:\n";
echo "  • Card: " . ($config['integration_id_card'] ?? '❌ NOT SET') . "\n";
echo "  • Wallet: " . ($config['integration_id_wallet'] ?? '❌ NOT SET') . "\n";
echo "  • Kiosk: " . ($config['integration_id_kiosk'] ?? '❌ NOT SET') . "\n\n";

// 2. Check Routes
echo "2️⃣ Callback URLs:\n";
echo str_repeat('-', 60) . "\n";

$gateway = app(\App\Services\Gateways\PaymobGateway::class);
echo "✅ Callback URL: " . $gateway->getCallbackUrl() . "\n";
echo "✅ Webhook URL: " . $gateway->getWebhookUrl() . "\n\n";

// 3. Test Connection
echo "3️⃣ اختبار الاتصال بـ Paymob:\n";
echo str_repeat('-', 60) . "\n";

$result = $gateway->testConnection();

if ($result['success']) {
    echo "✅ الاتصال ناجح!\n";
    echo "   Message: " . $result['message'] . "\n";
    
    if (isset($result['has_card_integration'])) {
        echo "   Card Integration: " . ($result['has_card_integration'] ? '✓' : '✗') . "\n";
    }
    if (isset($result['has_wallet_integration'])) {
        echo "   Wallet Integration: " . ($result['has_wallet_integration'] ? '✓' : '✗') . "\n";
    }
    if (isset($result['has_kiosk_integration'])) {
        echo "   Kiosk Integration: " . ($result['has_kiosk_integration'] ? '✓' : '✗') . "\n";
    }
} else {
    echo "❌ فشل الاتصال!\n";
    echo "   Error: " . $result['message'] . "\n";
}

echo "\n";

// 4. Check Recent Payments
echo "4️⃣ آخر 5 دفعات Paymob:\n";
echo str_repeat('-', 60) . "\n";

$recentPayments = \App\Models\Payment::where('gateway', 'paymob')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'reference', 'status', 'amount', 'gateway_order_id', 'created_at']);

if ($recentPayments->isEmpty()) {
    echo "⚠️  لا توجد دفعات سابقة\n";
} else {
    foreach ($recentPayments as $payment) {
        echo sprintf(
            "  #%d: %s - %s EGP - %s - Created: %s\n",
            $payment->id,
            $payment->reference,
            $payment->amount,
            $payment->status,
            $payment->created_at->format('Y-m-d H:i:s')
        );
        if ($payment->gateway_order_id) {
            echo "       Gateway Order ID: {$payment->gateway_order_id}\n";
        }
    }
}

echo "\n";

// 5. Check Supported Methods
echo "5️⃣ طرق الدفع المدعومة:\n";
echo str_repeat('-', 60) . "\n";

$methods = $gateway->getSupportedMethods();

if (empty($methods)) {
    echo "❌ لا توجد طرق دفع مُعدّة\n";
} else {
    foreach ($methods as $key => $method) {
        echo "  ✓ {$method['name']} ({$method['name_en']})\n";
        echo "     Description: {$method['description']}\n";
    }
}

echo "\n";

// 6. Instructions
echo "6️⃣ الخطوات التالية:\n";
echo str_repeat('-', 60) . "\n";

if (!$result['success']) {
    echo "❌ إصلاح إعدادات Paymob في Admin Panel أولاً\n";
} else {
    echo "✅ الإعدادات صحيحة\n";
    echo "\n";
    echo "الآن تأكد من Callback URLs في Paymob Dashboard:\n";
    echo "  1. افتح: https://accept.paymob.com/portal2/en/paymentIntegrations\n";
    echo "  2. ابحث عن Integration ID: " . ($config['integration_id_card'] ?? 'N/A') . "\n";
    echo "  3. تأكد من Callback URLs:\n";
    echo "     • Transaction processed: " . $gateway->getCallbackUrl() . "\n";
    echo "     • Transaction response: " . $gateway->getCallbackUrl() . "\n";
    echo "  4. احفظ التغييرات\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ انتهى التشخيص\n";
