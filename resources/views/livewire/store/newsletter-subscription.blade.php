<div class="w-full">
    {{-- Success/Error Message --}}
    @if($message)
        <div
            class="mb-3 p-3 rounded-lg text-sm {{ $messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}"
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            x-transition
        >
            <div class="flex items-center gap-2">
                @if($messageType === 'success')
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                @endif
                <span>{{ $message }}</span>
            </div>
        </div>
    @endif

    {{-- Newsletter Form --}}
    <form wire:submit.prevent="subscribe" class="flex gap-2">
        @csrf

        <div class="flex-1">
            <input
                type="email"
                wire:model="email"
                placeholder="{{ trans_db('store.footer.your_email') }}"
                class="w-full px-3 py-2 bg-gray-800 border rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none transition
                    {{ $errors->has('email') ? 'border-red-500 focus:border-red-500 focus:ring-1 focus:ring-red-500' : 'border-gray-700 focus:border-violet-500 focus:ring-1 focus:ring-violet-500' }}"
                {{ $loading ? 'disabled' : '' }}
            >

            @error('email')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            {{ $loading ? 'disabled' : '' }}
        >
            @if($loading)
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            @endif
            <span>{{ trans_db('store.footer.subscribe_button') }}</span>
        </button>
    </form>

    {{-- Privacy Note (Optional) --}}
    <p class="mt-2 text-xs text-gray-500">
        {{ __('newsletter.privacy_note_prefix') }}
        <a href="/page/privacy-policy" class="text-violet-400 hover:text-violet-300 underline">{{ __('newsletter.privacy_policy') }}</a>
    </p>
</div>

