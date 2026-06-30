@props(['product'])

@php
    $categoryId = $product->category_id;
    $productId = $product->id;
    $activeRules = \App\Models\ComboRule::active()->ordered()
        ->where(function($q) use ($categoryId, $productId) {
            $q->whereHas('conditions', function($q) use ($categoryId) {
                $q->where('condition_type', 'category')->where('category_id', $categoryId);
            })->orWhereHas('conditions', function($q) use ($productId) {
                $q->where('condition_type', 'product')->where('product_id', $productId);
            });
        })
        ->with(['conditions.category', 'conditions.product'])
        ->get();
@endphp

@if($activeRules->isNotEmpty())
    <div class="mt-4 p-3 bg-fuchsia-50 border border-fuchsia-200 rounded-lg shadow-sm">
        <div class="flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-fuchsia-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <div>
                <h4 class="text-sm font-bold text-fuchsia-900 mb-1">هذا المنتج مشمول في عرض كومبو!</h4>
                
                @foreach($activeRules as $rule)
                    <div class="text-xs text-fuchsia-800 mt-1 pb-1 {{ !$loop->last ? 'border-b border-fuchsia-200' : '' }}">
                        <strong class="text-fuchsia-700">{{ $rule->name }}:</strong>
                        احصل على خصم {{ $rule->discount_percentage }}% عند شراء:
                        <ul class="list-disc list-inside mt-1 space-y-0.5 pr-2">
                            @foreach($rule->conditions as $condition)
                                <li>
                                    {{ $condition->required_quantity }}
                                    @if($condition->condition_type === 'product' && $condition->product)
                                        من {{ $condition->product->name }}
                                    @elseif($condition->category)
                                        من {{ $condition->category->name }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
