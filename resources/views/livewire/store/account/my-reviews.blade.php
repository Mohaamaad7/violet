<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('account.dashboard') }}" class="hover:text-violet-600">{{ __('messages.account.dashboard') }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">{{ __('messages.account.my_reviews') }}</span>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.account.my_reviews') }}</h1>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 ltr:mr-2 rtl:ml-2" />
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-500 ltr:mr-2 rtl:ml-2" />
                    <span class="text-red-800">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Reviews List --}}
        <div class="space-y-6">
            @forelse($reviews as $review)
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            {{-- Product Image --}}
                            @if($review->product?->getFirstMediaUrl('images', 'thumb'))
                                <img 
                                    src="{{ $review->product->getFirstMediaUrl('images', 'thumb') }}" 
                                    alt="{{ $review->product->name }}"
                                    class="w-20 h-20 object-cover rounded-lg"
                                />
                            @else
                                <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-photo class="w-8 h-8 text-gray-400" />
                                </div>
                            @endif

                            {{-- Review Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <a href="{{ route('product.show', $review->product?->slug ?? '#') }}" class="font-semibold text-gray-900 hover:text-violet-600">
                                            {{ $review->product?->name ?? __('messages.reviews.product_deleted') }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            {{-- Stars --}}
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <x-heroicon-s-star class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" />
                                                @endfor
                                            </div>
                                            {{-- Status --}}
                                            @if($review->is_approved)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('messages.reviews.approved') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ __('messages.reviews.pending') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $review->created_at->format('Y-m-d') }}</span>
                                </div>

                                @if($review->title)
                                    <h4 class="font-medium text-gray-900 mt-3">{{ $review->title }}</h4>
                                @endif

                                @if($review->comment)
                                    <p class="text-gray-600 mt-2">{{ $review->comment }}</p>
                                @endif

                                {{-- Actions --}}
                                <div class="flex items-center gap-4 mt-4">
                                    <button
                                        wire:click="editReview({{ $review->id }})"
                                        class="inline-flex items-center text-sm text-violet-600 hover:text-violet-800"
                                    >
                                        <x-heroicon-o-pencil class="w-4 h-4 ltr:mr-1 rtl:ml-1" />
                                        {{ __('messages.reviews.edit') }}
                                    </button>
                                    <button
                                        wire:click="deleteReview({{ $review->id }})"
                                        wire:confirm="{{ __('messages.reviews.delete_confirm') }}"
                                        class="inline-flex items-center text-sm text-red-600 hover:text-red-800"
                                    >
                                        <x-heroicon-o-trash class="w-4 h-4 ltr:mr-1 rtl:ml-1" />
                                        {{ __('messages.reviews.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Helpful Count --}}
                    @if($review->helpful_count > 0)
                        <div class="px-6 py-3 bg-gray-50 border-t">
                            <span class="text-sm text-gray-500">
                                <x-heroicon-o-hand-thumb-up class="w-4 h-4 inline ltr:mr-1 rtl:ml-1" />
                                {{ $review->helpful_count }} {{ __('messages.reviews.people_found_helpful') }}
                            </span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
                    <x-heroicon-o-chat-bubble-bottom-center-text class="w-16 h-16 text-gray-300 mx-auto" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.reviews.no_reviews_yet') }}</h3>
                    <p class="mt-2 text-gray-500">{{ __('messages.reviews.no_reviews_desc') }}</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition">
                        {{ __('messages.reviews.browse_products') }}
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($reviews->hasPages())
            <div class="mt-6">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div wire:click="closeModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                {{-- Modal panel --}}
                <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-xl shadow-xl">
                    <div class="px-6 py-4 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ __('messages.reviews.edit_review') }}
                            </h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    <form wire:submit="updateReview" class="p-6 space-y-6">
                        {{-- Star Rating --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.reviews.your_rating') }}
                            </label>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <button
                                        type="button"
                                        wire:click="setRating({{ $i }})"
                                        class="focus:outline-none"
                                    >
                                        <x-heroicon-s-star class="w-8 h-8 {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400 transition" />
                                    </button>
                                @endfor
                            </div>
                            @error('rating')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Title --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.reviews.review_title') }}
                            </label>
                            <input
                                wire:model="title"
                                type="text"
                                id="title"
                                placeholder="{{ __('messages.reviews.title_placeholder') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500"
                            />
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Comment --}}
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.reviews.review_comment') }}
                            </label>
                            <textarea
                                wire:model="comment"
                                id="comment"
                                rows="4"
                                placeholder="{{ __('messages.reviews.comment_placeholder') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500"
                            ></textarea>
                            @error('comment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Moderation Notice --}}
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <x-heroicon-o-information-circle class="w-5 h-5 text-yellow-500 ltr:mr-2 rtl:ml-2 flex-shrink-0 mt-0.5" />
                                <p class="text-sm text-yellow-700">
                                    {{ __('messages.reviews.moderation_notice') }}
                                </p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                wire:click="closeModal"
                                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                            >
                                {{ __('messages.reviews.cancel') }}
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="updateReview">
                                    {{ __('messages.reviews.update') }}
                                </span>
                                <span wire:loading wire:target="updateReview">
                                    {{ __('messages.reviews.updating') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
