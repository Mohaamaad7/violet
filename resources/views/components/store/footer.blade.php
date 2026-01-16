{{-- Store Footer Component --}}
<footer class="bg-gray-900 text-gray-300 mt-auto">
    {{-- Main Footer Content --}}
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {{-- Company Info --}}
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-violet-500 to-violet-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-white">Violet</span>
                </div>
                <p class="text-sm text-gray-400 leading-relaxed mb-4">
                    {{ trans_db('store.footer.description') }}
                </p>
                {{-- Payment Methods --}}
                <div class="flex items-center gap-2 mt-4">
                    <span class="text-xs text-gray-500">{{ trans_db('store.footer.we_accept') }}</span>
                    <div class="flex gap-2">
                        <div class="w-10 h-6 bg-white rounded flex items-center justify-center">
                            <svg class="w-6 h-4" viewBox="0 0 48 32" fill="none">
                                <rect width="48" height="32" rx="4" fill="#1434CB" />
                                <path d="M17.5 16L21.5 12L17.5 8" stroke="white" stroke-width="2"
                                    stroke-linecap="round" />
                            </svg>
                        </div>
                        <div class="w-10 h-6 bg-white rounded flex items-center justify-center">
                            <svg class="w-6 h-4" viewBox="0 0 48 32">
                                <rect width="48" height="32" rx="4" fill="#EB001B" />
                                <circle cx="19" cy="16" r="10" fill="#FF5F00" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <h4 class="text-white font-bold text-lg mb-4">{{ trans_db('store.footer.quick_links') }}</h4>
                <ul class="space-y-2.5">
                    <li>
                        <a href="/about"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.about_us') }}
                        </a>
                    </li>
                    <li>
                        <a href="/products"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.shop_now') }}
                        </a>
                    </li>
                    <li>
                        <a href="/offers"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.special_offers') }}
                        </a>
                    </li>
                    <li>
                        <a href="/contact"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.contact_us') }}
                        </a>
                    </li>
                    <li>
                        <a href="/blog"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.blog') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Customer Service --}}
            <div>
                <h4 class="text-white font-bold text-lg mb-4">{{ trans_db('store.footer.customer_service') }}</h4>
                <ul class="space-y-2.5">
                    <li>
                        <a href="/help"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.help_center') }}
                        </a>
                    </li>
                    <li>
                        <a href="/shipping"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.shipping_info') }}
                        </a>
                    </li>
                    <li>
                        <a href="/returns"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.returns_refunds') }}
                        </a>
                    </li>
                    <li>
                        <a href="/track-order"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.track_order') }}
                        </a>
                    </li>
                    <li>
                        <a href="/faq"
                            class="text-gray-400 hover:text-violet-400 transition text-sm flex items-center gap-1.5 group">
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ trans_db('store.footer.faqs') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Contact & Newsletter --}}
            <div>
                <h4 class="text-white font-bold text-lg mb-4">{{ trans_db('store.footer.stay_connected') }}</h4>

                {{-- Contact Info --}}
                <div class="space-y-3 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div class="text-sm text-gray-400">
                            <a href="https://maps.app.goo.gl/xcyq9VRPMLZEKR7Q7" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:underline block" lang="ar" dir="rtl">
                                9 ش محمد السادات متفرع من طه حسين، النزهة الجديدة
                            </a>
                            <a href="https://maps.app.goo.gl/xcyq9VRPMLZEKR7Q7" target="_blank" rel="noopener noreferrer" class="text-violet-400 hover:underline block" lang="en" dir="ltr">
                                9 Mohamed El-Sadat St., off Taha Hussein, El Nozha El Gedida
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <div class="flex flex-col">
                            <a href="mailto:shereen.qassim@flowerviolet.com" class="text-sm text-gray-400 hover:text-violet-400 transition">shereen.qassim@flowerviolet.com</a>
                            <a href="mailto:abdelrahman.mahmoud@flowerviolet.com" class="text-sm text-gray-400 hover:text-violet-400 transition">abdelrahman.mahmoud@flowerviolet.com</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <a href="tel:+201091191056" class="text-sm text-gray-400 hover:text-violet-400 transition">+20 109 119 1056</a>
                    </div>
                </div>

                {{-- Newsletter Subscription --}}
                <div>
                    <h5 class="text-white font-semibold text-sm mb-2">{{ trans_db('store.footer.subscribe') }}</h5>
                    <p class="text-xs text-gray-500 mb-3">{{ trans_db('store.footer.newsletter_desc') }}</p>

                    {{-- Livewire Newsletter Component --}}
                    <livewire:store.newsletter-subscription />
                </div>
            </div>
        </div>
    </div>

    {{-- Social Links & Bottom Bar --}}
    <div class="border-t border-gray-800">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                {{-- Social Links --}}
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ trans_db('store.footer.follow_us') }}</span>
                    <div class="flex gap-3">
                        <a href="https://www.tiktok.com/@violet_cosmetics1?_r=1&_t=ZS-91TpDVEPdhA" target="_blank" rel="noopener noreferrer"
                            class="w-9 h-9 bg-gray-800 hover:bg-violet-600 rounded-lg flex items-center justify-center transition group" aria-label="TikTok">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.75 2h2.25v12.25a3.75 3.75 0 11-3.75-3.75h.75V9.5h-.75a5.25 5.25 0 105.25 5.25V7.25a5.25 5.25 0 002.25.5V5.5a3.75 3.75 0 01-2.25-.75V2h-2.25z" />
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/violetcosmetics3?igsh=MXhqbWw4c2h6ZXF2bw==" target="_blank" rel="noopener noreferrer"
                            class="w-9 h-9 bg-gray-800 hover:bg-violet-600 rounded-lg flex items-center justify-center transition group" aria-label="Instagram">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M7.75 2A5.75 5.75 0 002 7.75v8.5A5.75 5.75 0 007.75 22h8.5A5.75 5.75 0 0022 16.25v-8.5A5.75 5.75 0 0016.25 2h-8.5zm0 1.5h8.5A4.25 4.25 0 0120.5 7.75v8.5a4.25 4.25 0 01-4.25 4.25h-8.5A4.25 4.25 0 013.5 16.25v-8.5A4.25 4.25 0 017.75 3.5zm4.25 2.25a5.25 5.25 0 100 10.5 5.25 5.25 0 000-10.5zm0 1.5a3.75 3.75 0 110 7.5 3.75 3.75 0 010-7.5zm5.25.75a1 1 0 100 2 1 1 0 000-2z" />
                            </svg>
                        </a>
                        <a href="https://www.facebook.com/share/17VrMc1zY7/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer"
                            class="w-9 h-9 bg-gray-800 hover:bg-violet-600 rounded-lg flex items-center justify-center transition group" aria-label="Facebook">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.522-4.477-10-10-10S2 6.478 2 12c0 5.019 3.676 9.163 8.438 9.877v-6.987h-2.54v-2.89h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.242 0-1.632.771-1.632 1.562v1.875h2.773l-.443 2.89h-2.33v6.987C18.324 21.163 22 17.019 22 12z" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Copyright --}}
                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} <span class="text-violet-400 font-semibold">Violet</span>.
                        {{ trans_db('store.footer.copyright') }}
                    </p>
                    <div class="flex items-center justify-center md:justify-end gap-4 mt-2 text-xs text-gray-600">
                        <a href="/privacy"
                            class="hover:text-violet-400 transition">{{ trans_db('store.footer.privacy_policy') }}</a>
                        <span>•</span>
                        <a href="/terms"
                            class="hover:text-violet-400 transition">{{ trans_db('store.footer.terms') }}</a>
                        <span>•</span>
                        <a href="/cookies"
                            class="hover:text-violet-400 transition">{{ trans_db('store.footer.cookie_policy') }}</a>
                    </div>
                </div>
            </div>

            {{-- Powered By - Centered --}}
            <div class="text-center mt-6 pt-6 border-t border-gray-800">
                <p class="text-xs text-gray-500">
                    Powered by <span class="text-violet-400 font-semibold">Reyada E-commerce</span> v1 ·
                    <a href="https://www.areyada.com" target="_blank" rel="noopener noreferrer"
                        class="text-violet-400 hover:text-violet-300 font-medium transition-colors duration-200 hover:underline">
                        Reyada Solutions
                    </a>
                </p>
            </div>
        </div>
    </div>
</footer>