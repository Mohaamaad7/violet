{{-- Cosmetics Theme - Feature Strip Section --}}
<section id="features" class="bg-violet-900/50 py-16 border-y border-violet-800/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Feature 1: Cruelty Free --}}
            <div class="text-center group">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-violet-800/50 flex items-center justify-center group-hover:bg-gold-400/20 transition-colors duration-300">
                    <svg class="w-8 h-8 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="text-cream-100 font-semibold mb-2">{{ __('messages.cosmetics.features.cruelty_free.title') }}</h3>
                <p class="text-cream-400 text-sm">{{ __('messages.cosmetics.features.cruelty_free.description') }}</p>
            </div>

            {{-- Feature 2: Natural Ingredients --}}
            <div class="text-center group">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-violet-800/50 flex items-center justify-center group-hover:bg-gold-400/20 transition-colors duration-300">
                    <svg class="w-8 h-8 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-cream-100 font-semibold mb-2">{{ __('messages.cosmetics.features.natural.title') }}</h3>
                <p class="text-cream-400 text-sm">{{ __('messages.cosmetics.features.natural.description') }}</p>
            </div>

            {{-- Feature 3: Free Shipping --}}
            <div class="text-center group">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-violet-800/50 flex items-center justify-center group-hover:bg-gold-400/20 transition-colors duration-300">
                    <svg class="w-8 h-8 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <h3 class="text-cream-100 font-semibold mb-2">{{ __('messages.cosmetics.features.shipping.title') }}</h3>
                <p class="text-cream-400 text-sm">{{ __('messages.cosmetics.features.shipping.description') }}</p>
            </div>

            {{-- Feature 4: Premium Quality --}}
            <div class="text-center group">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-violet-800/50 flex items-center justify-center group-hover:bg-gold-400/20 transition-colors duration-300">
                    <svg class="w-8 h-8 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <h3 class="text-cream-100 font-semibold mb-2">{{ __('messages.cosmetics.features.quality.title') }}</h3>
                <p class="text-cream-400 text-sm">{{ __('messages.cosmetics.features.quality.description') }}</p>
            </div>
        </div>
    </div>
</section>
