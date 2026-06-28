@php
    $rules = \App\Models\ComboRule::active()->ordered()->with('conditions.category')->get();
    $count = $rules->count();
    $gridCols = 'grid-cols-1';
    if ($count == 2) {
        $gridCols = 'grid-cols-1 md:grid-cols-2';
    } elseif ($count >= 3) {
        $gridCols = 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3';
    }
@endphp

@if($rules->isNotEmpty())
    <div class="mb-10">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                عروض الكومبو المميزة
            </h2>
        </div>
        
        <div class="grid {{ $gridCols }} gap-4 sm:gap-6">
            @foreach($rules as $rule)
                <div class="bg-white rounded-[2rem] shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100/50 overflow-hidden hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.1)] transition-all duration-300 group flex items-stretch relative h-36 sm:h-44">
                    
                    {{-- Right Side (Text & Info) - RTL means flex starts from right --}}
                    <div class="flex-1 p-4 sm:p-6 flex flex-col justify-center text-right z-10">
                        <h3 class="text-base sm:text-xl font-bold text-gray-900 mb-1 sm:mb-2 line-clamp-1">{{ $rule->name }}</h3>
                        
                        <p class="text-xs sm:text-sm text-gray-500 mb-auto line-clamp-2">
                            {{ $rule->description }}
                        </p>

                        <div class="flex items-end justify-between mt-2">
                            <div class="flex flex-col">
                                {{-- Price Section --}}
                                @if($rule->discount_type === 'percentage')
                                    <span class="text-[10px] sm:text-xs text-gray-400 font-medium">خصم إضافي</span>
                                    <span class="text-base sm:text-xl font-bold text-gray-900">{{ $rule->discount_percentage }}%</span>
                                @else
                                    <span class="text-[10px] sm:text-xs text-gray-400">سعر العرض</span>
                                    <span class="text-base sm:text-xl font-bold text-gray-900">{{ number_format($rule->fixed_price, 0) }} EGP</span>
                                @endif
                            </div>

                            <a href="/products" class="inline-flex items-center gap-1 sm:gap-2 text-xs sm:text-sm font-bold text-gray-700 hover:text-violet-700 transition-colors bg-white hover:bg-gray-50 border border-gray-200 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl shadow-sm">
                                تسوق الآن
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Left Side (Image & Badge) --}}
                    <div class="w-[40%] sm:w-[45%] h-full relative shrink-0 bg-gradient-to-l from-violet-50/50 to-violet-100 overflow-visible">
                        
                        {{-- Decorative background shapes --}}
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-[80%] h-4 bg-black/5 rounded-[100%] blur-sm"></div>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-24 sm:w-32 sm:h-32 bg-white/60 rounded-full blur-xl"></div>
                        
                        {{-- Image --}}
                        @if($rule->image_path)
                            <img src="{{ Storage::url($rule->image_path) }}" alt="{{ $rule->name }}" class="absolute inset-0 w-full h-full object-contain p-2 sm:p-4 group-hover:scale-105 transition-transform duration-500 z-10 drop-shadow-md">
                        @else
                            <div class="absolute inset-0 w-full h-full flex flex-col items-center justify-center z-10 text-violet-300 p-4">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                        @endif

                        {{-- Discount Badge (Overlapping the center border) --}}
                        <div class="absolute top-1/2 -right-4 sm:-right-6 transform -translate-y-1/2 z-20">
                            <div class="w-10 h-10 sm:w-14 sm:h-14 bg-[#F2C94C] rounded-full flex flex-col items-center justify-center text-gray-900 font-bold shadow-sm border-[3px] sm:border-[4px] border-white group-hover:scale-110 transition-transform duration-300">
                                @if($rule->discount_type === 'percentage')
                                    <span class="text-xs sm:text-base leading-none tracking-tighter">-{{ $rule->discount_percentage }}%</span>
                                @else
                                    <span class="text-[9px] sm:text-[11px] leading-none mb-0.5">خصم</span>
                                    <span class="text-[10px] sm:text-xs leading-none font-black">ثابت</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
