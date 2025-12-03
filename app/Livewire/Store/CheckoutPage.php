<?php

namespace App\Livewire\Store;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Services\CartService;
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

    public function mount()
    {
        // Load cart items using CartService
        $this->loadCart();

        // Pre-fill user data if authenticated
        if (auth()->check()) {
            $user = auth()->user();
            $this->first_name = $user->name ? explode(' ', $user->name)[0] : '';
            $this->last_name = $user->name ? (explode(' ', $user->name)[1] ?? '') : '';
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';

            // Auto-select first address if exists
            $firstAddress = $user->shippingAddresses()->first();
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
        $savedAddresses = auth()->check() 
            ? auth()->user()->shippingAddresses 
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
        ])->layout('layouts.store');
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

        if (auth()->check() && $this->selectedAddressId) {
            // Authenticated user with saved address
            $shippingAddress = ShippingAddress::where('id', $this->selectedAddressId)
                ->where('user_id', auth()->id())
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

            // If authenticated, save the address for future use
            if (auth()->check()) {
                $shippingAddress = ShippingAddress::create([
                    'user_id' => auth()->id(),
                    'full_name' => $guestAddressData['name'],
                    'email' => $guestAddressData['email'],
                    'phone' => $guestAddressData['phone'],
                    'governorate' => $guestAddressData['governorate'],
                    'city' => $guestAddressData['city'],
                    'street_address' => $guestAddressData['address'],
                    'is_default' => auth()->user()->shippingAddresses()->count() === 0,
                ]);
                $guestAddressData = null; // Use the saved address instead
            }
        }

        // Validate payment method (COD only for now)
        if ($this->paymentMethod !== 'cod') {
            $this->dispatch('show-toast', [
                'message' => __('messages.checkout.invalid_payment'),
                'type' => 'error'
            ]);
            return;
        }

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
                // Generate unique order number
                $orderNumber = 'VLT-' . strtoupper(Str::random(8)) . '-' . now()->format('ymd');
                
                // Create Order
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => auth()->id(),
                    'shipping_address_id' => $shippingAddress?->id,
                    'guest_name' => $guestAddressData['name'] ?? null,
                    'guest_email' => $guestAddressData['email'] ?? null,
                    'guest_phone' => $guestAddressData['phone'] ?? null,
                    'guest_governorate' => $guestAddressData['governorate'] ?? null,
                    'guest_city' => $guestAddressData['city'] ?? null,
                    'guest_address' => $guestAddressData['address'] ?? null,
                    'status' => 'pending',
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

                // Clear cart session cookie for guests
                if (!auth()->check()) {
                    Cookie::queue(Cookie::forget('cart_session_id'));
                }

                return $order;
            });

            // =============================================
            // STEP 4: SUCCESS - Redirect to confirmation page
            // =============================================
            
            // Dispatch cart update event for header counter
            $this->dispatch('cart-updated', count: 0);

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
