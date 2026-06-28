@php
    $rules = \App\Models\ComboRule::active()->ordered()->with('conditions.category')->get();
    $count = $rules->count();
    $gridCols = 'grid-cols-1';
    if ($count == 2) {
        $gridCols = 'grid-cols-1 sm:grid-cols-2';
    } elseif ($count >= 3) {
        $gridCols = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
    }
@endphp

@if($rules->isNotEmpty())
    <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-xl shadow-lg p-4 sm:p-6 mb-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex-1 w-full">
                <h3 class="text-xl sm:text-2xl font-bold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                    عروض الكومبو الحصرية!
                </h3>
                
                <div class="grid {{ $gridCols }} gap-4 w-full">
                    @foreach($rules as $rule)
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm border border-white/20 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-yellow-300 mb-2">{{ $rule->name }}</h4>
                                <p class="text-sm text-gray-100 mb-3">{{ $rule->description }}</p>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 text-sm mt-auto">
                                @if($rule->discount_type === 'percentage')
                                    <span class="bg-violet-800 text-white px-2 py-1 rounded text-xs">خصم {{ $rule->discount_percentage }}%</span>
                                @else
                                    <span class="bg-violet-800 text-white px-2 py-1 rounded text-xs">سعر الكومبو {{ $rule->fixed_price }} ج.م</span>
                                @endif
                                
                                @foreach($rule->conditions as $condition)
                                    @if($condition->category)
                                        <span class="bg-white/20 px-2 py-1 rounded text-xs border border-white/30">
                                            اشتر {{ $condition->required_quantity }} من {{ $condition->category->name }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
