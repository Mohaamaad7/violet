{{-- Checkout Page (Task 9.7 - Part 1) --}}
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.checkout.title') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ __('messages.checkout.subtitle') }}</p>
        </div>

        @if(count($cartItems) > 0)
            {{-- 2-Column Grid Layout (RTL: Main on right, Sidebar on left) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Main Content Column (Address & User Info) - Order 1 in RTL --}}
                <div class="lg:col-span-7 {{ app()->getLocale() === 'ar' ? 'lg:order-2' : 'lg:order-1' }}">
                    
                    {{-- Shipping Address Section --}}
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-6 h-6 me-2 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ __('messages.checkout.shipping_address') }}
                        </h2>

                        @if(session()->has('message'))
                            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                                {{ session('message') }}
                            </div>
                        @endif

                        {{-- Saved Addresses (Authenticated Users) --}}
                        @if(auth()->check() && $savedAddresses->count() > 0 && !$showAddressForm)
                            <div class="space-y-3 mb-4">
                                @foreach($savedAddresses as $address)
                                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-colors
                                        {{ $selectedAddressId === $address->id ? 'border-violet-600 bg-violet-50' : 'border-gray-200 hover:border-violet-300' }}">
                                        <input 
                                            type="radio" 
                                            name="address" 
                                            value="{{ $address->id }}"
                                            wire:model.live="selectedAddressId"
                                            wire:click="selectAddress({{ $address->id }})"
                                            class="mt-1 text-violet-600 focus:ring-violet-500"
                                        >
                                        <div class="ms-3 flex-1">
                                            <p class="font-medium text-gray-900">
                                                {{ $address->first_name }} {{ $address->last_name }}
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $address->address_details }}, {{ $address->city }}, {{ $address->governorate }}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $address->phone }}
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <button 
                                wire:click="toggleAddressForm"
                                type="button"
                                class="w-full py-3 px-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-violet-500 hover:text-violet-600 transition-colors flex items-center justify-center"
                            >
                                <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                {{ __('messages.checkout.add_new_address') }}
                            </button>
                        @endif

                        {{-- Address Form (Guest or Add New) --}}
                        @if($showAddressForm || (!auth()->check()))
                            <form wire:submit.prevent="validateAddressForm" class="space-y-4">
                                {{-- Name Row --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.checkout.first_name') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="first_name"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                                @error('first_name') border-red-500 @enderror"
                                            required
                                        >
                                        @error('first_name') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.checkout.last_name') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="last_name"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                                @error('last_name') border-red-500 @enderror"
                                            required
                                        >
                                        @error('last_name') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Contact Row --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.checkout.email') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="email" 
                                            wire:model="email"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                                @error('email') border-red-500 @enderror"
                                            required
                                        >
                                        @error('email') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.checkout.phone') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="tel" 
                                            wire:model="phone"
                                            placeholder="01XXXXXXXXX"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                                @error('phone') border-red-500 @enderror"
                                            required
                                        >
                                        @error('phone') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Location Row --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.checkout.governorate') }} <span class="text-red-500">*</span>
                                        </label>
                                        <select 
                                            wire:model="governorate"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                                @error('governorate') border-red-500 @enderror"
                                            required
                                        >
                                            <option value="">{{ __('messages.checkout.select_governorate') }}</option>
                                            @foreach($governorates as $key => $value)
                                                <option value="{{ $key }}">{{ app()->getLocale() === 'ar' ? $value : $key }}</option>
                                            @endforeach
                                        </select>
                                        @error('governorate') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('messages.checkout.city') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="city"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                                @error('city') border-red-500 @enderror"
                                            required
                                        >
                                        @error('city') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Address Details --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('messages.checkout.address_details') }} <span class="text-red-500">*</span>
                                    </label>
                                    <textarea 
                                        wire:model="address_details"
                                        rows="3"
                                        placeholder="{{ __('messages.checkout.address_placeholder') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                            @error('address_details') border-red-500 @enderror"
                                        required
                                    ></textarea>
                                    @error('address_details') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Form Actions --}}
                                <div class="flex items-center justify-between pt-4">
                                    @if(auth()->check() && $savedAddresses->count() > 0)
                                        <button 
                                            type="button"
                                            wire:click="toggleAddressForm"
                                            class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                                        >
                                            ← {{ __('messages.checkout.back_to_saved') }}
                                        </button>
                                    @endif
                                    <button 
                                        type="submit"
                                        class="px-6 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors font-medium {{ auth()->check() && $savedAddresses->count() > 0 ? 'ms-auto' : '' }}"
                                    >
                                        {{ __('messages.checkout.validate_address') }}
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>

                    {{-- Payment Method Section --}}
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-6 h-6 me-2 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            {{ __('messages.checkout.payment_method') }}
                        </h2>

                        <div class="space-y-3">
                            @foreach($paymentMethods as $method)
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors
                                    {{ $paymentMethod === $method['key'] ? 'border-violet-600 bg-violet-50' : 'border-gray-200 hover:border-violet-300' }}">
                                    <input 
                                        type="radio" 
                                        name="payment_method" 
                                        value="{{ $method['key'] }}"
                                        wire:model.live="paymentMethod"
                                        class="text-violet-600 focus:ring-violet-500"
                                    >
                                    <div class="ms-3">
                                        <p class="font-medium text-gray-900">{{ $method['icon'] }} {{ $method['name'] }}</p>
                                        <p class="text-sm text-gray-600 mt-1">{{ $method['description'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Sidebar Column (Order Summary) - Sticky - Order 2 in RTL --}}
                <div class="lg:col-span-5 {{ app()->getLocale() === 'ar' ? 'lg:order-1' : 'lg:order-2' }}">
                    <div class="bg-white rounded-lg shadow-md p-6 lg:sticky lg:top-4">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('messages.checkout.order_summary') }}</h2>

                        {{-- Cart Items List --}}
                        <div class="divide-y divide-gray-200 mb-6">
                            @foreach($cartItems as $item)
                                <div class="py-4 flex items-center">
                                    <div class="relative">
                                        @if($item['image'])
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-cover rounded-lg">
                                        @else
                                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <span class="absolute -top-2 -end-2 w-6 h-6 bg-violet-600 text-white text-xs rounded-full flex items-center justify-center">
                                            {{ $item['quantity'] }}
                                        </span>
                                    </div>
                                    <div class="ms-4 flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $item['name'] }}</h3>
                                        <p class="text-sm text-gray-500 mt-1">{{ number_format($item['price'], 2) }} {{ __('messages.currency') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">{{ number_format($item['subtotal'], 2) }} {{ __('messages.currency') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Coupon Code Section --}}
                        <div class="border-t border-gray-200 pt-4 mb-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('messages.checkout.have_coupon') }}</h3>
                            
                            @if($appliedCoupon)
                                {{-- Applied Coupon Display --}}
                                <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="font-medium text-green-800">{{ $appliedCoupon->code }}</span>
                                    </div>
                                    <button 
                                        wire:click="removeCoupon"
                                        type="button"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium"
                                    >
                                        {{ __('messages.checkout.remove_coupon') }}
                                    </button>
                                </div>
                            @else
                                {{-- Coupon Input Form --}}
                                <div class="flex gap-2">
                                    <input 
                                        type="text" 
                                        wire:model="couponCode"
                                        placeholder="{{ __('messages.checkout.enter_coupon') }}"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-transparent text-sm
                                            @if($couponError) border-red-500 @endif"
                                    >
                                    <button 
                                        wire:click="applyCoupon"
                                        type="button"
                                        class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors text-sm font-medium"
                                    >
                                        {{ __('messages.checkout.apply') }}
                                    </button>
                                </div>
                                @if($couponError)
                                    <p class="mt-2 text-sm text-red-600">{{ $couponError }}</p>
                                @endif
                            @endif
                        </div>

                        {{-- Summary Calculations --}}
                        <div class="space-y-3 border-t border-gray-200 pt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('messages.checkout.subtotal') }}</span>
                                <span class="font-medium text-gray-900">{{ number_format($subtotal, 2) }} {{ __('messages.currency') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('messages.checkout.shipping') }}</span>
                                <span class="font-medium text-gray-900">{{ number_format($shippingCost, 2) }} {{ __('messages.currency') }}</span>
                            </div>
                            @if($couponDiscount > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>{{ __('messages.checkout.discount') }}</span>
                                    <span class="font-medium">-{{ number_format($couponDiscount, 2) }} {{ __('messages.currency') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-200">
                                <span class="text-gray-900">{{ __('messages.checkout.total') }}</span>
                                <span class="text-violet-600">{{ number_format($total, 2) }} {{ __('messages.currency') }}</span>
                            </div>
                        </div>

                        {{-- Place Order Button (COD) --}}
                        <div class="mt-6">
                            <button 
                                type="button"
                                wire:click="placeOrder"
                                wire:loading.attr="disabled"
                                wire:loading.class="!bg-gray-400 !cursor-wait"
                                class="w-full py-4 bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-bold text-lg transition-colors flex items-center justify-center gap-2"
                            >
                                <span wire:loading.remove wire:target="placeOrder">
                                    {{ __('messages.checkout.place_order') }}
                                </span>
                                <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('messages.loading') }}
                                </span>
                            </button>
                            <p class="mt-2 text-xs text-center text-gray-500">
                                💳 {{ __('messages.checkout.cod_description') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty Cart Message --}}
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('messages.checkout.empty_cart') }}</h2>
                <p class="text-gray-600 mb-6">{{ __('messages.checkout.empty_cart_description') }}</p>
                <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors font-medium">
                    {{ __('messages.checkout.continue_shopping') }}
                </a>
            </div>
        @endif
    </div>
</div>
