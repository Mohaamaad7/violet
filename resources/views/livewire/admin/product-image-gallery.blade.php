<div class="space-y-4" x-data="{ 
    editingId: @entangle('editingImageId'),
    dragging: false,
    draggedIndex: null,
    images: @js($images)
}">
    {{-- Upload New Images Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6">
        <div class="flex items-center justify-center">
            <label for="file-upload" class="cursor-pointer">
                <div class="flex flex-col items-center space-y-2">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Click to upload or drag and drop
                    </span>
                    <span class="text-xs text-gray-500">
                        PNG, JPG, GIF up to 5MB
                    </span>
                </div>
                <input 
                    id="file-upload" 
                    type="file" 
                    class="hidden" 
                    wire:model="newImages" 
                    multiple
                    accept="image/*"
                >
            </label>
        </div>
        
        @if ($newImages)
            <div class="mt-4">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="animate-spin h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Uploading...</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Images Grid --}}
    @if (count($images) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4"
             x-sort="$wire.updateOrder($event.detail.ids)"
             x-sort:group="images">
            @foreach ($images as $image)
                <div 
                    class="relative group aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden border-2 transition-all duration-200"
                    :class="editingId === {{ $image['id'] }} ? 'border-primary-500 ring-2 ring-primary-200' : 'border-gray-200 dark:border-gray-600 hover:border-primary-300'"
                    x-sort:item="{{ $image['id'] }}"
                >
                    {{-- Image --}}
                    <img 
                        src="{{ Storage::url($image['image_path']) }}" 
                        alt="Product Image"
                        class="w-full h-full object-cover"
                    >

                    {{-- Primary Badge --}}
                    @if ($image['is_primary'])
                        <div class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded-md text-xs font-bold flex items-center space-x-1 shadow-lg">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span>Primary</span>
                        </div>
                    @endif

                    {{-- Hover Overlay with Edit Icon --}}
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200 flex items-center justify-center">
                        <button 
                            type="button"
                            wire:click="openEdit({{ $image['id'] }})"
                            class="opacity-0 group-hover:opacity-100 bg-white dark:bg-gray-800 rounded-full p-3 shadow-lg transform transition-all duration-200 hover:scale-110"
                            @click="editingId = {{ $image['id'] }}"
                        >
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                    </div>

                    {{-- Drag Handle --}}
                    <div 
                        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 bg-gray-800 bg-opacity-75 rounded p-1 cursor-move"
                        x-sort:handle
                    >
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                    </div>

                    {{-- Edit Controls Panel (Shows when editing) --}}
                    <div 
                        x-show="editingId === {{ $image['id'] }}"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        class="absolute inset-x-0 bottom-0 bg-white dark:bg-gray-800 border-t-2 border-primary-500 p-3 space-y-2"
                        @click.away="editingId = null; $wire.closeEdit()"
                    >
                        {{-- Set as Primary Button --}}
                        @if (!$image['is_primary'])
                            <button 
                                type="button"
                                wire:click="setPrimary({{ $image['id'] }})"
                                class="w-full flex items-center justify-center space-x-2 px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md font-medium transition-colors duration-200"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span>Set as Primary</span>
                            </button>
                        @else
                            <div class="w-full flex items-center justify-center space-x-2 px-3 py-2 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md font-medium">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Primary Image</span>
                            </div>
                        @endif

                        {{-- Delete Button --}}
                        <button 
                            type="button"
                            wire:click="deleteImage({{ $image['id'] }})"
                            wire:confirm="Are you sure you want to delete this image?"
                            class="w-full flex items-center justify-center space-x-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium transition-colors duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Delete Image</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No images</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by uploading images.</p>
        </div>
    @endif
</div>

@script
<script>
    Alpine.directive('sort', (el, { expression }, { evaluate }) => {
        let sortable = Sortable.create(el, {
            animation: 150,
            handle: '[x-sort\\:handle]',
            draggable: '[x-sort\\:item]',
            ghostClass: 'opacity-50',
            onEnd: function (evt) {
                let ids = Array.from(el.querySelectorAll('[x-sort\\:item]')).map(item => {
                    return item.getAttribute('x-sort:item');
                });
                
                evaluate(expression)({ detail: { ids } });
            }
        });
    });
</script>
@endscript
