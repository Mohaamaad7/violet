@extends('layouts.store')

@section('title', trans_db('messages.payment.select_title'))

@section('content')
    <div class="min-h-screen bg-gray-50 py-12" dir="rtl">
        <div class="max-w-2xl mx-auto px-4">
            {{-- Header --}}
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ trans_db('messages.payment.select_heading') }}</h1>
                <p class="text-gray-600 mt-2">{{ trans_db('messages.payment.order_number') }}: {{ $order->order_number }}
                </p>
                <p class="text-xl font-semibold text-violet-600 mt-2">{{ number_format($order->total, 2) }}
                    {{ trans_db('messages.payment.egp') }}</p>
            </div>

            {{-- Error Message --}}
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('payment.process', $order) }}" method="POST" id="payment-form">
                @csrf

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Online Payment Methods --}}
                    @if(count($paymentMethods) > 0)
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800 mb-4">{{ trans_db('messages.payment.online_payment') }}</h3>

                            <div class="space-y-3">
                                @foreach($paymentMethods as $code => $method)
                                    <label
                                        class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-violet-400 hover:bg-violet-50 transition-all payment-method-option">
                                        <input type="radio" name="payment_method" value="{{ $code }}"
                                            class="w-5 h-5 text-violet-600 border-gray-300 focus:ring-violet-500">
                                        <div class="mr-4 flex-1">
                                            <span class="font-medium text-gray-900">{{ $method['name'] }}</span>
                                            @if(isset($method['description']))
                                                <span class="text-sm text-gray-500 block">{{ $method['description'] }}</span>
                                            @endif
                                        </div>
                                        {{-- Icon placeholder --}}
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                                </path>
                                            </svg>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Cash on Delivery --}}
                    @if($codEnabled)
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 mb-4">{{ trans_db('messages.payment.cod_payment') }}</h3>

                            <label
                                class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-violet-400 hover:bg-violet-50 transition-all payment-method-option">
                                <input type="radio" name="payment_method" value="cod"
                                    class="w-5 h-5 text-violet-600 border-gray-300 focus:ring-violet-500">
                                <div class="mr-4 flex-1">
                                    <span class="font-medium text-gray-900">{{ trans_db('messages.payment.cod_option') }}</span>
                                    <span
                                        class="text-sm text-gray-500 block">{{ trans_db('messages.payment.cod_description') }}</span>
                                </div>
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                </div>
                            </label>
                        </div>
                    @endif
                </div>

                {{-- Submit Button --}}
                <div class="mt-6">
                    <button type="submit" id="submit-btn"
                        class="w-full bg-violet-600 hover:bg-violet-700 text-white font-semibold py-4 px-6 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="btn-text">{{ trans_db('messages.payment.proceed_payment') }}</span>
                        <span id="btn-loading" class="hidden">
                            <svg class="animate-spin inline-block w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ trans_db('messages.payment.processing_payment') }}
                        </span>
                    </button>
                </div>

                {{-- Back Link --}}
                <div class="text-center mt-4">
                    <a href="{{ route('checkout') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                        {{ trans_db('messages.payment.continue_shopping') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .payment-method-option:has(input:checked) {
            border-color: #8B5CF6;
            background-color: #F5F3FF;
        }
    </style>

    <script>
        document.getElementById('payment-form').addEventListener('submit', function (e) {
            const selected = document.querySelector('input[name="payment_method"]:checked');
            if (!selected) {
                e.preventDefault();
                alert('{{ trans_db("messages.checkout.invalid_payment") }}');
                return;
            }

            document.getElementById('submit-btn').disabled = true;
            document.getElementById('btn-text').classList.add('hidden');
            document.getElementById('btn-loading').classList.remove('hidden');
        });
    </script>
@endsection