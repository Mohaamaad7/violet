<x-layouts.partners :heading="__('messages.partners.nav.profile')">
    <div class="max-w-4xl mx-auto">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('messages.partners.nav.profile') }}
            </x-slot>
            
            <div class="text-center py-12">
                <i class="ph ph-user-circle text-6xl text-gray-300 dark:text-gray-700 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400 font-medium">
                    صفحة الملف الشخصي قيد التطوير
                </p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                    ستتمكن قريباً من تعديل بياناتك وحساباتك على وسائل التواصل الاجتماعي
                </p>
            </div>
        </x-filament::section>
    </div>
</x-layouts.partners>
