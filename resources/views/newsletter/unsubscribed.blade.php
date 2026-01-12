@extends('layouts.newsletter')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-md mx-auto text-center">
        <div class="bg-white shadow-md rounded-lg p-8">
            <svg class="mx-auto h-16 w-16 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                {{ __('newsletter.successfully_unsubscribed') }}
            </h2>
            
            <p class="text-gray-600 mb-6">
                {{ __('newsletter.removed_from_list') }}
            </p>
            
            <p class="text-sm text-gray-500 mb-8">
                {{ __('newsletter.no_more_emails') }}
            </p>
            
            <a 
                href="{{ route('home') }}"
                class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200"
            >
                {{ __('newsletter.back_to_home') }}
            </a>
        </div>
    </div>
</div>
@endsection
