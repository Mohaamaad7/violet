<x-layouts.partners :heading="__('messages.partners.nav.payouts')">
    <div class="max-w-6xl mx-auto">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('messages.partners.nav.payouts') }}
            </x-slot>
            
            <div class="text-center py-12">
                <i class="ph ph-bank text-6xl text-gray-300 dark:text-gray-700 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400 font-medium">
                    صفحة طلبات الصرف قيد التطوير
                </p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                    ستتمكن قريباً من طلب سحب أرباحك وتتبع حالة الطلبات
                </p>
            </div>
        </x-filament::section>
    </div>
</x-layouts.partners>
