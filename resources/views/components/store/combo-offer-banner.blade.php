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
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                </svg>
                عروض الكومبو الحصرية!
            </h2>
        </div>
        
        <div class="grid {{ $gridCols }} gap-6">
            @foreach($rules as $rule)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group flex flex-col">
                    {{-- Image --}}
                    @if($rule->image_path)
                        <div class="aspect-[16/7] sm:aspect-video w-full overflow-hidden bg-gray-100">
                            <img src="{{ Storage::url($rule->image_path) }}" alt="{{ $rule->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    @else
                        <div class="aspect-[16/7] sm:aspect-video w-full bg-gradient-to-br from-violet-500 to-fuchsia-600 flex flex-col items-center justify-center text-white relative overflow-hidden">
                            <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E');"></div>
                            <h3 class="text-2xl sm:text-3xl font-bold z-10">{{ $rule->name }}</h3>
                            <span class="mt-2 bg-white/20 px-3 py-1 rounded-full text-sm font-medium z-10 backdrop-blur-sm">
                                @if($rule->discount_type === 'percentage')
                                    خصم {{ $rule->discount_percentage }}%
                                @else
                                    وفر وتألق!
                                @endif
                            </span>
                        </div>
                    @endif

                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 mb-1">{{ $rule->name }}</h4>
                                <p class="text-sm text-gray-500 line-clamp-2">{{ $rule->description }}</p>
                            </div>
                            
                            <div class="shrink-0 mr-4">
                                @if($rule->discount_type === 'percentage')
                                    <span class="inline-flex items-center justify-center bg-green-100 text-green-700 font-bold px-3 py-1.5 rounded-lg text-sm">
                                        خصم {{ $rule->discount_percentage }}%
                                    </span>
                                @else
                                    <span class="inline-flex flex-col items-center justify-center bg-violet-100 text-violet-700 font-bold px-3 py-1.5 rounded-lg text-sm">
                                        <span class="text-[10px] font-normal leading-none mb-1">سعر العرض</span>
                                        <span>{{ number_format($rule->fixed_price, 0) }} ج.م</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-auto">
                            <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">شروط الكومبو:</h5>
                            <div class="space-y-2">
                                @foreach($rule->conditions as $condition)
                                    @if($condition->category)
                                        <div class="flex items-center gap-3 text-sm text-gray-700 bg-gray-50 p-2 rounded-lg border border-gray-100">
                                            <div class="w-8 h-8 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center font-bold shrink-0">
                                                {{ $condition->required_quantity }}
                                            </div>
                                            <span class="flex-1">من قسم <span class="font-bold text-gray-900">{{ $condition->category->name }}</span></span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
