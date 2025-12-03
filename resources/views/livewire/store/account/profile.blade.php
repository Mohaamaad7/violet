<div class="bg-cream-50 min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Back Link --}}
        <a href="{{ route('account.dashboard') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-violet-600 mb-6">
            <svg class="w-4 h-4 me-1 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            {{ __('messages.account.back_to_dashboard') }}
        </a>
        
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.account.profile') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('messages.account.profile_subtitle') }}</p>
        </div>
        
        {{-- Profile Information Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.account.profile_info') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('messages.account.profile_info_desc') }}</p>
            </div>
            
            <form wire:submit="updateProfile" class="p-6 space-y-6">
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.account.name') }}
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        wire:model="name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                        required
                    >
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.account.email') }}
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        wire:model="email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                        required
                    >
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('messages.account.phone') }}
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        wire:model="phone"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                        dir="ltr"
                    >
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                {{-- Save Button --}}
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-violet-600 text-white font-semibold rounded-lg hover:bg-violet-700 focus:ring-4 focus:ring-violet-200 transition-colors"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-wait"
                    >
                        <span wire:loading.remove wire:target="updateProfile">{{ __('messages.save') }}</span>
                        <span wire:loading wire:target="updateProfile">{{ __('messages.loading') }}</span>
                    </button>
                </div>
            </form>
        </div>
        
        {{-- Password Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.account.change_password') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('messages.account.change_password_desc') }}</p>
                </div>
                @if(!$showPasswordForm)
                    <button 
                        wire:click="togglePasswordForm"
                        class="px-4 py-2 text-sm font-medium text-violet-600 border border-violet-600 rounded-lg hover:bg-violet-50 transition-colors"
                    >
                        {{ __('messages.account.change_password_btn') }}
                    </button>
                @endif
            </div>
            
            @if($showPasswordForm)
                <form wire:submit="updatePassword" class="p-6 space-y-6">
                    {{-- Current Password --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.account.current_password') }}
                        </label>
                        <input 
                            type="password" 
                            id="current_password" 
                            wire:model="current_password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                            required
                        >
                        @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- New Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.account.new_password') }}
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            wire:model="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                            required
                        >
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.account.confirm_password') }}
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            wire:model="password_confirmation"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                            required
                        >
                    </div>
                    
                    {{-- Buttons --}}
                    <div class="flex items-center justify-end gap-4">
                        <button 
                            type="button"
                            wire:click="togglePasswordForm"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                        >
                            {{ __('messages.cancel') }}
                        </button>
                        <button 
                            type="submit" 
                            class="px-6 py-3 bg-violet-600 text-white font-semibold rounded-lg hover:bg-violet-700 focus:ring-4 focus:ring-violet-200 transition-colors"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-wait"
                        >
                            <span wire:loading.remove wire:target="updatePassword">{{ __('messages.account.update_password') }}</span>
                            <span wire:loading wire:target="updatePassword">{{ __('messages.loading') }}</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
