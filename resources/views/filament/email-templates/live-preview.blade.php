<div 
    x-data="{ 
        content: @entangle('data.content_html').live,
        availableVariables: {{ Js::from($getRecord()?->available_variables ?? []) }},
        sampleData: @js($getRecord() ? app(\App\Services\EmailTemplateService::class)->getSampleData($getRecord()) : []),
        
        replaceVariables(html) {
            if (!html || typeof html !== 'string') return '';
            
            let result = html;
            Object.keys(this.sampleData).forEach(key => {
                const regex = new RegExp('\\{\\{\\s*' + key + '\\s*\\}\\}', 'g');
                result = result.replace(regex, this.sampleData[key]);
            });
            return result;
        },
        
        get previewHtml() {
            return this.replaceVariables(this.content);
        }
    }"
    class="fi-fo-field-wrp"
>
    <div class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 overflow-hidden">
        <!-- Preview Header -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-300 dark:border-gray-600 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">معاينة مباشرة للبريد الإلكتروني</span>
            <span class="text-xs text-gray-500 dark:text-gray-400 mr-auto">(تتحدث تلقائياً)</span>
        </div>
        
        <!-- Preview Content -->
        <div class="p-4 bg-gray-50 dark:bg-gray-800">
            <div class="bg-white dark:bg-gray-900 rounded shadow-sm" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                <iframe 
                    x-ref="previewFrame"
                    :srcdoc="previewHtml"
                    class="w-full border-0 rounded"
                    style="min-height: 400px;"
                    @load="$refs.previewFrame.style.height = ($refs.previewFrame.contentWindow.document.body.scrollHeight + 40) + 'px'"
                ></iframe>
            </div>
        </div>
        
        <!-- Preview Info -->
        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 border-t border-gray-300 dark:border-gray-600">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                💡 <strong>ملاحظة:</strong> المتغيرات (مثل <code class="px-1 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-gray-800 dark:text-gray-200">{{ '{{ order_number }}' }}</code>) 
                يتم استبدالها ببيانات تجريبية للمعاينة فقط.
            </p>
        </div>
    </div>
</div>
