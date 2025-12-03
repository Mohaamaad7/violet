{{-- Cosmetics Theme - Home Page --}}
<div>
    {{-- Hero Section --}}
    <x-cosmetics.hero :featuredProduct="$featuredProduct" />

    {{-- Feature Strip --}}
    <x-cosmetics.feature-strip />

    {{-- Best Sellers Section --}}
    <section id="best-sellers" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section Header --}}
            <div class="text-center mb-12">
                <span class="inline-block px-4 py-1 mb-4 text-gold-400 text-sm font-medium tracking-wider uppercase border border-gold-400/30 rounded-full">
                    {{ __('messages.cosmetics.best_sellers.badge') }}
                </span>
                <h2 class="text-3xl sm:text-4xl font-playfair font-bold text-cream-50 mb-4">
                    {{ __('messages.cosmetics.best_sellers.title') }}
                </h2>
                <p class="text-cream-300 text-lg max-w-2xl mx-auto">
                    {{ __('messages.cosmetics.best_sellers.description') }}
                </p>
            </div>

            {{-- Products Grid --}}
            @if($bestSellers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($bestSellers as $product)
                        <x-cosmetics.product-card :product="$product" />
                    @endforeach
                </div>

                {{-- View All Button --}}
                <div class="text-center mt-12">
                    <a 
                        href="{{ route('products.index') }}" 
                        class="inline-flex items-center px-8 py-3 border-2 border-gold-400 text-gold-400 font-semibold rounded-full hover:bg-gold-400 hover:text-violet-950 transition-all duration-300"
                    >
                        {{ __('messages.cosmetics.best_sellers.view_all') }}
                        <svg class="w-5 h-5 {{ app()->getLocale() === 'ar' ? 'mr-2 rotate-180' : 'ml-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-violet-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h3 class="text-cream-300 text-lg font-medium mb-2">{{ __('messages.cosmetics.best_sellers.empty_title') }}</h3>
                    <p class="text-cream-500">{{ __('messages.cosmetics.best_sellers.empty_description') }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Newsletter Banner --}}
    <x-cosmetics.newsletter-banner />
</div>
