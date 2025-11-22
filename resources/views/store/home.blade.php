<x-store-layout 
    title="Violet - Your Premium E-Commerce Destination"
    description="Shop quality products at unbeatable prices"
    keywords="online shopping, e-commerce, violet store"
>
    {{-- Dynamic Hero Slider --}}
    <livewire:store.hero-slider />

    {{-- Features Section --}}
    <div class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Free Shipping --}}
                <div class="text-center">
                    <div class="w-16 h-16 bg-violet-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('store.home.free_shipping') }}</h3>
                    <p class="text-gray-600">{{ __('store.home.free_shipping_desc') }}</p>
                </div>

                {{-- Secure Payment --}}
                <div class="text-center">
                    <div class="w-16 h-16 bg-violet-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('store.home.secure_payment') }}</h3>
                    <p class="text-gray-600">{{ __('store.home.secure_payment_desc') }}</p>
                </div>

                {{-- Easy Returns --}}
                <div class="text-center">
                    <div class="w-16 h-16 bg-violet-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('store.home.easy_returns') }}</h3>
                    <p class="text-gray-600">{{ __('store.home.easy_returns_desc') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Dynamic Promotional Banners --}}
    <livewire:store.banners-section position="homepage_middle" />

    {{-- Dynamic Featured Products --}}
    <livewire:store.featured-products />

    {{-- Newsletter Section --}}
    <div class="py-16 bg-gradient-to-r from-violet-600 to-violet-800 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                {{ __('store.home.newsletter_title') }}
            </h2>
            <p class="text-xl text-violet-100 mb-8 max-w-2xl mx-auto">
                {{ __('store.home.newsletter_desc') }}
            </p>
            <form action="#" method="POST" class="max-w-md mx-auto flex gap-3">
                @csrf
                <input 
                    type="email" 
                    placeholder="{{ __('store.home.enter_email') }}"
                    class="flex-1 px-4 py-3 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white"
                    required
                >
                <button 
                    type="submit"
                    class="px-6 py-3 bg-white text-violet-700 rounded-lg font-semibold hover:bg-cream-100 transition"
                >
                    {{ __('store.footer.subscribe_button') }}
                </button>
            </form>
        </div>
    </div>
</x-store-layout>
