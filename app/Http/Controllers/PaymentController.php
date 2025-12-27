<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Payment Controller
 * 
 * يتعامل مع عمليات الدفع وـ callbacks من بوابات الدفع المختلفة.
 */
class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentGatewayManager $gatewayManager
    ) {
    }

    /**
     * Show payment method selection page
     */
    public function selectMethod(Order $order)
    {
        // Check order belongs to current customer or is guest order
        if ($order->customer_id && auth('customer')->id() !== $order->customer_id) {
            abort(403);
        }

        // Check order is pending
        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', $order->id);
        }

        $paymentMethods = $this->paymentService->getEnabledMethods();

        // Always add COD if not already paying online
        $codEnabled = \App\Models\PaymentSetting::get('payment_cod_enabled', true);

        return view('payment.select-method', [
            'order' => $order,
            'paymentMethods' => $paymentMethods,
            'codEnabled' => $codEnabled,
        ]);
    }

    /**
     * Process payment - redirect to active gateway
     */
    public function process(Request $request, Order $order)
    {
        // Accept method from POST body or GET query
        $method = $request->input('payment_method') ?? $request->input('method');

        if (!$method) {
            return back()->with('error', 'طريقة الدفع مطلوبة');
        }

        // Check method is enabled
        if (!\App\Models\PaymentSetting::isMethodEnabled($method)) {
            return back()->with('error', 'طريقة الدفع غير متاحة');
        }

        // Initiate payment via the active gateway
        $result = $this->paymentService->initiatePayment($order, $method);

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        // Redirect to gateway checkout
        return redirect()->away($result['redirect_url']);
    }

    // ==================== Kashier Callbacks ====================

    /**
     * Handle callback from Kashier
     * User is redirected here after payment
     */
    public function kashierCallback(Request $request)
    {
        Log::info('Kashier callback received', $request->all());

        $result = $this->paymentService->handleCallback('kashier', $request->all());

        return $this->handleCallbackResult($result);
    }

    /**
     * Handle webhook from Kashier (server-to-server)
     */
    public function kashierWebhook(Request $request)
    {
        Log::info('Kashier webhook received', $request->all());

        $result = $this->paymentService->handleWebhook('kashier', $request->all());

        return response()->json([
            'status' => $result['success'] ? 'success' : 'failed',
            'already_processed' => $result['already_processed'] ?? false,
        ]);
    }

    // ==================== Paymob Callbacks ====================

    /**
     * Handle callback from Paymob
     * User is redirected here after payment
     */
    public function paymobCallback(Request $request)
    {
        Log::info('Paymob callback received', $request->all());

        $result = $this->paymentService->handleCallback('paymob', $request->all());

        return $this->handleCallbackResult($result);
    }

    /**
     * Handle webhook from Paymob (server-to-server)
     */
    public function paymobWebhook(Request $request)
    {
        Log::info('Paymob webhook received', $request->all());

        $result = $this->paymentService->handleWebhook('paymob', $request->all());

        return response()->json([
            'status' => $result['success'] ? 'success' : 'failed',
            'already_processed' => $result['already_processed'] ?? false,
        ]);
    }

    // ==================== Legacy Callback (backwards compatibility) ====================

    /**
     * Legacy callback handler - redirects to the appropriate gateway callback
     * 
     * @deprecated Use gateway-specific callbacks instead
     */
    public function callback(Request $request)
    {
        // Try to determine which gateway this is from
        // Kashier sends 'merchantOrderId' and 'signature'
        // Paymob sends 'hmac' and 'merchant_order_id'

        if ($request->has('merchantOrderId') || $request->has('signature')) {
            return $this->kashierCallback($request);
        }

        if ($request->has('hmac') || $request->has('merchant_order_id')) {
            return $this->paymobCallback($request);
        }

        // Fallback: redirect to active gateway callback
        $activeGateway = $this->gatewayManager->getActiveGatewayName();

        Log::warning('Legacy callback - could not determine gateway, using active', [
            'active_gateway' => $activeGateway,
            'data' => $request->all(),
        ]);

        return match ($activeGateway) {
            'paymob' => $this->paymobCallback($request),
            default => $this->kashierCallback($request),
        };
    }

    /**
     * Legacy webhook handler
     * 
     * @deprecated Use gateway-specific webhooks instead
     */
    public function webhook(Request $request)
    {
        if ($request->has('merchantOrderId') || $request->has('signature')) {
            return $this->kashierWebhook($request);
        }

        if ($request->has('hmac') || $request->has('obj')) {
            return $this->paymobWebhook($request);
        }

        $activeGateway = $this->gatewayManager->getActiveGatewayName();

        return match ($activeGateway) {
            'paymob' => $this->paymobWebhook($request),
            default => $this->kashierWebhook($request),
        };
    }

    // ==================== Helper Methods ====================

    /**
     * Handle callback result and redirect appropriately
     */
    protected function handleCallbackResult(array $result)
    {
        if (!$result['success'] && !isset($result['payment']) && !isset($result['order'])) {
            // No payment found - redirect to home
            return redirect()->route('home')
                ->with('error', 'حدث خطأ في عملية الدفع');
        }

        $order = $result['order'] ?? ($result['payment']->order ?? null);

        if (!$order) {
            return redirect()->route('home')
                ->with('error', 'لم يتم العثور على الطلب');
        }

        if ($result['success'] || ($result['status'] ?? '') === 'completed') {
            // Success - redirect to success page
            return redirect()->route('checkout.success', $order->id);
        }

        // Failed - redirect to failed page
        return redirect()->route('payment.failed', $order->id)
            ->with('error', $result['error'] ?? 'فشلت عملية الدفع');
    }

    // ==================== Success & Failed Pages ====================

    /**
     * Payment success page
     */
    public function success(Order $order)
    {
        // Optional: verify ownership
        if ($order->customer_id && auth('customer')->id() !== $order->customer_id) {
            // For guests, we allow viewing by order id
            if (!session('just_ordered_' . $order->id)) {
                abort(403);
            }
        }

        return view('payment.success', [
            'order' => $order->load('items.product'),
        ]);
    }

    /**
     * Payment failed page
     */
    public function failed(Order $order)
    {
        $lastPayment = $order->payments()->latest()->first();

        return view('payment.failed', [
            'order' => $order,
            'payment' => $lastPayment,
            'error' => session('error') ?? $lastPayment?->failure_reason,
        ]);
    }
}
