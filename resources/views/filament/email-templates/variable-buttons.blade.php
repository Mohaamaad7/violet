<div 
    x-data="{ 
        variables: {{ Js::from($getRecord()?->available_variables ?? []) }},
        openBrace: String.fromCharCode(123, 123),
        closeBrace: String.fromCharCode(125, 125),
        
        getVariableDisplay(variable) {
            return this.openBrace + ' ' + variable + ' ' + this.closeBrace;
        },
        
        insertVariable(variable) {
            const variableText = this.openBrace + ' ' + variable + ' ' + this.closeBrace;
            
            // Get the editor mode
            const visualMode = document.querySelector('[name=_editor_mode_visual]')?.checked;
            
            if (visualMode) {
                // Insert into RichEditor (TipTap)
                const editorWrapper = document.querySelector('[wire\\:key*=content_html]');
                if (editorWrapper) {
                    const editor = editorWrapper.__tiptap?.editor;
                    if (editor) {
                        editor.chain().focus().insertContent(variableText).run();
                        
                        // Show success notification
                        if (window.FilamentNotification) {
                            new window.FilamentNotification()
                                .title('تم إدراج المتغير')
                                .body(variableText)
                                .success()
                                .send();
                        }
                    }
                }
            } else {
                // Insert into Textarea (HTML mode)
                const textarea = document.querySelector('textarea[wire\\:model\\.live=\"data.content_html\"]');
                if (textarea) {
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = textarea.value;
                    const before = text.substring(0, start);
                    const after = text.substring(end, text.length);
                    
                    textarea.value = before + variableText + after;
                    
                    // Move cursor after inserted text
                    textarea.selectionStart = textarea.selectionEnd = start + variableText.length;
                    
                    // Trigger Livewire update
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                    
                    // Show success notification
                    if (window.FilamentNotification) {
                        new window.FilamentNotification()
                            .title('تم إدراج المتغير')
                            .body(variableText)
                            .success()
                            .send();
                    }
                }
            }
        }
    }"
    class="space-y-3"
>
    <div class="flex flex-wrap gap-2">
        <template x-for="variable in variables" :key="variable">
            <button 
                type="button"
                @click="insertVariable(variable)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-lg text-sm font-medium hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors cursor-pointer border border-primary-200 dark:border-primary-700"
                x-tooltip="@js(__('newsletter.click_to_insert_tooltip'))"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <code class="text-xs" x-text="getVariableDisplay(variable)"></code>
            </button>
        </template>
    </div>
    
    <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
        <p class="flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span>
                <strong>{{ __('newsletter.how_to_use_title') }}</strong> {{ __('newsletter.how_to_use_body_1') }}
                {{ __('newsletter.how_to_use_body_2') }}
            </span>
        </p>
    </div>
</div>