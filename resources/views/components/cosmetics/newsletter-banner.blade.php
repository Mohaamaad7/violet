{{-- Cosmetics Theme - Newsletter Banner --}}
<section id="contact" class="relative py-20 overflow-hidden">
    {{-- Background Gradient --}}
    <div class="absolute inset-0 bg-gradient-to-r from-violet-900 via-violet-800 to-violet-900"></div>
    
    {{-- Decorative Elements --}}
    <div class="absolute top-0 left-0 w-full h-full">
        <div class="absolute top-10 {{ app()->getLocale() === 'ar' ? 'right-10' : 'left-10' }} w-64 h-64 bg-gold-400/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 {{ app()->getLocale() === 'ar' ? 'left-10' : 'right-10' }} w-96 h-96 bg-violet-600/20 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        {{-- Badge --}}
        <span class="inline-block px-4 py-1 mb-6 text-gold-400 text-sm font-medium tracking-wider uppercase border border-gold-400/30 rounded-full">
            {{ __('messages.cosmetics.newsletter.badge') }}
        </span>

        {{-- Heading --}}
        <h2 class="text-3xl sm:text-4xl font-playfair font-bold text-cream-50 mb-4">
            {{ __('messages.cosmetics.newsletter.title') }}
        </h2>

        {{-- Description --}}
        <p class="text-cream-300 text-lg mb-8 max-w-2xl mx-auto">
            {{ __('messages.cosmetics.newsletter.description') }}
        </p>

        {{-- Newsletter Form --}}
        <form 
            x-data="{ email: '', loading: false, success: false, error: '' }"
            @submit.prevent="
                loading = true;
                error = '';
                // TODO: Implement newsletter subscription
                setTimeout(() => {
                    success = true;
                    loading = false;
                    email = '';
                }, 1000);
            "
            class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto"
        >
            <div class="flex-1 relative">
                <input 
                    type="email" 
                    x-model="email"
                    :disabled="loading"
                    required
                    placeholder="{{ __('messages.cosmetics.newsletter.placeholder') }}"
                    class="w-full px-6 py-4 bg-violet-950/50 border border-violet-700 rounded-full text-cream-100 placeholder-cream-500 focus:outline-none focus:border-gold-400 focus:ring-2 focus:ring-gold-400/20 transition-all duration-200"
                >
            </div>
            <button 
                type="submit"
                :disabled="loading"
                class="px-8 py-4 bg-gold-400 text-violet-950 font-semibold rounded-full hover:bg-gold-300 focus:outline-none focus:ring-2 focus:ring-gold-400 focus:ring-offset-2 focus:ring-offset-violet-900 transform hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
            >
                <span x-show="!loading">{{ __('messages.cosmetics.newsletter.button') }}</span>
                <span x-show="loading" x-cloak class="flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </form>

        {{-- Success Message --}}
        <div 
            x-data="{ show: false }"
            x-show="show"
            x-cloak
            @newsletter-success.window="show = true; setTimeout(() => show = false, 5000)"
            class="mt-4 text-green-400 text-sm"
        >
            {{ __('messages.cosmetics.newsletter.success') }}
        </div>

        {{-- Privacy Note --}}
        <p class="mt-6 text-cream-500 text-sm">
            {{ __('messages.cosmetics.newsletter.privacy') }}
        </p>
    </div>
</section>
