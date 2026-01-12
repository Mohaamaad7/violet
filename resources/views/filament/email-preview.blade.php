<div class="space-y-4">
    {{-- Template Info --}}
    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
        <div class="flex items-center gap-3 mb-3">
            <x-filament::icon
                icon="heroicon-o-information-circle"
                class="h-5 w-5 text-primary-500"
            />
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ __('newsletter.template_info_title') }}
            </h3>
        </div>
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">{{ __('newsletter.template_name_label') }}</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $template->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">{{ __('newsletter.template_type_label') }}</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $template->type }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">{{ __('newsletter.template_subject_ar_label') }}</dt>
                <dd class="font-medium text-gray-900 dark:text-white">{{ $template->subject_ar }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">{{ __('newsletter.template_status_label') }}</dt>
                <dd>
                    <x-filament::badge :color="$template->is_active ? 'success' : 'danger'">
                        {{ $template->is_active ? __('newsletter.template_active') : __('newsletter.template_inactive') }}
                    </x-filament::badge>
                </dd>
            </div>
        </dl>
    </div>

    {{-- Preview Warning --}}
    <div class="rounded-lg bg-warning-50 p-4 dark:bg-warning-900/20">
        <div class="flex gap-3">
            <x-filament::icon
                icon="heroicon-o-exclamation-triangle"
                class="h-5 w-5 text-warning-500 flex-shrink-0"
            />
            <div class="text-sm text-warning-800 dark:text-warning-200">
                <strong>{{ __('newsletter.email_preview_warning_title') }}</strong> {{ __('newsletter.email_preview_warning_body') }}
            </div>
        </div>
    </div>

    {{-- Email Preview --}}
    <div class="rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
        <div class="bg-gray-100 dark:bg-gray-700 px-4 py-3 border-b border-gray-300 dark:border-gray-600">
            <div class="flex items-center gap-2">
                <x-filament::icon
                    icon="heroicon-o-envelope"
                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('newsletter.email_preview_content_title') }}
                </span>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 p-4 max-h-[600px] overflow-y-auto">
            <iframe
                srcdoc="{{ htmlspecialchars($html, ENT_QUOTES) }}"
                class="w-full border-0"
                style="min-height: 500px;"
                onload="this.style.height = (this.contentWindow.document.body.scrollHeight + 50) + 'px';"
            ></iframe>
        </div>
    </div>

    {{-- Variables Used --}}
    @if(!empty($template->available_variables))
    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
        <div class="flex items-center gap-3 mb-3">
            <x-filament::icon
                icon="heroicon-o-variable"
                class="h-5 w-5 text-primary-500"
            />
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ __('newsletter.variables_used') }} ({{ count($template->available_variables) }})
            </h3>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($template->available_variables as $variable)
                <code class="px-2 py-1 text-xs font-mono bg-gray-200 dark:bg-gray-700 rounded">
                    {{ '{{ ' . $variable . ' }}' }}
                </code>
            @endforeach
        </div>
    </div>
    @endif
</div>
