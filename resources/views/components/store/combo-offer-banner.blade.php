@php
    $rules = \App\Models\ComboRule::active()->ordered()->with('conditions.category')->get();
@endphp

@if($rules->isNotEmpty())
    <section class="mb-16">
        <h2 class="text-2xl font-bold mb-8 text-gray-900">عروض الكومبو المميزة</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            @foreach($rules as $rule)
                <!-- Card -->
                <div class="bg-[#F8F4FB] rounded-2xl p-5 flex items-center border border-purple-50 shadow-sm relative group overflow-hidden">
                    <!-- Text Info -->
                    <div class="w-3/5 z-10 pl-2">
                        <h3 class="font-bold text-[17px] text-gray-900 mb-1">{{ $rule->name }}</h3>
                        <p class="text-xs text-gray-500 mb-3 leading-relaxed">{{ $rule->description ?? 'منتجات مختارة بعناية' }}</p>
                        
                        <div class="mb-4">
                            @if($rule->discount_type === 'percentage')
                                <span class="font-bold text-lg text-gray-900">خصم {{ $rule->discount_percentage }}%</span>
                            @else
                                <span class="font-bold text-lg text-gray-900">{{ number_format($rule->fixed_price, 0) }} EGP</span>
                            @endif
                        </div>
                        
                        <a href="/products" class="bg-transparent border border-gray-300 text-gray-700 px-4 py-1.5 rounded-lg inline-flex items-center gap-2 hover:bg-white transition text-xs font-bold group-hover:border-violet-600 group-hover:text-violet-600">
                            تسوق الآن
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        </a>
                    </div>
                    <!-- Image Area -->
                    <div class="w-2/5 relative h-full flex items-center justify-center">
                        @if($rule->image_path)
                            <img src="{{ asset('storage/' . $rule->image_path) }}" alt="{{ $rule->name }}" class="max-h-32 object-contain mix-blend-multiply drop-shadow-md">
                        @else
                            <img src="https://placehold.co/200x250/e9d5ff/6b21a8?text={{ urlencode($rule->name) }}" alt="{{ $rule->name }}" class="max-h-32 object-contain mix-blend-multiply drop-shadow-md">
                        @endif
                        
                        <!-- Discount Badge -->
                        <div class="absolute -bottom-2 -right-4 bg-yellow-400 text-gray-900 font-extrabold rounded-full w-12 h-12 flex items-center justify-center text-sm border-[3px] border-white shadow-sm z-20">
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
