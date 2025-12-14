<div class="bg-cream-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Back Link --}}
        <a href="{{ route('account.orders') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-violet-600 mb-6">
            <svg class="w-4 h-4 me-1 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            {{ __('messages.account.back_to_orders') }}
        </a>
        
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.account.order') }} #{{ $order->order_number }}</h1>
                <p class="mt-2 text-gray-600">{{ __('messages.account.placed_on') }} {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
            </div>
            
            @php
                $statusColorMap = [
                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'delivered' => 'bg-green-100 text-green-800 border-green-200',
                    'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                    'rejected' => 'bg-red-100 text-red-800 border-red-200',
                ];
                $statusKey = $order->status?->toString() ?? 'pending';
                $statusLabel = $order->status?->label() ?? __('messages.account.status.pending');
            @endphp
            <span class="self-start px-4 py-2 text-sm font-semibold rounded-full border {{ $statusColorMap[$statusKey] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                {{ $statusLabel }}
            </span>
        </div>
        
        {{-- Order Progress Timeline --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">{{ __('messages.account.order_progress') }}</h2>
            
            @php
                $steps = ['pending', 'processing', 'shipped', 'delivered'];
                $currentStatusKey = $order->status?->toString() ?? 'pending';
                $currentIndex = array_search($currentStatusKey, $steps);
                if ($currentStatusKey === 'cancelled' || $currentStatusKey === 'rejected') $currentIndex = -1;
            @endphp
            
            <div class="relative">
                {{-- Progress Line --}}
                <div class="absolute top-5 start-0 end-0 h-0.5 bg-gray-200">
                    <div class="h-full bg-violet-600 transition-all duration-500" 
                         style="width: {{ $currentIndex >= 0 ? (($currentIndex / (count($steps) - 1)) * 100) : 0 }}%">
                    </div>
                </div>
                
                {{-- Steps --}}
                <div class="relative flex justify-between">
                    @foreach($steps as $index => $step)
                        @php
                            $isCompleted = $currentIndex >= $index;
                            $isCurrent = $currentIndex === $index;
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $isCompleted ? 'bg-violet-600 text-white' : 'bg-gray-200 text-gray-400' }} {{ $isCurrent ? 'ring-4 ring-violet-100' : '' }}">
                                @if($isCompleted && !$isCurrent)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <span class="text-sm font-semibold">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <span class="mt-2 text-xs font-medium {{ $isCompleted ? 'text-violet-600' : 'text-gray-400' }}">
                                {{ __('messages.account.status.' . $step) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            
            @if($currentStatusKey === 'cancelled' || $currentStatusKey === 'rejected')
                <div class="mt-6 p-4 bg-red-50 rounded-lg border border-red-100">
                    <p class="text-sm text-red-800">
                        <span class="font-semibold">{{ __('messages.account.order_cancelled') }}:</span>
                        {{ $order->cancellation_reason ?? __('messages.account.no_reason') }}
                    </p>
                </div>
            @endif
        </div>
        
        {{-- Order Items --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.account.order_items') }}</h2>
            </div>
            
            <div class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                    <div class="p-6 flex items-center gap-4">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            @if($item->product && $item->product->slug)
                                <a href="{{ route('product.show', $item->product->slug) }}" class="block w-full h-full">
                                    @if($item->product->getFirstMediaUrl('images', 'thumb'))
                                        <img 
                                            src="{{ $item->product->getFirstMediaUrl('images', 'thumb') }}" 
                                            alt="{{ $item->product->name ?? '' }}"
                                            class="w-full h-full object-cover hover:opacity-80 transition-opacity"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </a>
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            @if($item->product && $item->product->slug)
                                <a href="{{ route('product.show', $item->product->slug) }}" class="font-semibold text-gray-900 hover:text-violet-600 transition-colors truncate block">
                                    {{ $item->product->name }}
                                </a>
                            @else
                                <h3 class="font-semibold text-gray-500 truncate">
                                    {{ $item->product_name ?? __('messages.account.product_unavailable') }}
                                </h3>
                            @endif
                            @if($item->variant_name)
                                <p class="text-sm text-gray-500">{{ $item->variant_name }}</p>
                            @endif
                            <p class="text-sm text-gray-500 mt-1">
                                {{ __('messages.account.quantity') }}: {{ $item->quantity }}
                            </p>
                        </div>
                        
                        <div class="text-end">
                            <p class="font-semibold text-gray-900">{{ number_format($item->subtotal, 2) }} {{ __('messages.egp') }}</p>
                            <p class="text-sm text-gray-500">{{ number_format($item->price, 2) }} {{ __('messages.egp') }} × {{ $item->quantity }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Order Summary --}}
            <div class="bg-gray-50 p-6">
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('messages.account.subtotal') }}</dt>
                        <dd class="text-gray-900">{{ number_format($order->subtotal, 2) }} {{ __('messages.egp') }}</dd>
                    </div>
                    
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">{{ __('messages.account.discount') }}</dt>
                            <dd class="text-green-600">-{{ number_format($order->discount_amount, 2) }} {{ __('messages.egp') }}</dd>
                        </div>
                    @endif
                    
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">{{ __('messages.account.shipping') }}</dt>
                        <dd class="text-gray-900">
                            @if($order->shipping_cost > 0)
                                {{ number_format($order->shipping_cost, 2) }} {{ __('messages.egp') }}
                            @else
                                {{ __('messages.account.free') }}
                            @endif
                        </dd>
                    </div>
                    
                    @if($order->tax_amount > 0)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">{{ __('messages.account.tax') }}</dt>
                            <dd class="text-gray-900">{{ number_format($order->tax_amount, 2) }} {{ __('messages.egp') }}</dd>
                        </div>
                    @endif
                    
                    <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-200">
                        <dt class="text-gray-900">{{ __('messages.account.total') }}</dt>
                        <dd class="text-gray-900">{{ number_format($order->total, 2) }} {{ __('messages.egp') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        {{-- Order Details Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Shipping Address --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.account.shipping_address') }}</h2>
                
                @if($order->shippingAddress)
                    <div class="text-gray-600">
                        <p class="font-semibold text-gray-900">{{ $order->shippingAddress->full_name }}</p>
                        <p class="mt-1" dir="ltr">{{ $order->shippingAddress->phone }}</p>
                        <p class="mt-2">{{ $order->shippingAddress->full_address }}</p>
                    </div>
                @else
                    {{-- Guest Order Address --}}
                    <div class="text-gray-600">
                        <p class="font-semibold text-gray-900">{{ $order->guest_name }}</p>
                        <p class="mt-1" dir="ltr">{{ $order->guest_phone }}</p>
                        <p class="mt-2">{{ $order->guest_address }}, {{ $order->guest_city }}, {{ $order->guest_governorate }}</p>
                    </div>
                @endif
            </div>
            
            {{-- Payment Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.account.payment_info') }}</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('messages.account.payment_method') }}</span>
                        <span class="font-medium text-gray-900">
                            {{ __('messages.account.payment_methods.' . $order->payment_method) }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('messages.account.payment_status') }}</span>
                        @php
                            $paymentStatusColors = [
                                'pending' => 'text-yellow-600',
                                'paid' => 'text-green-600',
                                'failed' => 'text-red-600',
                                'refunded' => 'text-purple-600',
                            ];
                        @endphp
                        <span class="font-medium {{ $paymentStatusColors[$order->payment_status] ?? 'text-gray-600' }}">
                            {{ __('messages.account.payment_statuses.' . $order->payment_status) }}
                        </span>
                    </div>
                    
                    @if($order->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ __('messages.account.paid_on') }}</span>
                            <span class="font-medium text-gray-900">{{ $order->paid_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Order Notes --}}
        @if($order->notes)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.account.order_notes') }}</h2>
                <p class="text-gray-600">{{ $order->notes }}</p>
            </div>
        @endif
        
        {{-- Status History --}}
        @if($order->statusHistory->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.account.status_history') }}</h2>
                
                <div class="space-y-4">
                    @foreach($order->statusHistory as $history)
                        <div class="flex items-start gap-4">
                            <div class="w-2 h-2 mt-2 rounded-full bg-violet-600 flex-shrink-0"></div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ __('messages.account.status.' . $history->status) }}</p>
                                @if($history->notes)
                                    <p class="text-sm text-gray-500 mt-1">{{ $history->notes }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">{{ $history->created_at->format('M d, Y \a\t h:i A') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        {{-- Help Section --}}
        <div class="bg-violet-50 rounded-xl p-6 mt-8 text-center">
            <h3 class="text-lg font-semibold text-violet-900">{{ __('messages.account.need_help') }}</h3>
            <p class="text-violet-700 mt-2">{{ __('messages.account.contact_support') }}</p>
            <a href="mailto:support@violet.com" class="inline-flex items-center mt-4 text-violet-600 hover:text-violet-800 font-medium">
                <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                support@violet.com
            </a>
        </div>
    </div>
</div>
