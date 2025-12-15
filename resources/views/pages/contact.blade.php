<x-store-layout>
    <x-slot name="title">{{ __('contact.page_title') }}</x-slot>
    <x-slot name="description">{{ __('contact.meta_description') }}</x-slot>

    {{-- Hero Section --}}
    <div class="relative bg-gradient-to-br from-violet-600 via-violet-700 to-purple-800 text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0"
                style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Ccircle cx=\'30\' cy=\'30\' r=\'2\'/%3E%3Ccircle cx=\'10\' cy=\'10\' r=\'2\'/%3E%3Ccircle cx=\'50\' cy=\'10\' r=\'2\'/%3E%3Ccircle cx=\'10\' cy=\'50\' r=\'2\'/%3E%3Ccircle cx=\'50\' cy=\'50\' r=\'2\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
                {{ __('contact.hero.title') }}
            </h1>
            <p class="text-xl md:text-2xl text-violet-100 max-w-3xl mx-auto">
                {{ __('contact.hero.subtitle') }}
            </p>
        </div>

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

            {{-- Contact Info & Form Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">

                {{-- Contact Information --}}
                <div class="bg-white rounded-2xl shadow-lg p-8 lg:p-12">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1 h-12 bg-gradient-to-b from-violet-600 to-purple-600 rounded-full"></div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">
                            {{ __('contact.contact_info.title') }}
                        </h2>
                    </div>

                    <div class="space-y-6">
                        {{-- Phone --}}
                        <div
                            class="flex items-start gap-4 p-6 bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl hover:shadow-md transition-all duration-300 border border-violet-100">
                            <div
                                class="flex-shrink-0 w-12 h-12 bg-violet-600 rounded-xl flex items-center justify-center text-2xl">
                                📱
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-purple-900 mb-2">
                                    {{ __('contact.contact_info.phone.title') }}
                                </h3>
                                <p class="text-gray-700 mb-3">
                                    <a href="tel:01091191056"
                                        class="text-violet-600 hover:text-violet-800 font-semibold">
                                        01091191056
                                    </a>
                                </p>
                                <a href="https://wa.me/201091191056?text=مرحباً، أريد الاستفسار عن منتجاتكم"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-full transition-all duration-300 shadow-md hover:shadow-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                    </svg>
                                    {{ __('contact.contact_info.phone.whatsapp_button') }}
                                </a>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div
                            class="flex items-start gap-4 p-6 bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl hover:shadow-md transition-all duration-300 border border-violet-100">
                            <div
                                class="flex-shrink-0 w-12 h-12 bg-violet-600 rounded-xl flex items-center justify-center text-2xl">
                                📍
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-purple-900 mb-2">
                                    {{ __('contact.contact_info.address.title') }}
                                </h3>
                                <p class="text-gray-700 leading-relaxed">
                                    {{ __('contact.contact_info.address.line1') }}<br>
                                    {{ __('contact.contact_info.address.line2') }}
                                </p>
                            </div>
                        </div>

                        {{-- Working Hours --}}
                        <div
                            class="flex items-start gap-4 p-6 bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl hover:shadow-md transition-all duration-300 border border-violet-100">
                            <div
                                class="flex-shrink-0 w-12 h-12 bg-violet-600 rounded-xl flex items-center justify-center text-2xl">
                                ⏰
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-purple-900 mb-2">
                                    {{ __('contact.contact_info.hours.title') }}
                                </h3>
                                <p class="text-gray-700 leading-relaxed">
                                    {{ __('contact.contact_info.hours.weekdays') }}<br>
                                    {{ __('contact.contact_info.hours.friday') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Form (Livewire Component) --}}
                <livewire:store.contact-form />

            </div>

            {{-- Social Media Section --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 lg:p-12 mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-center text-gray-900 mb-8">
                    {{ __('contact.social.title') }}
                </h2>

                <div class="flex justify-center gap-8 flex-wrap">
                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/people/Violet-Cosmetics" target="_blank"
                        class="group flex flex-col items-center transition-transform duration-300 hover:-translate-y-2">
                        <div
                            class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center mb-3 group-hover:shadow-2xl transition-shadow duration-300">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-700">{{ __('contact.social.facebook') }}</span>
                    </a>

                    {{-- Instagram --}}
                    <a href="https://www.instagram.com/violetcosmetics3?igsh=MXhqbWw4c2h6ZXF2bw==" target="_blank"
                        class="group flex flex-col items-center transition-transform duration-300 hover:-translate-y-2">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-3 group-hover:shadow-2xl transition-shadow duration-300"
                            style="background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" />
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-700">{{ __('contact.social.instagram') }}</span>
                    </a>

                    {{-- TikTok --}}
                    <a href="https://www.tiktok.com/@violet_cosmetics1?_r=1&_t=ZS-91TpDVEPdhA" target="_blank"
                        class="group flex flex-col items-center transition-transform duration-300 hover:-translate-y-2">
                        <div
                            class="w-20 h-20 bg-black rounded-2xl flex items-center justify-center mb-3 group-hover:shadow-2xl transition-shadow duration-300">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" />
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-700">{{ __('contact.social.tiktok') }}</span>
                    </a>
                </div>
            </div>

            {{-- Map Section --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 lg:p-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-center text-gray-900 mb-8">
                    {{ __('contact.map.title') }}
                </h2>
                <div class="w-full h-96 rounded-xl overflow-hidden shadow-md">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3452.9842!2d31.3372!3d30.1074!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583fa60b21beeb%3A0x79dfb296e8423bba!2s9%20Mohammed%20El-Sadat%2C%20Huckstep%2C%20El%20Nozha%2C%20Cairo%20Governorate%204473203!5e0!3m2!1sen!2seg!4v1734301234567!5m2!1sen!2seg"
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>
    </div>

</x-store-layout>