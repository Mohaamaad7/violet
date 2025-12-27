<?php

namespace App\Livewire\Store;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\EmailService;
use App\Services\PaymentService;
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
    protected CouponService $couponService;
    protected PaymentService $paymentService;

    // Address Selection (for authenticated users)
    public $selectedAddressId = null;
    public $showAddressForm = false;

    // Address Form Fields
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';

    // Geographic Location (New System)
    public $country_id = null;
    public $governorate_id = null;
    public $city_id = null;

    // Old fields (kept for backward compatibility during transition)
    public $governorate = '';
    public $city = '';
    public $address_details = '';

    // Payment Method (Placeholder for Part 2)
    public $paymentMethod = 'cod'; // Cash on Delivery default

    // Coupon
    public $couponCode = '';
    public $appliedCoupon = null;
    public $couponDiscount = 0;
    public $couponError = '';

    // Cart Data
    public $cartItems = [];
    public $subtotal = 0;
    public $shippingCost = 0; // Placeholder
    public $total = 0;

    /**
     * Validation rules for address form
     */
    protected function rules()
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^[0-9]{10,15}$/',
            'country_id' => 'required|exists:countries,id',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'address_details' => 'required|string|max:500',
        ];
    }

    public function boot(CartService $cartService, CouponService $couponService, PaymentService $paymentService)
    {
        $this->cartService = $cartService;
        $this->couponService = $couponService;
        $this->paymentService = $paymentService;
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

        // Auto-select Egypt as default country
        if (!$this->country_id) {
            $egypt = \App\Models\Country::where('code', 'EG')->first();
            if ($egypt) {
                $this->country_id = $egypt->id;
            }
        }
    }

    /**
     * Update country - triggered when user selects a country
     */
    public function updatedCountryId($value)
    {
        // Reset governorate and city when country changes
        $this->governorate_id = null;
        $this->city_id = null;

        // Reset shipping cost
        $this->calculateShippingCost();
    }

    /**
     * Update governorate - triggered when user selects a governorate
     */
    public function updatedGovernorateId($value)
    {
        // Reset city when governorate changes
        $this->city_id = null;

        // Calculate shipping cost from governorate
        $this->calculateShippingCost();
    }

    /**
     * Update city - triggered when user selects a city
     */
    public function updatedCityId($value)
    {
        // Recalculate shipping cost (city may have custom cost)
        $this->calculateShippingCost();
    }

    /**
     * Calculate shipping cost based on selected governorate/city
     */
    protected function calculateShippingCost(): void
    {
        $this->shippingCost = 50; // Default fallback

        if ($this->city_id) {
            $city = \App\Models\City::find($this->city_id);
            if ($city) {
                // Use city's custom shipping cost if available, otherwise use governorate's
                $this->shippingCost = $city->shipping_cost ?? $city->governorate->shipping_cost ?? 50;
            }
        } elseif ($this->governorate_id) {
            $governorate = \App\Models\Governorate::find($this->governorate_id);
            if ($governorate) {
                $this->shippingCost = $governorate->shipping_cost ?? 50;
            }
        }

        // Recalculate total
        $this->recalculateTotal();
    }

    /**
     * Get list of countries
     */
    public function getCountriesProperty()
    {
        return \App\Models\Country::where('is_active', true)
            ->orderBy('name_ar')
            ->get();
    }

    /**
     * Get list of governorates for the selected country
     */
    public function getGovernoratesProperty()
    {
        if (!$this->country_id) {
            return [];
        }

        return \App\Models\Governorate::where('country_id', $this->country_id)
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get();
    }

    /**
     * Get list of cities for the selected governorate
     */
    public function getCitiesProperty()
    {
        if (!$this->governorate_id) {
            return [];
        }

        return \App\Models\City::where('governorate_id', $this->governorate_id)
            ->where('is_active', true)
            ->orderBy('name_ar')
            ->get();
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
            $this->calculateShippingCost(); // Dynamic shipping calculation
            $this->recalculateTotal();
        } else {
            $this->cartItems = [];
            $this->subtotal = 0;
            $this->shippingCost = 0;
            $this->total = 0;
        }
    }

    /**
     * Recalculate total with coupon discount
     */
    protected function recalculateTotal(): void
    {
        $this->total = $this->subtotal + $this->shippingCost - $this->couponDiscount;
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(): void
    {
        $this->couponError = '';

        if (empty($this->couponCode)) {
            return;
        }

        // Prepare cart items for validation
        $cartItemsForValidation = collect($this->cartItems)->map(function ($item) {
            return [
                'product_id' => $item['product_id'],
                'category_id' => null, // TODO: Add category_id to cart items
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ];
        })->toArray();

        // Validate coupon
        $result = $this->couponService->validateCoupon(
            $this->couponCode,
            $cartItemsForValidation,
            $this->getCustomerId()
        );

        if (!$result['valid']) {
            $this->couponError = $result['error'];
            $this->appliedCoupon = null;
            $this->couponDiscount = 0;
            $this->recalculateTotal();
            return;
        }

        // Calculate discount
        $coupon = $result['coupon'];
        $discountResult = $this->couponService->calculateDiscount(
            $coupon,
            $cartItemsForValidation,
            $this->shippingCost
        );

        $this->appliedCoupon = $coupon;
        $this->couponDiscount = $discountResult['discount'] + $discountResult['shipping_discount'];
        $this->recalculateTotal();

        // Show success message
        $this->dispatch('show-toast', [
            'message' => __('messages.coupon_success.applied'),
            'type' => 'success'
        ]);
    }

    /**
     * Remove applied coupon
     */
    public function removeCoupon(): void
    {
        $this->couponCode = '';
        $this->appliedCoupon = null;
        $this->couponDiscount = 0;
        $this->couponError = '';
        $this->recalculateTotal();

        $this->dispatch('show-toast', [
            'message' => __('messages.coupon_success.removed'),
            'type' => 'info'
        ]);
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

        return view('livewire.store.checkout-page', [
            'savedAddresses' => $savedAddresses,
            'paymentMethods' => $this->getPaymentMethods(),
        ])->layout('layouts.store');
    }

    /**
     * Get enabled payment methods from the active payment gateway
     */
    protected function getPaymentMethods(): array
    {
        $methods = [];

        // Cash on Delivery (always available if enabled)
        if (PaymentSetting::get('payment_cod_enabled', true)) {
            $methods[] = [
                'key' => 'cod',
                'name' => 'الدفع عند الاستلام',
                'description' => 'ادفع نقداً عند استلام الطلب',
                'icon' => '💵',
            ];
        }

        // Get online payment methods from the active gateway
        try {
            $gatewayMethods = $this->paymentService->getEnabledMethods();

            foreach ($gatewayMethods as $key => $method) {
                $methods[] = [
                    'key' => $key,
                    'name' => $method['name'],
                    'description' => $method['description'] ?? '',
                    'icon' => $this->getMethodIcon($key),
                ];
            }
        } catch (\Exception $e) {
            // If gateway not configured, only show COD
            \Log::warning('Could not get payment methods from gateway', ['error' => $e->getMessage()]);
        }

        return $methods;
    }

    /**
     * Get icon for payment method
     */
    protected function getMethodIcon(string $method): string
    {
        return match ($method) {
            'card' => '💳',
            'meeza' => '🏦',
            'vodafone_cash', 'wallet' => '📱',
            'orange_money' => '🍊',
            'etisalat_cash' => '📞',
            'valu' => '🛒',
            'kiosk' => '🏪',
            'instapay' => '🏛️',
            default => '💳',
        };
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

            // Get governorate and city names for backward compatibility
            $governorate = \App\Models\Governorate::find($this->governorate_id);
            $city = \App\Models\City::find($this->city_id);

            // Prepare guest address data
            $guestAddressData = [
                'name' => $this->first_name . ' ' . $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'governorate' => $governorate?->name_ar ?? '',
                'city' => $city?->name_ar ?? '',
                'address' => $this->address_details,
            ];

            // If authenticated customer, save the address for future use
            $customer = $this->getCustomer();
            if ($customer) {
                $shippingAddress = ShippingAddress::create([
                    'customer_id' => $customer->id,
                    'country_id' => $this->country_id,
                    'governorate_id' => $this->governorate_id,
                    'city_id' => $this->city_id,
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

        // Validate payment method - allow COD and any method from the active gateway
        $validMethods = ['cod', 'card', 'meeza', 'vodafone_cash', 'wallet', 'orange_money', 'etisalat_cash', 'kiosk', 'instapay', 'valu'];
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
            $order = DB::transaction(function () use ($cart, $shippingAddress, $guestAddressData, $isOnlinePayment) {
                // Generate temporary order number (will be updated after creation)
                $tempOrderNumber = 'TEMP-' . uniqid();

                // Determine payment status based on payment method
                $paymentStatus = $isOnlinePayment ? 'pending' : 'unpaid';

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
                    'status' => $isOnlinePayment ? OrderStatus::PENDING_PAYMENT : OrderStatus::PENDING,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $this->paymentMethod, // Use actual selected method
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
            // STEP 4: SEND CONFIRMATION EMAILS (COD only)
            // Online payments will send emails after successful payment callback
            // =============================================

            if (!$isOnlinePayment) {
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
            }

            // =============================================
            // STEP 5: SUCCESS - Redirect based on payment method
            // =============================================

            // Dispatch cart update event for header counter
            $this->dispatch('cart-updated', count: 0);

            // Online payment: redirect to payment processor
            if ($isOnlinePayment) {
                // Use Livewire's redirect method
                $this->redirectRoute('payment.process', [
                    'order' => $order->id,
                    'method' => $this->paymentMethod
                ]);
                return;
            }

            // COD: redirect to success page
            $this->redirectRoute('checkout.success', ['order' => $order->id]);

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
