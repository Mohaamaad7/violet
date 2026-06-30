<div class="min-h-screen bg-gray-50 pb-20">
    {{-- Hero Banner --}}
    <section class="relative bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-500 py-16 md:py-24 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>
        <div class="container mx-auto px-4 relative z-10 text-center text-white">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">العروض والخصومات</h1>
            <p class="text-lg md:text-xl text-white/80 max-w-2xl mx-auto">اكتشف أحدث العروض والخصومات والكومبو الحصرية</p>
        </div>
    </section>

    {{-- Main Content --}}
    <div class="container mx-auto px-4 -mt-8 relative z-20">
        <x-store.offers-grid :offers="$unifiedOffers" />
    </div>
</div>