@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <svg class="mx-auto h-12 w-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                {{ __('Unsubscribe from Newsletter') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('We\'re sorry to see you go') }}
            </p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-8">
            <div class="mb-6">
                <p class="text-gray-700 mb-2">
                    {{ __('Email') }}: <strong>{{ $subscriber->email }}</strong>
                </p>
                <p class="text-sm text-gray-500">
                    {{ __('Subscribed on') }}: {{ $subscriber->subscribed_at->format('d M Y') }}
                </p>
            </div>

            <form action="{{ route('newsletter.unsubscribe.process', $subscriber->unsubscribe_token) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Why are you unsubscribing?') }} {{ __('(Optional)') }}
                    </label>
                    <textarea 
                        name="reason" 
                        id="reason" 
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="{{ __('Your feedback helps us improve') }}"
                    ></textarea>
                </div>

                <div class="flex gap-4">
                    <button 
                        type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200"
                    >
                        {{ __('Unsubscribe') }}
                    </button>
                    
                    <a 
                        href="{{ route('home') }}"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-lg text-center transition duration-200"
                    >
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6">
            {{ __('Changed your mind? You can always resubscribe later!') }}
        </p>
    </div>
</div>
@endsection
