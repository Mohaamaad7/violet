@extends('layouts.store')

@section('title', 'فشل الدفع')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-red-50 to-white py-16" dir="rtl">
        <div class="max-w-lg mx-auto px-4 text-center">
            {{-- Failed Icon --}}
            <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            {{-- Failed Message --}}
            <h1 class="text-3xl font-bold text-gray-900 mb-2">فشلت عملية الدفع</h1>
            <p class="text-gray-600 mb-4">عذراً، لم نتمكن من إتمام عملية الدفع</p>

            {{-- Error Details --}}
            @if($error)
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-8 text-right">
                    <p class="font-medium">سبب الفشل:</p>
                    <p class="text-sm">{{ $error }}</p>
                </div>
            @endif

            {{-- Order Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 text-right">
                <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-4">
                    <span class="text-gray-500">رقم الطلب</span>
                    <span class="font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500">المبلغ</span>
                    <span class="font-semibold text-gray-900">{{ number_format($order->total, 2) }} جنيه</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('payment.select', $order) }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-violet-600 text-white font-semibold rounded-xl hover:bg-violet-700 transition-all">
                    إعادة المحاولة
                </a>
                <a href="{{ route('checkout') }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all">
                    العودة للسلة
                </a>
            </div>

            {{-- Help Text --}}
            <p class="text-sm text-gray-500 mt-8">
                إذا استمرت المشكلة، يرجى التواصل مع خدمة العملاء
            </p>
        </div>
    </div>
@endsection