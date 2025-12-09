<div 
    x-data="{ 
        rawContent: @entangle('data.content_html').live,
        availableVariables: {{ Js::from($getRecord()?->available_variables ?? []) }},
        sampleData: @js($getRecord() ? app(\App\Services\EmailTemplateService::class)->getSampleData($getRecord()) : []),
        
        get content() {
            // Convert TipTap JSON to HTML if needed
            if (this.rawContent && typeof this.rawContent === 'object' && this.rawContent.type === 'doc') {
                return this.tiptapToHtml(this.rawContent);
            }
            return this.rawContent || '';
        },
        
        tiptapToHtml(doc) {
            if (!doc || !doc.content) return '';
            return this.renderNodes(doc.content);
        },
        
        renderNodes(nodes) {
            if (!Array.isArray(nodes)) return '';
            return nodes.map(node => this.renderNode(node)).join('');
        },
        
        renderNode(node) {
            if (!node) return '';
            
            switch (node.type) {
                case 'paragraph':
                    const pAttrs = node.attrs?.textAlign ? ` style=\"text-align: ${node.attrs.textAlign}\"` : '';
                    return `<p${pAttrs}>${this.renderNodes(node.content || [])}</p>`;
                case 'heading':
                    const level = node.attrs?.level || 1;
                    return `<h${level}>${this.renderNodes(node.content || [])}</h${level}>`;
                case 'text':
                    let text = node.text || '';
                    if (node.marks) {
                        node.marks.forEach(mark => {
                            switch (mark.type) {
                                case 'bold': text = `<strong>${text}</strong>`; break;
                                case 'italic': text = `<em>${text}</em>`; break;
                                case 'underline': text = `<u>${text}</u>`; break;
                                case 'strike': text = `<s>${text}</s>`; break;
                                case 'link': text = `<a href=\"${mark.attrs?.href || '#'}\">${text}</a>`; break;
                                case 'textStyle': 
                                    if (mark.attrs?.color) text = `<span style=\"color: ${mark.attrs.color}\">${text}</span>`;
                                    break;
                            }
                        });
                    }
                    return text;
                case 'bulletList':
                    return `<ul>${this.renderNodes(node.content || [])}</ul>`;
                case 'orderedList':
                    return `<ol>${this.renderNodes(node.content || [])}</ol>`;
                case 'listItem':
                    return `<li>${this.renderNodes(node.content || [])}</li>`;
                case 'table':
                    return `<table border=\"1\" cellpadding=\"8\" cellspacing=\"0\" style=\"border-collapse: collapse; width: 100%;\">${this.renderNodes(node.content || [])}</table>`;
                case 'tableRow':
                    return `<tr>${this.renderNodes(node.content || [])}</tr>`;
                case 'tableCell':
                case 'tableHeader':
                    const tag = node.type === 'tableHeader' ? 'th' : 'td';
                    const cellStyle = node.attrs?.textAlign ? ` style=\"text-align: ${node.attrs.textAlign}\"` : '';
                    return `<${tag}${cellStyle}>${this.renderNodes(node.content || [])}</${tag}>`;
                case 'blockquote':
                    return `<blockquote style=\"border-right: 4px solid #ccc; padding-right: 1rem; margin-right: 0;\">${this.renderNodes(node.content || [])}</blockquote>`;
                case 'hardBreak':
                    return '<br>';
                default:
                    return this.renderNodes(node.content || []);
            }
        },
        
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
                💡 <strong>ملاحظة:</strong> المتغيرات (مثل <code class="px-1 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-gray-800 dark:text-gray-200">@{{ order_number }}</code>) 
                يتم استبدالها ببيانات تجريبية للمعاينة فقط.
            </p>
        </div>
    </div>
</div>
