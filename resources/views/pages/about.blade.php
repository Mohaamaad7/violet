<x-store-layout>
    <x-slot name="title">{{ __('about.page_title') }}</x-slot>
    <x-slot name="description">{{ __('about.meta_description') }}</x-slot>

    {{-- Hero Section --}}
    <div class="relative bg-gradient-to-br from-violet-600 via-violet-700 to-purple-800 text-white overflow-hidden">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0"
                style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Ccircle cx=\'30\' cy=\'30\' r=\'2\'/%3E%3Ccircle cx=\'10\' cy=\'10\' r=\'2\'/%3E%3Ccircle cx=\'50\' cy=\'10\' r=\'2\'/%3E%3Ccircle cx=\'10\' cy=\'50\' r=\'2\'/%3E%3Ccircle cx=\'50\' cy=\'50\' r=\'2\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 animate-fade-in">
                {{ __('about.hero.title') }}
            </h1>
            <p class="text-xl md:text-2xl text-violet-100 max-w-3xl mx-auto animate-fade-in-up">
                {{ __('about.hero.subtitle') }}
            </p>
        </div>

        {{-- Wave Divider --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-12 md:h-20">
                <path
                    d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                    fill="#f9fafb" />
            </svg>
        </div>
    </div>

    <div class="bg-gray-50 py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Our Story Section --}}
            <div
                class="bg-white rounded-2xl shadow-lg p-8 lg:p-12 mb-12 transform hover:scale-[1.01] transition-transform duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-12 bg-gradient-to-b from-violet-600 to-purple-600 rounded-full"></div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">
                        {{ __('about.our_story.title') }}
                    </h2>
                </div>
                <div class="prose prose-lg prose-violet max-w-none">
                    <p class="text-gray-700 leading-relaxed mb-4">
                        {{ __('about.our_story.paragraph_1') }}
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        {{ __('about.our_story.paragraph_2') }}
                    </p>
                </div>
            </div>

            {{-- Our Vision Section --}}
            <div
                class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl shadow-lg p-8 lg:p-12 mb-12 border border-violet-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-violet-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">
                        {{ __('about.our_vision.title') }}
                    </h2>
                </div>
                <p class="text-gray-700 text-lg leading-relaxed">
                    {{ __('about.our_vision.content') }}
                </p>
            </div>

            {{-- Our Values Section --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 lg:p-12 mb-12">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-1 h-12 bg-gradient-to-b from-violet-600 to-purple-600 rounded-full"></div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">
                        {{ __('about.our_values.title') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Quality --}}
                    <div
                        class="group bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-violet-100">
                        <div
                            class="w-14 h-14 bg-violet-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-purple-900 mb-3">
                            {{ __('about.our_values.quality.title') }}
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            {{ __('about.our_values.quality.description') }}
                        </p>
                    </div>

                    {{-- Transparency --}}
                    <div
                        class="group bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-violet-100">
                        <div
                            class="w-14 h-14 bg-violet-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-purple-900 mb-3">
                            {{ __('about.our_values.transparency.title') }}
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            {{ __('about.our_values.transparency.description') }}
                        </p>
                    </div>

                    {{-- Innovation --}}
                    <div
                        class="group bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-violet-100">
                        <div
                            class="w-14 h-14 bg-violet-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-purple-900 mb-3">
                            {{ __('about.our_values.innovation.title') }}
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            {{ __('about.our_values.innovation.description') }}
                        </p>
                    </div>

                    {{-- Customer Satisfaction --}}
                    <div
                        class="group bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-violet-100">
                        <div
                            class="w-14 h-14 bg-violet-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-purple-900 mb-3">
                            {{ __('about.our_values.customer_satisfaction.title') }}
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            {{ __('about.our_values.customer_satisfaction.description') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Our Achievements Section --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 lg:p-12 mb-12">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-1 h-12 bg-gradient-to-b from-violet-600 to-purple-600 rounded-full"></div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">
                        {{ __('about.our_achievements.title') }}
                    </h2>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Happy Customers --}}
                    <div
                        class="bg-gradient-to-br from-violet-600 to-purple-700 rounded-xl p-6 text-center text-white transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <div class="text-5xl font-bold mb-2">1000+</div>
                        <div class="text-violet-100 font-medium">{{ __('about.our_achievements.happy_customers') }}
                        </div>
                    </div>

                    {{-- Diverse Products --}}
                    <div
                        class="bg-gradient-to-br from-violet-600 to-purple-700 rounded-xl p-6 text-center text-white transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <div class="text-5xl font-bold mb-2">500+</div>
                        <div class="text-violet-100 font-medium">{{ __('about.our_achievements.diverse_products') }}
                        </div>
                    </div>

                    {{-- Years Experience --}}
                    <div
                        class="bg-gradient-to-br from-violet-600 to-purple-700 rounded-xl p-6 text-center text-white transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <div class="text-5xl font-bold mb-2">5+</div>
                        <div class="text-violet-100 font-medium">{{ __('about.our_achievements.years_experience') }}
                        </div>
                    </div>

                    {{-- Quality Guarantee --}}
                    <div
                        class="bg-gradient-to-br from-violet-600 to-purple-700 rounded-xl p-6 text-center text-white transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <div class="text-5xl font-bold mb-2">100%</div>
                        <div class="text-violet-100 font-medium">{{ __('about.our_achievements.quality_guarantee') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Why Choose Us Section --}}
            <div class="bg-gradient-to-br from-violet-600 to-purple-700 rounded-2xl shadow-2xl p-8 lg:p-12 text-white">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-bold">
                        {{ __('about.why_choose_us.title') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Authentic Products --}}
                    <div
                        class="flex gap-4 bg-white/10 rounded-xl p-6 backdrop-blur-sm hover:bg-white/20 transition-all duration-300">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center text-2xl">
                                ✨
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">{{ __('about.why_choose_us.authentic_products.title') }}
                            </h3>
                            <p class="text-violet-100 leading-relaxed">
                                {{ __('about.why_choose_us.authentic_products.description') }}
                            </p>
                        </div>
                    </div>

                    {{-- Fast Delivery --}}
                    <div
                        class="flex gap-4 bg-white/10 rounded-xl p-6 backdrop-blur-sm hover:bg-white/20 transition-all duration-300">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center text-2xl">
                                🚚
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">{{ __('about.why_choose_us.fast_delivery.title') }}</h3>
                            <p class="text-violet-100 leading-relaxed">
                                {{ __('about.why_choose_us.fast_delivery.description') }}
                            </p>
                        </div>
                    </div>

                    {{-- Competitive Prices --}}
                    <div
                        class="flex gap-4 bg-white/10 rounded-xl p-6 backdrop-blur-sm hover:bg-white/20 transition-all duration-300">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center text-2xl">
                                💎
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">{{ __('about.why_choose_us.competitive_prices.title') }}
                            </h3>
                            <p class="text-violet-100 leading-relaxed">
                                {{ __('about.why_choose_us.competitive_prices.description') }}
                            </p>
                        </div>
                    </div>

                    {{-- Excellent Service --}}
                    <div
                        class="flex gap-4 bg-white/10 rounded-xl p-6 backdrop-blur-sm hover:bg-white/20 transition-all duration-300">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center text-2xl">
                                🤝
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">{{ __('about.why_choose_us.excellent_service.title') }}
                            </h3>
                            <p class="text-violet-100 leading-relaxed">
                                {{ __('about.why_choose_us.excellent_service.description') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('styles')
        <style>
            @keyframes fade-in {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fade-in-up {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in {
                animation: fade-in 0.8s ease-out;
            }

            .animate-fade-in-up {
                animation: fade-in-up 0.8s ease-out 0.2s both;
            }
        </style>
    @endpush
</x-store-layout>