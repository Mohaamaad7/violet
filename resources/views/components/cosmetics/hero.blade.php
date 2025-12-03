{{-- Cosmetics Theme - Hero Section --}}
@props([
    'featuredProduct' => null,
])

<section class="relative min-h-screen flex items-center pt-16 overflow-hidden">
    {{-- Background Gradient --}}
    <div class="absolute inset-0 bg-gradient-to-br from-violet-950 via-violet-900 to-violet-950"></div>
    
    {{-- Decorative Elements --}}
    <div class="absolute top-20 {{ app()->getLocale() === 'ar' ? 'left-10' : 'right-10' }} w-72 h-72 bg-violet-700/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 {{ app()->getLocale() === 'ar' ? 'right-10' : 'left-10' }} w-96 h-96 bg-gold-400/10 rounded-full blur-3xl"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            {{-- Text Content --}}
            <div class="text-center lg:text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} order-2 lg:order-{{ app()->getLocale() === 'ar' ? '2' : '1' }}">
                <span class="inline-block px-4 py-1 mb-6 text-gold-400 text-sm font-medium tracking-wider uppercase border border-gold-400/30 rounded-full">
                    {{ __('messages.cosmetics.hero.badge') }}
                </span>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-playfair font-bold text-cream-50 leading-tight mb-6">
                    {!! __('messages.cosmetics.hero.title') !!}
                </h1>
                
                <p class="text-lg text-cream-300 mb-8 max-w-lg mx-auto lg:mx-0 {{ app()->getLocale() === 'ar' ? 'lg:mr-0' : 'lg:ml-0' }}">
                    {{ __('messages.cosmetics.hero.description') }}
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-{{ app()->getLocale() === 'ar' ? 'end' : 'start' }}">
                    <a 
                        href="{{ route('products.index') }}" 
                        class="inline-flex items-center justify-center px-8 py-3 bg-gold-400 text-violet-950 font-semibold rounded-full hover:bg-gold-300 transform hover:scale-105 transition-all duration-300 shadow-lg shadow-gold-400/25"
                    >
                        {{ __('messages.cosmetics.hero.cta_shop') }}
                        <svg class="w-5 h-5 {{ app()->getLocale() === 'ar' ? 'mr-2 rotate-180' : 'ml-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    <a 
                        href="#best-sellers" 
                        class="inline-flex items-center justify-center px-8 py-3 border-2 border-cream-100/30 text-cream-100 font-semibold rounded-full hover:border-cream-100 hover:bg-cream-100/10 transition-all duration-300"
                    >
                        {{ __('messages.cosmetics.hero.cta_explore') }}
                    </a>
                </div>
            </div>

            {{-- Featured Product Image --}}
            <div class="relative order-1 lg:order-{{ app()->getLocale() === 'ar' ? '1' : '2' }} flex justify-center">
                <div class="relative">
                    {{-- Glow Effect --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-gold-400/30 to-transparent blur-2xl scale-150"></div>
                    
                    {{-- Floating Product Image --}}
                    @if($featuredProduct && $featuredProduct->getFirstMediaUrl('images'))
                        <img 
                            src="{{ $featuredProduct->getFirstMediaUrl('images') }}" 
                            alt="{{ $featuredProduct->name }}"
                            class="relative w-80 h-80 sm:w-96 sm:h-96 object-contain animate-float drop-shadow-2xl"
                        >
                    @else
                        {{-- Placeholder/Default Image --}}
                        <div class="relative w-80 h-80 sm:w-96 sm:h-96 rounded-full bg-gradient-to-br from-violet-800 to-violet-900 flex items-center justify-center animate-float">
                            <svg class="w-32 h-32 text-gold-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Price Tag --}}
                    @if($featuredProduct)
                        <div class="absolute -bottom-4 {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} bg-gold-400 text-violet-950 px-6 py-2 rounded-full font-bold shadow-lg">
                            {{ Number::currency($featuredProduct->price, 'SAR') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll Indicator --}}
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
        <a href="#features" class="flex flex-col items-center text-cream-300 hover:text-gold-400 transition-colors duration-200">
            <span class="text-sm mb-2">{{ __('messages.cosmetics.hero.scroll') }}</span>
            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </a>
    </div>
</section>
