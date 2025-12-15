<div class="bg-white rounded-2xl shadow-lg p-8 lg:p-12">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-1 h-12 bg-gradient-to-b from-violet-600 to-purple-600 rounded-full"></div>
        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">
            {{ __('contact.contact_form.title') }}
        </h2>
    </div>

    <form wire:submit="submit" class="space-y-6">
        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                {{ __('contact.contact_form.name') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" wire:model="name"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all duration-200 @error('name') border-red-500 @enderror"
                placeholder="{{ __('contact.contact_form.name_placeholder') }}">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                {{ __('contact.contact_form.email') }} <span class="text-red-500">*</span>
            </label>
            <input type="email" id="email" wire:model="email"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all duration-200 @error('email') border-red-500 @enderror"
                placeholder="{{ __('contact.contact_form.email_placeholder') }}">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                {{ __('contact.contact_form.phone') }} <span class="text-red-500">*</span>
            </label>
            <input type="tel" id="phone" wire:model="phone"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all duration-200 @error('phone') border-red-500 @enderror"
                placeholder="{{ __('contact.contact_form.phone_placeholder') }}" dir="ltr">
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Subject --}}
        <div>
            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">
                {{ __('contact.contact_form.subject') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="subject" wire:model="subject"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all duration-200 @error('subject') border-red-500 @enderror"
                placeholder="{{ __('contact.contact_form.subject_placeholder') }}">
            @error('subject')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Message --}}
        <div>
            <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
                {{ __('contact.contact_form.message') }} <span class="text-red-500">*</span>
            </label>
            <textarea id="message" wire:model="message" rows="6"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-violet-500 focus:ring-2 focus:ring-violet-200 transition-all duration-200 resize-none @error('message') border-red-500 @enderror"
                placeholder="{{ __('contact.contact_form.message_placeholder') }}"></textarea>
            @error('message')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit Button --}}
        <button type="submit" wire:loading.attr="disabled"
            class="w-full bg-gradient-to-r from-violet-600 to-purple-700 text-white font-bold py-4 px-8 rounded-full hover:from-violet-700 hover:to-purple-800 transition-all duration-300 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <span wire:loading.remove>{{ __('contact.contact_form.submit') }}</span>
            <span wire:loading>
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                {{ __('contact.contact_form.sending') }}
            </span>
        </button>
    </form>
</div>