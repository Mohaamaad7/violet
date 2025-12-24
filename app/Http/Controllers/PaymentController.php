<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\KashierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected KashierService $kashierService
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
     * Process payment - redirect to Kashier
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

        // Initiate payment
        $result = $this->paymentService->initiatePayment($order, $method);

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        // Redirect to Kashier
        return redirect()->away($result['redirect_url']);
    }

    /**
     * Handle callback from Kashier
     * User is redirected here after payment
     */
    public function callback(Request $request)
    {
        Log::info('Kashier callback received', $request->all());

        $result = $this->paymentService->handleCallback($request->all());

        if (!$result['success'] && !isset($result['payment'])) {
            // No payment found - redirect to home
            return redirect()->route('home')
                ->with('error', 'حدث خطأ في عملية الدفع');
        }

        $payment = $result['payment'];
        $order = $payment->order;

        if ($result['success'] || ($result['status'] ?? '') === 'completed') {
            // Success - redirect to success page
            return redirect()->route('checkout.success', $order->id);
        }

        // Failed - redirect to failed page
        return redirect()->route('payment.failed', $order->id)
            ->with('error', $result['error'] ?? 'فشلت عملية الدفع');
    }

    /**
     * Handle webhook from Kashier (async)
     */
    public function webhook(Request $request)
    {
        Log::info('Kashier webhook received', $request->all());

        // Validate signature
        if (!$this->kashierService->validateSignature($request->all())) {
            Log::warning('Invalid webhook signature');
            return response()->json(['status' => 'invalid_signature'], 403);
        }

        // Process callback (same logic)
        $result = $this->paymentService->handleCallback($request->all());

        return response()->json([
            'status' => $result['success'] ? 'success' : 'failed',
            'already_processed' => $result['already_processed'] ?? false,
        ]);
    }

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
