@props(['offers'])

<div x-data="offersGrid(@js($offers))" id="offers-grid">
    {{-- Filter Tab Bar --}}
    <div class="flex flex-wrap items-center justify-center gap-2 md:gap-4 mb-10 bg-white p-2 rounded-2xl shadow-sm border border-gray-100 max-w-3xl mx-auto">
        <button @click="setTab('all')" :class="tab === 'all' ? 'bg-violet-600 text-white shadow-md' : 'bg-transparent text-gray-600 hover:bg-gray-50'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300">الكل</button>
        <button @click="setTab('discount')" :class="tab === 'discount' ? 'bg-violet-600 text-white shadow-md' : 'bg-transparent text-gray-600 hover:bg-gray-50'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300">أكواد الخصم</button>
        <button @click="setTab('combo')" :class="tab === 'combo' ? 'bg-violet-600 text-white shadow-md' : 'bg-transparent text-gray-600 hover:bg-gray-50'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300">عروض الكومبو</button>
        <button @click="setTab('bundle')" :class="tab === 'bundle' ? 'bg-violet-600 text-white shadow-md' : 'bg-transparent text-gray-600 hover:bg-gray-50'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300">عروض المنتجات</button>
    </div>

    {{-- Dynamic Grid --}}
    <div x-show="visibleOffers.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="grid gap-6 transition-all duration-500" 
         :style="`grid-template-columns: repeat(${isMobile ? 1 : Math.min(visibleOffers.length, 3)}, 1fr)`"
         @resize.window="checkMobile()">
        
        <template x-for="offer in visibleOffers" :key="offer.id">
            {{-- Vertical Offer Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col group">
                {{-- Image Top --}}
                <div class="relative w-full pt-[100%] bg-gray-50 overflow-hidden">
                    <img :src="offer.image" :alt="offer.title" class="absolute inset-0 w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-500" onerror="this.onerror=null; this.src='https://placehold.co/400x400/e9d5ff/6b21a8?text=' + encodeURIComponent(offer.title)">
                    
                    <div x-show="offer.discount_percentage" class="absolute top-4 right-4 bg-red-500 text-white font-black px-3 py-1.5 rounded-lg text-sm shadow-md">
                        <span x-text="'-' + offer.discount_percentage + '%'"></span>
                    </div>
                </div>
                
                {{-- Content Bottom --}}
                <div class="p-5 flex flex-col flex-grow">
                    <div class="mb-auto">
                        <div class="flex items-center gap-2 mb-2">
                            <span x-show="offer.type === 'combo'" class="bg-violet-100 text-violet-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">كومبو</span>
                            <span x-show="offer.type === 'discount'" class="bg-fuchsia-100 text-fuchsia-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">كود خصم</span>
                            <span x-show="offer.type === 'bundle'" class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">منتج</span>
                        </div>
                        <h3 class="font-black text-lg text-gray-900 mb-2 leading-tight" x-text="offer.title"></h3>
                        <p class="text-sm text-gray-500 line-clamp-2 leading-relaxed" x-text="offer.description"></p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex flex-col mb-4">
                            <template x-if="offer.original_price">
                                <span class="text-sm text-gray-400 line-through font-medium" x-text="offer.original_price + ' ' + offer.currency"></span>
                            </template>
                            <template x-if="offer.offer_price">
                                <span class="text-xl font-black text-violet-600" x-text="offer.offer_price + ' ' + offer.currency"></span>
                            </template>
                            <template x-if="!offer.offer_price && offer.discount_percentage && offer.type === 'discount'">
                                <span class="text-xl font-black text-violet-600" x-text="'خصم ' + offer.discount_percentage + '%'"></span>
                            </template>
                        </div>
                        
                        <a :href="offer.type === 'combo' ? '/products' : (offer.type === 'discount' ? '/products' : '/products')" 
                           class="w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-violet-600 text-white font-bold py-3 px-4 rounded-xl transition-colors group/btn">
                            <span x-text="offer.type === 'discount' ? 'تسوق واستخدم الكود' : 'تسوق الآن'"></span>
                            <svg class="w-4 h-4 transform rotate-180 group-hover/btn:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty State --}}
    <div x-show="visibleOffers.length === 0" style="display: none;" class="text-center py-20">
        <div class="text-6xl mb-4 opacity-50">🛒</div>
        <h3 class="text-2xl font-bold text-gray-700 mb-2">لا توجد عروض حالياً</h3>
        <p class="text-gray-500">يرجى التحقق مرة أخرى لاحقاً للحصول على عروض جديدة.</p>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Only define if not already defined to avoid errors when used multiple times
        if (!Alpine.data('offersGrid')) {
            Alpine.data('offersGrid', (initialOffers) => ({
                offers: initialOffers,
                tab: 'all',
                isMobile: window.innerWidth < 768,
                
                init() {
                    const params = new URLSearchParams(window.location.search);
                    if (params.has('type')) {
                        this.tab = params.get('type');
                    }
                    if (window.location.hash) {
                        const hash = window.location.hash.replace('#', '');
                        if (['combo', 'discount', 'bundle'].includes(hash)) {
                            this.tab = hash;
                            setTimeout(() => {
                                document.getElementById('offers-grid')?.scrollIntoView({ behavior: 'smooth' });
                            }, 500);
                        }
                    }
                    this.checkMobile();
                },
                
                get visibleOffers() {
                    if (this.tab === 'all') return this.offers;
                    return this.offers.filter(offer => offer.type === this.tab);
                },
                
                setTab(newTab) {
                    this.tab = newTab;
                    const url = new URL(window.location);
                    if (newTab === 'all') {
                        url.searchParams.delete('type');
                    } else {
                        url.searchParams.set('type', newTab);
                    }
                    window.history.pushState({}, '', url);
                },
                
                checkMobile() {
                    this.isMobile = window.innerWidth < 768;
                }
            }));
        }
    });
</script>
@endpush
