{{-- Single Root Element Required by Livewire v3 --}}
<div>
    @if($sliders->count() > 0)
    {{-- Hero Slider Section --}}
    <div class="relative bg-gradient-to-r from-violet-600 to-violet-800 overflow-hidden">
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                @foreach($sliders as $slider)
                <div class="swiper-slide">
                    <div class="relative h-[400px] md:h-[500px] lg:h-[600px]">
                        {{-- Background Image --}}
                        @if($slider->image_path)
                        <img 
                            src="{{ asset('storage/' . $slider->image_path) }}" 
                            alt="{{ $slider->title }}"
                            class="absolute inset-0 w-full h-full object-cover"
                        />
                        {{-- Overlay for better text readability --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-black/30"></div>
                        @endif

                        {{-- Content --}}
                        <div class="relative container mx-auto px-4 h-full flex items-center">
                            <div class="max-w-2xl text-white">
                                {{-- Title --}}
                                @if($slider->title)
                                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4 animate-fadeInUp">
                                    {{ $slider->title }}
                                </h1>
                                @endif

                                {{-- Subtitle --}}
                                @if($slider->subtitle)
                                <p class="text-xl md:text-2xl text-gray-100 mb-8 animate-fadeInUp animation-delay-200">
                                    {{ $slider->subtitle }}
                                </p>
                                @endif

                                {{-- CTA Button --}}
                                @if($slider->link_url)
                                <a 
                                    href="{{ $slider->link_url }}" 
                                    class="inline-block px-8 py-3 bg-white text-violet-700 font-semibold rounded-lg hover:bg-cream-50 transition duration-300 shadow-lg animate-fadeInUp animation-delay-400"
                                >
                                    Shop Now
                                    <svg class="inline-block w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            {{-- Navigation Buttons (only show if more than 1 slide) --}}
            @if($sliders->count() > 1)
            <div class="swiper-button-prev text-white"></div>
            <div class="swiper-button-next text-white"></div>
            
            {{-- Pagination --}}
            <div class="swiper-pagination"></div>
            @endif
        </div>
    </div>

    {{-- Initialize Swiper --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const heroSwiper = new Swiper('.hero-swiper', {
                loop: {{ $sliders->count() > 1 ? 'true' : 'false' }},
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
            });
        });
    </script>

    {{-- Custom Animations --}}
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .animation-delay-200 {
            animation-delay: 0.2s;
            opacity: 0;
        }
        
        .animation-delay-400 {
            animation-delay: 0.4s;
            opacity: 0;
        }
        
        .swiper-button-prev,
        .swiper-button-next {
            color: white !important;
        }
        
        .swiper-button-prev:after,
        .swiper-button-next:after {
            font-size: 30px !important;
        }
        
        .swiper-pagination-bullet {
            background: white !important;
            opacity: 0.5;
        }
        
        .swiper-pagination-bullet-active {
            opacity: 1 !important;
        }
    </style>
    @else
    {{-- Fallback: Show default hero if no sliders --}}
    <div class="bg-gradient-to-r from-violet-600 to-violet-800 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                Welcome to Violet Store
            </h1>
            <p class="text-xl md:text-2xl text-violet-100 mb-8">
                Your premium destination for quality products at unbeatable prices
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/products" class="px-8 py-3 bg-white text-violet-700 rounded-lg font-semibold hover:bg-cream-50 transition">
                    Shop Now
                </a>
                <a href="/offers" class="px-8 py-3 bg-violet-700 border-2 border-white rounded-lg font-semibold hover:bg-violet-600 transition">
                    View Offers
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
