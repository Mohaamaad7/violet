@extends('layouts.store')

@section('title', 'تم الدفع بنجاح')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-green-50 to-white py-16" dir="rtl">
        <div class="max-w-lg mx-auto px-4 text-center">
            {{-- Success Icon --}}
            <div
                class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce-slow">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            {{-- Success Message --}}
            <h1 class="text-3xl font-bold text-gray-900 mb-2">تم الدفع بنجاح!</h1>
            <p class="text-gray-600 mb-8">شكراً لك، تم استلام دفعتك وجاري تجهيز طلبك</p>

            {{-- Order Details Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 text-right">
                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                    <span class="text-gray-500">رقم الطلب</span>
                    <span class="font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>

                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                    <span class="text-gray-500">المبلغ المدفوع</span>
                    <span class="font-semibold text-green-600">{{ number_format($order->total, 2) }} جنيه</span>
                </div>

                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                    <span class="text-gray-500">طريقة الدفع</span>
                    <span
                        class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500">حالة الطلب</span>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        قيد التجهيز
                    </span>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 text-right">
                <h3 class="font-semibold text-gray-900 mb-4">منتجات الطلب</h3>

                @foreach($order->items as $item)
                    <div class="flex items-center gap-4 py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 text-sm">{{ $item->product_name }}</p>
                            <p class="text-gray-500 text-xs">الكمية: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-medium text-gray-900">{{ number_format($item->subtotal, 2) }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('account.orders.show', $order) }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-violet-600 text-white font-semibold rounded-xl hover:bg-violet-700 transition-all">
                    تتبع الطلب
                </a>
                <a href="{{ route('store.index') }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all">
                    متابعة التسوق
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes bounce-slow {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-bounce-slow {
            animation: bounce-slow 2s infinite;
        }
    </style>
@endsection