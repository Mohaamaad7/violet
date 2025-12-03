<div class="space-y-8">
    {{-- Review Stats Summary --}}
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            {{-- Average Rating --}}
            <div class="flex-shrink-0 text-center">
                <div class="text-5xl font-bold text-gray-900">{{ $this->stats['average_rating'] }}</div>
                <div class="flex justify-center mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <x-heroicon-s-star class="w-5 h-5 {{ $i <= round($this->stats['average_rating']) ? 'text-yellow-400' : 'text-gray-300' }}" />
                    @endfor
                </div>
                <div class="text-sm text-gray-500 mt-1">
                    {{ $this->stats['total_count'] }} {{ __('messages.reviews.reviews_count') }}
                </div>
            </div>

            {{-- Rating Distribution --}}
            <div class="flex-1 space-y-2">
                @foreach($this->stats['distribution'] as $stars => $data)
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600 w-16">{{ $stars }} {{ __('messages.reviews.stars') }}</span>
                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-yellow-400 rounded-full transition-all duration-300"
                                style="width: {{ $data['percentage'] }}%"
                            ></div>
                        </div>
                        <span class="text-sm text-gray-500 w-12 text-right">{{ $data['count'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Write Review - Interactive Star Selector --}}
            <div class="flex-shrink-0">
                @auth
                    @if($this->canReview)
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-2">
                                {{ $this->hasReviewed ? __('messages.reviews.tap_to_edit') : __('messages.reviews.tap_to_rate') }}
                            </p>
                            {{-- Interactive Star Rating CTA --}}
                            <div class="flex justify-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <button
                                        type="button"
                                        wire:click="selectRating({{ $i }})"
                                        class="group focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 rounded transition-transform hover:scale-110"
                                        title="{{ $i }} {{ __('messages.reviews.stars') }}"
                                    >
                                        <x-heroicon-s-star class="w-10 h-10 text-gray-300 group-hover:text-yellow-400 transition-colors cursor-pointer" />
                                    </button>
                                @endfor
                            </div>
                            
                            @if($this->hasReviewed)
                                <div class="mt-3 flex items-center justify-center gap-3">
                                    <span class="text-xs text-green-600 flex items-center gap-1">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                        {{ __('messages.reviews.your_review_posted') }}
                                    </span>
                                    <button
                                        wire:click="deleteReview"
                                        wire:confirm="{{ __('messages.reviews.delete_confirm') }}"
                                        class="text-xs text-red-600 hover:text-red-800 hover:underline"
                                    >
                                        {{ __('messages.reviews.delete_review') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center">
                            {{ __('messages.reviews.purchase_required') }}
                        </p>
                    @endif
                @else
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-2">{{ __('messages.reviews.tap_to_rate') }}</p>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-violet-600 hover:text-violet-800 font-medium">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                            {{ __('messages.reviews.login_to_review') }}
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 ltr:mr-2 rtl:ml-2" />
                <span class="text-green-800">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-500 ltr:mr-2 rtl:ml-2" />
                <span class="text-red-800">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Review Form Modal --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Background overlay --}}
            <div wire:click="closeForm" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            {{-- Modal container with flex centering --}}
            <div class="fixed inset-0 flex items-center justify-center p-4">
                {{-- Modal panel --}}
                <div class="relative w-full max-w-lg overflow-hidden bg-white rounded-xl shadow-xl">
                    <div class="px-6 py-4 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $isEditing ? __('messages.reviews.edit_review') : __('messages.reviews.write_review') }}
                            </h3>
                            <button wire:click="closeForm" class="text-gray-400 hover:text-gray-600">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    <form wire:submit="submit" class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
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
                                wire:click="closeForm"
                                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                            >
                                {{ __('messages.reviews.cancel') }}
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="submit">
                                    {{ $isEditing ? __('messages.reviews.update') : __('messages.reviews.submit') }}
                                </span>
                                <span wire:loading wire:target="submit">
                                    {{ __('messages.reviews.submitting') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Sort & Filter --}}
    @if($this->stats['total_count'] > 0)
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.reviews.customer_reviews') }}</h3>
            <select
                wire:model.live="sortBy"
                class="rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"
            >
                <option value="latest">{{ __('messages.reviews.sort.latest') }}</option>
                <option value="oldest">{{ __('messages.reviews.sort.oldest') }}</option>
                <option value="highest">{{ __('messages.reviews.sort.highest') }}</option>
                <option value="lowest">{{ __('messages.reviews.sort.lowest') }}</option>
                <option value="helpful">{{ __('messages.reviews.sort.helpful') }}</option>
            </select>
        </div>
    @endif

    {{-- Reviews List --}}
    <div class="space-y-6">
        @forelse($reviews as $review)
            <div class="bg-white rounded-xl shadow-sm border p-6 {{ !$review->is_approved ? 'border-yellow-300 bg-yellow-50' : '' }}">
                <div class="flex items-start gap-4">
                    {{-- User Avatar --}}
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-violet-100 rounded-full flex items-center justify-center">
                            <span class="text-violet-600 font-semibold text-lg">
                                {{ substr($review->user->name ?? 'U', 0, 1) }}
                            </span>
                        </div>
                    </div>

                    {{-- Review Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $review->user->name ?? __('messages.reviews.anonymous') }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    {{-- Stars --}}
                                    <div class="flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            <x-heroicon-s-star class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" />
                                        @endfor
                                    </div>
                                    {{-- Verified Badge --}}
                                    @if($review->is_verified_purchase)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <x-heroicon-o-check-badge class="w-3 h-3 ltr:mr-1 rtl:ml-1" />
                                            {{ __('messages.reviews.verified') }}
                                        </span>
                                    @endif
                                    {{-- Pending Badge --}}
                                    @if(!$review->is_approved)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <x-heroicon-o-clock class="w-3 h-3 ltr:mr-1 rtl:ml-1" />
                                            {{ __('messages.reviews.pending') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                        </div>

                        @if($review->title)
                            <h5 class="font-medium text-gray-900 mt-3">{{ $review->title }}</h5>
                        @endif

                        @if($review->comment)
                            <p class="text-gray-600 mt-2">{{ $review->comment }}</p>
                        @endif

                        {{-- Helpful Button --}}
                        @if($review->is_approved)
                            <div class="flex items-center gap-4 mt-4">
                                <button
                                    wire:click="markHelpful({{ $review->id }})"
                                    class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                                >
                                    <x-heroicon-o-hand-thumb-up class="w-4 h-4 ltr:mr-1 rtl:ml-1" />
                                    {{ __('messages.reviews.helpful') }} ({{ $review->helpful_count }})
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <x-heroicon-o-chat-bubble-bottom-center-text class="w-16 h-16 text-gray-300 mx-auto" />
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.reviews.no_reviews') }}</h3>
                <p class="mt-2 text-gray-500">{{ __('messages.reviews.be_first') }}</p>
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
