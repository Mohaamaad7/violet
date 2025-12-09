<div class="space-y-4">
    <div class="rounded-lg bg-danger-50 p-6 dark:bg-danger-900/20">
        <div class="flex gap-4">
            <x-filament::icon
                icon="heroicon-o-x-circle"
                class="h-6 w-6 text-danger-500 flex-shrink-0"
            />
            <div class="flex-1">
                <h3 class="text-base font-semibold text-danger-800 dark:text-danger-200 mb-2">
                    خطأ في عرض المعاينة
                </h3>
                <p class="text-sm text-danger-700 dark:text-danger-300">
                    {{ $error }}
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
            الحلول المقترحة:
        </h4>
        <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 dark:text-gray-300">
            <li>تأكد من أن القالب يحتوي على محتوى HTML صالح</li>
            <li>تحقق من أن جميع المتغيرات مكتوبة بشكل صحيح</li>
            <li>قم بحفظ التغييرات أولاً ثم حاول مرة أخرى</li>
        </ul>
    </div>
</div>
