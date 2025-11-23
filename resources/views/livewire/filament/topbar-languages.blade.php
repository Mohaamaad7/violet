@php
    $locale = app()->getLocale();
@endphp

<div x-data
     x-on:locale-updated.window="
        document.documentElement.setAttribute('dir', ($event.detail.locale === 'ar') ? 'rtl' : 'ltr');
        if ($event.detail.reload) { setTimeout(() => window.location.reload(), 120); }
     "
     class="flex items-center gap-x-2">
    
    <!-- Arabic Language Button -->
    <button 
        wire:click="switch('ar')"
        type="button"
        @class([
            'fi-btn fi-btn-size-sm transition-all duration-150 rounded-md px-3 py-2 text-sm font-medium outline-hidden',
            'fi-btn-primary bg-violet-600 text-white shadow-md locale-active' => $locale === 'ar',
            'fi-btn-outlined border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-white/5' => $locale !== 'ar',
        ])
    >
        <x-heroicon-m-language class="w-4 h-4 me-1" />
        العربية
    </button>
    
    <!-- English Language Button -->
    <button 
        wire:click="switch('en')"
        type="button"
        @class([
            'fi-btn fi-btn-size-sm transition-all duration-150 rounded-md px-3 py-2 text-sm font-medium outline-hidden',
            'fi-btn-primary bg-violet-600 text-white shadow-md locale-active' => $locale === 'en',
            'fi-btn-outlined border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-white/5' => $locale !== 'en',
        ])
    >
        <x-heroicon-m-language class="w-4 h-4 me-1" />
        English
    </button>
</div>
