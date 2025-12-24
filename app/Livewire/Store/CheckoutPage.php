<?php

namespace App\Livewire\Store;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Services\CartService;
use App\Services\EmailService;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Checkout - Violet Store')]
class CheckoutPage extends Component
{
    protected CartService $cartService;
    // Address Selection (for authenticated users)
    public $selectedAddressId = null;
    public $showAddressForm = false;

    // Address Form Fields
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $governorate = '';
    public $city = '';
    public $address_details = '';

    // Payment Method (Placeholder for Part 2)
    public $paymentMethod = 'cod'; // Cash on Delivery default

    // Cart Data
    public $cartItems = [];
    public $subtotal = 0;
    public $shippingCost = 0; // Placeholder
    public $total = 0;

    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get the currently authenticated customer
     */
    private function getCustomer(): ?Customer
    {
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->user();
        }
        return null;
    }

    /**
     * Get the currently authenticated customer ID
     */
    private function getCustomerId(): ?int
    {
        return $this->getCustomer()?->id;
    }

    public function mount()
    {
        // Load cart items using CartService
        $this->loadCart();

        // Pre-fill customer data if authenticated
        $customer = $this->getCustomer();
        if ($customer) {
            $this->first_name = $customer->name ? explode(' ', $customer->name)[0] : '';
            $this->last_name = $customer->name ? (explode(' ', $customer->name)[1] ?? '') : '';
            $this->email = $customer->email;
            $this->phone = $customer->phone ?? '';

            // Auto-select first address if exists
            $firstAddress = ShippingAddress::where('customer_id', $customer->id)->first();
            if ($firstAddress) {
                $this->selectedAddressId = $firstAddress->id;
            } else {
                $this->showAddressForm = true;
            }
        } else {
            // Guest user - show form directly
            $this->showAddressForm = true;
        }
    }

    protected function loadCart()
    {
        // Use CartService to get cart (handles both Guest Cookie and Auth User)
        $cart = $this->cartService->getCart();

        if ($cart && $cart->items->count() > 0) {
            $this->cartItems = $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name ?? 'Unknown Product',
                    'price' => $item->product->sale_price ?? $item->product->price ?? 0,
                    'quantity' => $item->quantity,
                    'image' => $item->product ? $item->product->getFirstMediaUrl('products') : '',
                    'subtotal' => $item->quantity * ($item->product->sale_price ?? $item->product->price ?? 0),
                ];
            })->toArray();

            $this->subtotal = collect($this->cartItems)->sum('subtotal');
            $this->shippingCost = 50; // Placeholder - will be dynamic in Part 2
            $this->total = $this->subtotal + $this->shippingCost;
        } else {
            $this->cartItems = [];
            $this->subtotal = 0;
            $this->shippingCost = 0;
            $this->total = 0;
        }
    }

    public function selectAddress($addressId)
    {
        $this->selectedAddressId = $addressId;
        $this->showAddressForm = false;
    }

    public function toggleAddressForm()
    {
        $this->showAddressForm = !$this->showAddressForm;
        if ($this->showAddressForm) {
            $this->selectedAddressId = null;
        }
    }

    protected function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^[0-9]{10,15}$/',
            'governorate' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address_details' => 'required|string|max:500',
        ];
    }

    public function validateAddressForm()
    {
        $this->validate();

        // Store address state (will be used in Part 2 for order creation)
        session()->flash('message', 'Address validated successfully!');
    }

    public function render()
    {
        $customer = $this->getCustomer();
        $savedAddresses = $customer
            ? ShippingAddress::where('customer_id', $customer->id)->get()
            : collect();

        $egyptGovernorates = [
            'Cairo' => 'القاهرة',
            'Giza' => 'الجيزة',
            'Alexandria' => 'الإسكندرية',
            'Dakahlia' => 'الدقهلية',
            'Red Sea' => 'البحر الأحمر',
            'Beheira' => 'البحيرة',
            'Fayoum' => 'الفيوم',
            'Gharbia' => 'الغربية',
            'Ismailia' => 'الإسماعيلية',
            'Menofia' => 'المنوفية',
            'Minya' => 'المنيا',
            'Qaliubiya' => 'القليوبية',
            'New Valley' => 'الوادي الجديد',
            'Suez' => 'السويس',
            'Aswan' => 'أسوان',
            'Assiut' => 'أسيوط',
            'Beni Suef' => 'بني سويف',
            'Port Said' => 'بورسعيد',
            'Damietta' => 'دمياط',
            'Sharkia' => 'الشرقية',
            'South Sinai' => 'جنوب سيناء',
            'Kafr Al Sheikh' => 'كفر الشيخ',
            'Matrouh' => 'مطروح',
            'Luxor' => 'الأقصر',
            'Qena' => 'قنا',
            'North Sinai' => 'شمال سيناء',
            'Sohag' => 'سوهاج',
        ];

        return view('livewire.store.checkout-page', [
            'savedAddresses' => $savedAddresses,
            'governorates' => $egyptGovernorates,
            'paymentMethods' => $this->getPaymentMethods(),
        ])->layout('layouts.store');
    }

    /**
     * Get enabled payment methods from settings
     */
    protected function getPaymentMethods(): array
    {
        $methods = [];

        // Cash on Delivery
        if (PaymentSetting::get('payment_cod_enabled', true)) {
            $methods[] = [
                'key' => 'cod',
                'name' => 'الدفع عند الاستلام',
                'description' => 'ادفع نقداً عند استلام الطلب',
                'icon' => '💵',
            ];
        }

        // Card Payment
        if (PaymentSetting::get('payment_card_enabled', false)) {
            $methods[] = [
                'key' => 'card',
                'name' => 'البطاقة البنكية',
                'description' => 'Visa, Mastercard, Meeza',
                'icon' => '💳',
            ];
        }

        // Vodafone Cash
        if (PaymentSetting::get('payment_vodafone_cash_enabled', false)) {
            $methods[] = [
                'key' => 'vodafone_cash',
                'name' => 'فودافون كاش',
                'description' => 'ادفع عبر محفظة فودافون',
                'icon' => '📱',
            ];
        }

        // Meeza
        if (PaymentSetting::get('payment_meeza_enabled', false)) {
            $methods[] = [
                'key' => 'meeza',
                'name' => 'ميزة',
                'description' => 'ادفع ببطاقة ميزة',
                'icon' => '🏦',
            ];
        }

        return $methods;
    }

    /**
     * Place the order with COD payment method
     * 
     * Flow:
     * 1. Validate address selection/creation
     * 2. Verify stock availability (race condition protection)
     * 3. DB::transaction for atomic operation
     * 4. Create Order -> Create OrderItems -> Decrement Stock -> Clear Cart
     * 5. Redirect to success page
     */
    public function placeOrder()
    {
        // =============================================
        // STEP 1: VALIDATION GUARDS
        // =============================================

        // Ensure cart has items
        if (empty($this->cartItems)) {
            $this->dispatch('show-toast', [
                'message' => __('messages.checkout.empty_cart'),
                'type' => 'error'
            ]);
            return;
        }

        // Validate address - either saved address selected OR form filled
        $shippingAddress = null;
        $guestAddressData = null;

        if ($this->getCustomer() && $this->selectedAddressId) {
            // Authenticated customer with saved address
            $shippingAddress = ShippingAddress::where('id', $this->selectedAddressId)
                ->where('customer_id', $this->getCustomerId())
                ->first();

            if (!$shippingAddress) {
                $this->dispatch('show-toast', [
                    'message' => __('messages.checkout.invalid_address'),
                    'type' => 'error'
                ]);
                return;
            }
        } else {
            // Guest OR authenticated user creating new address
            $this->validate();

            // Prepare guest address data
            $guestAddressData = [
                'name' => $this->first_name . ' ' . $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'governorate' => $this->governorate,
                'city' => $this->city,
                'address' => $this->address_details,
            ];

            // If authenticated customer, save the address for future use
            $customer = $this->getCustomer();
            if ($customer) {
                $shippingAddress = ShippingAddress::create([
                    'customer_id' => $customer->id,
                    'full_name' => $guestAddressData['name'],
                    'email' => $guestAddressData['email'],
                    'phone' => $guestAddressData['phone'],
                    'governorate' => $guestAddressData['governorate'],
                    'city' => $guestAddressData['city'],
                    'street_address' => $guestAddressData['address'],
                    'is_default' => ShippingAddress::where('customer_id', $customer->id)->count() === 0,
                ]);
                $guestAddressData = null; // Use the saved address instead
            }
        }

        // Validate payment method
        $validMethods = ['cod', 'card', 'vodafone_cash', 'meeza', 'orange_money', 'etisalat_cash'];
        if (!in_array($this->paymentMethod, $validMethods)) {
            $this->dispatch('show-toast', [
                'message' => __('messages.checkout.invalid_payment'),
                'type' => 'error'
            ]);
            return;
        }

        // For online payments, create order first then redirect to payment gateway
        $isOnlinePayment = $this->paymentMethod !== 'cod';

        // =============================================
        // STEP 2: STOCK VERIFICATION (Race Condition Protection)
        // =============================================

        $stockErrors = [];
        $cart = $this->cartService->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            $this->dispatch('show-toast', [
                'message' => __('messages.checkout.cart_expired'),
                'type' => 'error'
            ]);
            return;
        }

        // Fresh load products with current stock
        foreach ($cart->items as $cartItem) {
            $product = Product::find($cartItem->product_id);

            if (!$product) {
                $stockErrors[] = __('messages.checkout.product_unavailable', [
                    'name' => $cartItem->product_name ?? 'Unknown'
                ]);
                continue;
            }

            // Check if variant or main product
            if ($cartItem->product_variant_id) {
                $variant = $product->variants()->find($cartItem->product_variant_id);
                if (!$variant || $variant->stock < $cartItem->quantity) {
                    $stockErrors[] = __('messages.checkout.insufficient_stock', [
                        'name' => $product->name,
                        'available' => $variant->stock ?? 0
                    ]);
                }
            } else {
                if ($product->stock < $cartItem->quantity) {
                    $stockErrors[] = __('messages.checkout.insufficient_stock', [
                        'name' => $product->name,
                        'available' => $product->stock
                    ]);
                }
            }
        }

        if (!empty($stockErrors)) {
            $this->dispatch('show-toast', [
                'message' => implode("\n", $stockErrors),
                'type' => 'error'
            ]);
            return;
        }

        // =============================================
        // STEP 3: ATOMIC TRANSACTION
        // =============================================

        try {
            $order = DB::transaction(function () use ($cart, $shippingAddress, $guestAddressData) {
                // Generate temporary order number (will be updated after creation)
                $tempOrderNumber = 'TEMP-' . uniqid();

                // Create Order
                $order = Order::create([
                    'order_number' => $tempOrderNumber,
                    'customer_id' => $this->getCustomerId(),
                    'shipping_address_id' => $shippingAddress?->id,
                    'guest_name' => $guestAddressData['name'] ?? null,
                    'guest_email' => $guestAddressData['email'] ?? null,
                    'guest_phone' => $guestAddressData['phone'] ?? null,
                    'guest_governorate' => $guestAddressData['governorate'] ?? null,
                    'guest_city' => $guestAddressData['city'] ?? null,
                    'guest_address' => $guestAddressData['address'] ?? null,
                    'status' => OrderStatus::PENDING,
                    'payment_status' => 'unpaid', // COD = unpaid until delivery
                    'payment_method' => 'cod',
                    'subtotal' => $this->subtotal,
                    'shipping_cost' => $this->shippingCost,
                    'discount_amount' => 0, // TODO: Implement discount codes
                    'tax_amount' => 0, // TODO: Implement tax calculation
                    'total' => $this->total,
                ]);

                // Create Order Items & Decrement Stock
                foreach ($cart->items as $cartItem) {
                    $product = Product::find($cartItem->product_id);
                    $variant = $cartItem->product_variant_id
                        ? $product->variants()->find($cartItem->product_variant_id)
                        : null;

                    // Get price (variant or product)
                    $price = $variant
                        ? ($variant->sale_price ?? $variant->price)
                        : ($product->sale_price ?? $product->price);

                    // Create order item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'product_name' => $product->name,
                        'product_sku' => $variant?->sku ?? $product->sku ?? '',
                        'variant_name' => $variant?->name,
                        'price' => $price,
                        'quantity' => $cartItem->quantity,
                        'subtotal' => $price * $cartItem->quantity,
                    ]);

                    // Decrement stock
                    if ($variant) {
                        $variant->decrement('stock', $cartItem->quantity);
                    } else {
                        $product->decrement('stock', $cartItem->quantity);
                    }

                    // Increment sales count
                    $product->increment('sales_count', $cartItem->quantity);
                }

                // Clear cart
                $cart->items()->delete();
                $cart->delete();

                // Update order number with actual ID
                $date = date('Ymd');  // 20251216
                $time = date('His');  // 142500
                $orderId = str_pad($order->id, 6, '0', STR_PAD_LEFT);  // 000123
                $finalOrderNumber = "VLT-{$date}-{$time}-{$orderId}";

                $order->update(['order_number' => $finalOrderNumber]);

                // Clear cart session cookie for guests
                if (!$this->getCustomer()) {
                    Cookie::queue(Cookie::forget('cart_session_id'));
                }

                return $order;
            });

            // =============================================
            // STEP 4: SEND CONFIRMATION EMAILS
            // =============================================

            try {
                $emailService = app(EmailService::class);

                // Send order confirmation to customer
                $emailService->sendOrderConfirmation($order);

                // Send notification to admin
                $emailService->sendAdminNewOrderNotification($order);

            } catch (\Exception $e) {
                // Log email error but don't fail the order
                report($e);
            }

            // =============================================
            // STEP 5: SUCCESS - Redirect based on payment method
            // =============================================

            // Dispatch cart update event for header counter
            $this->dispatch('cart-updated', count: 0);

            // Online payment: redirect to payment processor
            if ($isOnlinePayment) {
                return redirect()->route('payment.process', [
                    'order' => $order->id,
                    'method' => $this->paymentMethod
                ]);
            }

            // COD: redirect to success page
            return redirect()->route('checkout.success', ['order' => $order->id]);

        } catch (\Exception $e) {
            // Transaction automatically rolled back
            report($e); // Log the error

            $this->dispatch('show-toast', [
                'message' => __('messages.checkout.order_failed'),
                'type' => 'error'
            ]);
        }
    }
}
