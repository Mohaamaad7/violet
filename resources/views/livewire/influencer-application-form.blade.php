<div class="max-w-3xl mx-auto">
    @if($submitted)
        {{-- Success Message --}}
        <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-green-800 mb-2">{{ __('messages.influencer.success_title') }}</h2>
            <p class="text-green-700">{{ __('messages.influencer.success_message') }}</p>
            <a href="{{ route('home') }}"
                class="inline-block mt-6 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                {{ __('messages.home') }}
            </a>
        </div>
    @elseif($alreadyApplied)
        {{-- Already Applied Message --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-yellow-800 mb-2">{{ __('messages.influencer.already_applied_title') }}</h2>
            <p class="text-yellow-700">{{ __('messages.influencer.already_applied_message') }}</p>
        </div>
    @else
        {{-- Application Form --}}
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.influencer.form_title') }}</h1>
                <p class="text-gray-600 mt-2">{{ __('messages.influencer.form_subtitle') }}</p>
            </div>

            <form wire:submit.prevent="submit" class="space-y-8">
                {{-- Personal Information --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ __('messages.influencer.section_personal') }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.full_name') }}
                                *</label>
                            <input type="text" wire:model="full_name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('full_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.email') }} *</label>
                            <input type="email" wire:model="email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.phone') }} *</label>
                            <input type="tel" wire:model="phone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Social Media Accounts --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg>
                        {{ __('messages.influencer.section_social') }}
                    </h3>
                    <p class="text-gray-500 text-sm mb-4">{{ __('messages.influencer.social_hint') }}</p>

                    {{-- Instagram --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 bg-pink-50 rounded-lg">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instagram URL</label>
                            <input type="url" wire:model="instagram_url" placeholder="https://instagram.com/username"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                            @error('instagram_url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.influencer.followers') }}</label>
                            <input type="number" wire:model="instagram_followers" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        </div>
                    </div>

                    {{-- Facebook --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 bg-blue-50 rounded-lg">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                            <input type="url" wire:model="facebook_url" placeholder="https://facebook.com/pagename"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.influencer.followers') }}</label>
                            <input type="number" wire:model="facebook_followers" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- TikTok --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">TikTok URL</label>
                            <input type="url" wire:model="tiktok_url" placeholder="https://tiktok.com/@username"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.influencer.followers') }}</label>
                            <input type="number" wire:model="tiktok_followers" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                        </div>
                    </div>

                    {{-- YouTube --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 bg-red-50 rounded-lg">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">YouTube URL</label>
                            <input type="url" wire:model="youtube_url" placeholder="https://youtube.com/c/channelname"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.influencer.subscribers') }}</label>
                            <input type="number" wire:model="youtube_followers" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>

                    {{-- Twitter/X --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-sky-50 rounded-lg">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Twitter/X URL</label>
                            <input type="url" wire:model="twitter_url" placeholder="https://twitter.com/username"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.influencer.followers') }}</label>
                            <input type="number" wire:model="twitter_followers" min="0"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>
                </div>

                {{-- Additional Info --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        {{ __('messages.influencer.section_additional') }}
                    </h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.influencer.content_type') }}</label>
                            <select wire:model="content_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">{{ __('messages.influencer.select_content_type') }}</option>
                                <option value="fashion">{{ __('messages.influencer.content_types.fashion') }}</option>
                                <option value="beauty">{{ __('messages.influencer.content_types.beauty') }}</option>
                                <option value="lifestyle">{{ __('messages.influencer.content_types.lifestyle') }}</option>
                                <option value="food">{{ __('messages.influencer.content_types.food') }}</option>
                                <option value="travel">{{ __('messages.influencer.content_types.travel') }}</option>
                                <option value="tech">{{ __('messages.influencer.content_types.tech') }}</option>
                                <option value="fitness">{{ __('messages.influencer.content_types.fitness') }}</option>
                                <option value="other">{{ __('messages.influencer.content_types.other') }}</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.influencer.portfolio') }}</label>
                            <textarea wire:model="portfolio" rows="4"
                                placeholder="{{ __('messages.influencer.portfolio_placeholder') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-4">
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full py-3 px-6 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-primary-200 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>{{ __('messages.influencer.submit') }}</span>
                        <span wire:loading>{{ __('messages.loading') }}</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>