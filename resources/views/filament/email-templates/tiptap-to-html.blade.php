<div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Convert TipTap JSON to HTML when switching from Visual to HTML mode
            const toggle = document.querySelector('[name="_editor_mode_visual"]');
            const richEditorWrapper = document.querySelector('[wire\\:key*="content_html"][style*="display"]');
            
            if (!toggle) return;
            
            toggle.addEventListener('change', function() {
                const isVisualMode = this.checked;
                
                // When switching FROM Visual TO HTML mode
                if (!isVisualMode) {
                    setTimeout(() => {
                        try {
                            // Get TipTap editor instance
                            const editorElement = document.querySelector('.tiptap.ProseMirror');
                            if (!editorElement) return;
                            
                            // Extract HTML from TipTap editor
                            const htmlContent = editorElement.innerHTML;
                            
                            // Find textarea and update it
                            const textarea = document.querySelector('textarea[wire\\:model*="content_html"]');
                            if (textarea && htmlContent) {
                                // Clean up the HTML (remove data-pm attributes)
                                const cleanHtml = htmlContent
                                    .replace(/\s*data-pm-[^=]*="[^"]*"/g, '')
                                    .replace(/\s*class="[^"]*"/g, '')
                                    .trim();
                                
                                textarea.value = cleanHtml;
                                
                                // Trigger Livewire update
                                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        } catch (error) {
                            console.error('Error converting TipTap to HTML:', error);
                        }
                    }, 100);
                }
                
                // When switching FROM HTML TO Visual mode
                if (isVisualMode) {
                    setTimeout(() => {
                        try {
                            const textarea = document.querySelector('textarea[wire\\:model*="content_html"]');
                            const editorElement = document.querySelector('.tiptap.ProseMirror');
                            
                            if (textarea && editorElement && textarea.value) {
                                // Set HTML content to TipTap editor
                                editorElement.innerHTML = textarea.value;
                                
                                // Trigger change event
                                editorElement.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        } catch (error) {
                            console.error('Error loading HTML to TipTap:', error);
                        }
                    }, 100);
                }
            });
            
            // Before form submit, ensure HTML is saved
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const isVisualMode = toggle?.checked;
                    
                    if (isVisualMode) {
                        try {
                            const editorElement = document.querySelector('.tiptap.ProseMirror');
                            const hiddenInput = document.querySelector('[wire\\:model*="content_html"]');
                            
                            if (editorElement && hiddenInput) {
                                const htmlContent = editorElement.innerHTML
                                    .replace(/\s*data-pm-[^=]*="[^"]*"/g, '')
                                    .replace(/\s*class="[^"]*"/g, '')
                                    .trim();
                                
                                hiddenInput.value = htmlContent;
                            }
                        } catch (error) {
                            console.error('Error saving HTML before submit:', error);
                        }
                    }
                });
            }
        });
    </script>
</div>
