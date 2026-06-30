@php
    $rules = \App\Models\ComboRule::active()->ordered()->with(['conditions.category', 'conditions.product'])->get();
@endphp

@if($rules->isNotEmpty())
    <section class="mb-16">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-600 to-purple-600 flex items-center justify-center text-white shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">عروض الكومبو المميزة</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            
            @foreach($rules as $rule)
                <!-- Premium Card -->
                <div class="relative rounded-[24px] overflow-hidden group bg-white border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(109,40,217,0.1)] transition-all duration-500 transform hover:-translate-y-1" style="height: 180px;">
                    
                    <!-- Subtle Background decoration -->
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-violet-50 rounded-full blur-3xl opacity-50 group-hover:bg-violet-100 transition-colors duration-500"></div>
                    
                    <div class="flex h-full relative z-10">
                        <!-- Text Area -->
                        <div class="flex-1 p-4 md:p-5 flex flex-col justify-between border-l border-gray-50/50" style="width: 55%;">
                            <div>
                                <div class="mb-2">
                                    <span class="inline-block px-3 py-1 bg-violet-50 text-violet-700 text-[10px] md:text-xs font-bold rounded-full border border-violet-100">
                                        @if($rule->discount_type === 'percentage')
                                            توفير {{ $rule->discount_percentage }}%
                                        @else
                                            عرض مميز
                                        @endif
                                    </span>
                                </div>
                                <h3 class="font-black text-gray-900 text-base md:text-lg mb-1 leading-tight line-clamp-1">{{ $rule->name }}</h3>
                                <p class="text-[11px] md:text-xs text-gray-500 leading-relaxed line-clamp-1">{{ $rule->description ?? 'باقة منتجات مختارة بعناية' }}</p>
                            </div>
                            
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex flex-col">
                                    @if($rule->discount_type === 'percentage')
                                        <span class="font-black text-base md:text-lg text-violet-600">خصم {{ $rule->discount_percentage }}%</span>
                                    @else
                                        <span class="text-[10px] text-gray-400 font-bold mb-0.5">فقط بسعر</span>
                                        <span class="font-black text-base md:text-lg text-violet-600">{{ number_format($rule->fixed_price, 0) }} ج.م</span>
                                    @endif
                                </div>
                                
                                <a href="/products" class="w-9 h-9 md:w-10 md:h-10 flex items-center justify-center bg-gray-900 text-white rounded-xl shadow-md hover:bg-violet-600 hover:shadow-lg transition-all duration-300 group/btn">
                                    <svg class="w-4 h-4 transform rotate-180 group-hover/btn:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        </div>

                        <!-- Image Area -->
                        <div class="relative h-full flex items-center justify-center p-3 overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100/50" style="width: 45%;">
                            <!-- Decorative circles in image background -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-20">
                                <div class="w-24 h-24 border border-violet-300 rounded-full absolute"></div>
                                <div class="w-32 h-32 border border-violet-200 rounded-full absolute"></div>
                            </div>
                            
                            @if($rule->image_path)
                                <img src="{{ asset('storage/' . $rule->image_path) }}" alt="{{ $rule->name }}" class="w-full h-full object-contain filter drop-shadow-xl group-hover:scale-110 transition-transform duration-500 relative z-10">
                            @else
                                <img src="https://placehold.co/400x400/e9d5ff/6b21a8?text={{ urlencode($rule->name) }}" alt="{{ $rule->name }}" class="w-full h-full object-contain filter drop-shadow-xl group-hover:scale-110 transition-transform duration-500 relative z-10">
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </section>
@endif
