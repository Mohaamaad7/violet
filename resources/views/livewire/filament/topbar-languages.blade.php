<div x-data 
     x-on:locale-updated.window="
        document.documentElement.setAttribute('dir', ($event.detail.locale === 'ar') ? 'rtl' : 'ltr');
        if ($event.detail.reload) {
            setTimeout(() => window.location.reload(), 100);
        }
     " 
     class="flex items-center gap-2">
    <button type="button" wire:click="switch('ar')" class="px-2 py-1 rounded {{ app()->getLocale() === 'ar' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-700' }}" title="عربي">
        عربي
    </button>
    <span class="text-gray-300">|</span>
    <button type="button" wire:click="switch('en')" class="px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-gray-100 text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-700' }}" title="English">
        English
    </button>
</div>
