@php
    $rules = \App\Models\ComboRule::active()->ordered()->with('conditions.category')->get();
@endphp

@if($rules->isNotEmpty())
    <section class="mb-16">
        <h2 class="text-2xl font-bold mb-8 text-gray-900">عروض الكومبو المميزة</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            @foreach($rules as $rule)
                <!-- Card -->
                <div class="bg-[#F8F4FB] rounded-2xl p-4 flex items-center border border-purple-50 shadow-sm relative group overflow-hidden" style="height: 160px;">
                    <!-- Text Info -->
                    <div class="z-10 pl-2" style="width: 55%;">
                        <h3 class="font-bold text-[16px] md:text-[17px] text-gray-900 mb-1 leading-tight line-clamp-1">{{ $rule->name }}</h3>
                        <p class="text-[11px] md:text-xs text-gray-500 mb-2 leading-relaxed line-clamp-2">{{ $rule->description ?? 'منتجات مختارة بعناية' }}</p>
                        
                        <div class="mb-3">
                            @if($rule->discount_type === 'percentage')
                                <span class="font-bold text-sm md:text-lg text-gray-900">خصم {{ $rule->discount_percentage }}%</span>
                            @else
                                <span class="font-bold text-sm md:text-lg text-gray-900">{{ number_format($rule->fixed_price, 0) }} EGP</span>
                            @endif
                        </div>
                        
                        <a href="/products" class="bg-transparent border border-gray-300 text-gray-700 px-3 py-1 rounded-lg inline-flex items-center gap-1 hover:bg-white transition text-[10px] md:text-xs font-bold group-hover:border-violet-600 group-hover:text-violet-600">
                            تسوق الآن
                            <i class="fa-solid fa-arrow-left text-[9px]"></i>
                        </a>
                    </div>
                    <!-- Image Area -->
                    <div class="relative h-full flex items-center justify-center" style="width: 45%;">
                        @if($rule->image_path)
                            <img src="{{ asset('storage/' . $rule->image_path) }}" alt="{{ $rule->name }}" class="drop-shadow-md rounded-lg" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        @else
                            <img src="https://placehold.co/200x250/e9d5ff/6b21a8?text={{ urlencode($rule->name) }}" alt="{{ $rule->name }}" class="drop-shadow-md rounded-lg" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        @endif
                        
                        <!-- Discount Badge -->
                        <div class="absolute bottom-0 right-0 bg-yellow-400 text-gray-900 font-extrabold rounded-full w-10 h-10 md:w-12 md:h-12 flex items-center justify-center text-[10px] md:text-sm border-[2px] border-white shadow-md z-20 transform translate-x-2 translate-y-2">
                            @if($rule->discount_type === 'percentage')
                                -{{ $rule->discount_percentage }}%
                            @else
                                خصم
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </section>
@endif
