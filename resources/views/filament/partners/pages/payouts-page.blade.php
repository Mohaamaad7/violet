<x-layouts.partners :heading="__('messages.partners.nav.payouts')">
    <div>
        <div class="max-w-6xl mx-auto">
            <x-filament::section>
                <x-slot name="heading">
                    {{ __('messages.partners.nav.payouts') }}
                </x-slot>
                
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">
                        صفحة طلبات الصرف قيد التطوير
                    </p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                        ستتمكن قريباً من طلب سحب أرباحك وتتبع حالة الطلبات
                    </p>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-layouts.partners>
