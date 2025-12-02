<?php

namespace App\Livewire\Store;

use App\Models\ShippingAddress;
use App\Services\CartService;
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
}
