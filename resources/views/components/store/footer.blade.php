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
                        <svg class="w-5 h-5 text-violet-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="text-sm text-gray-400">123 Violet Street, Commerce City, CC 12345</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <a href="mailto:support@violet.com"
                            class="text-sm text-gray-400 hover:text-violet-400 transition">
                            support@violet.com
                        </a>
                    </div>
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <a href="tel:+1234567890" class="text-sm text-gray-400 hover:text-violet-400 transition">
                            +1 (234) 567-890
                        </a>
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
                        <a href="#"
                            class="w-9 h-9 bg-gray-800 hover:bg-violet-600 rounded-lg flex items-center justify-center transition group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-9 h-9 bg-gray-800 hover:bg-violet-600 rounded-lg flex items-center justify-center transition group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-9 h-9 bg-gray-800 hover:bg-violet-600 rounded-lg flex items-center justify-center transition group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-9 h-9 bg-gray-800 hover:bg-violet-600 rounded-lg flex items-center justify-center transition group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
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