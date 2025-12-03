<div class="bg-cream-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Back Link --}}
        <a href="{{ route('account.dashboard') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-violet-600 mb-6">
            <svg class="w-4 h-4 me-1 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            {{ __('messages.account.back_to_dashboard') }}
        </a>
        
        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.account.addresses') }}</h1>
                <p class="mt-2 text-gray-600">{{ __('messages.account.addresses_subtitle') }}</p>
            </div>
            @if(!$showForm)
                <button 
                    wire:click="openForm"
                    class="inline-flex items-center px-4 py-2 bg-violet-600 text-white font-semibold rounded-lg hover:bg-violet-700 transition-colors"
                >
                    <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('messages.account.add_address') }}
                </button>
            @endif
        </div>
        
        {{-- Address Form Modal --}}
        @if($showForm)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $editingAddressId ? __('messages.account.edit_address') : __('messages.account.add_address') }}
                    </h2>
                    <button wire:click="closeForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form wire:submit="save" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Full Name --}}
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.full_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="full_name" 
                                wire:model="full_name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                                required
                            >
                            @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.phone') }} <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                wire:model="phone"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                                dir="ltr"
                                required
                            >
                            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Governorate --}}
                        <div>
                            <label for="governorate" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.governorate') }} <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="governorate" 
                                wire:model="governorate"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                                required
                            >
                                <option value="">{{ __('messages.account.select_governorate') }}</option>
                                @foreach($governorates as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('governorate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- City --}}
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.city') }} <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="city" 
                                wire:model="city"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                                required
                            >
                            @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Area --}}
                        <div>
                            <label for="area" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.area') }}
                            </label>
                            <input 
                                type="text" 
                                id="area" 
                                wire:model="area"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                            >
                            @error('area') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Street Address --}}
                        <div class="md:col-span-2">
                            <label for="street_address" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.street_address') }} <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="street_address" 
                                wire:model="street_address"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                                placeholder="{{ __('messages.account.street_address_placeholder') }}"
                                required
                            >
                            @error('street_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Building Number --}}
                        <div>
                            <label for="building_number" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.building_number') }}
                            </label>
                            <input 
                                type="text" 
                                id="building_number" 
                                wire:model="building_number"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                            >
                            @error('building_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Floor --}}
                        <div>
                            <label for="floor" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.floor') }}
                            </label>
                            <input 
                                type="text" 
                                id="floor" 
                                wire:model="floor"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                            >
                            @error('floor') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Apartment --}}
                        <div>
                            <label for="apartment" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.apartment') }}
                            </label>
                            <input 
                                type="text" 
                                id="apartment" 
                                wire:model="apartment"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                            >
                            @error('apartment') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Landmark --}}
                        <div>
                            <label for="landmark" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('messages.account.landmark') }}
                            </label>
                            <input 
                                type="text" 
                                id="landmark" 
                                wire:model="landmark"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                                placeholder="{{ __('messages.account.landmark_placeholder') }}"
                            >
                            @error('landmark') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        {{-- Default Address --}}
                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="is_default"
                                    class="w-4 h-4 text-violet-600 border-gray-300 rounded focus:ring-violet-500"
                                >
                                <span class="ms-2 text-sm text-gray-700">{{ __('messages.account.set_as_default') }}</span>
                            </label>
                        </div>
                    </div>
                    
                    {{-- Form Buttons --}}
                    <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                        <button 
                            type="button"
                            wire:click="closeForm"
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
                            <span wire:loading.remove wire:target="save">
                                {{ $editingAddressId ? __('messages.account.update_address') : __('messages.account.save_address') }}
                            </span>
                            <span wire:loading wire:target="save">{{ __('messages.loading') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
        
        {{-- Address List --}}
        @if($addresses->isEmpty() && !$showForm)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.account.no_addresses') }}</h3>
                <p class="mt-2 text-gray-500">{{ __('messages.account.no_addresses_desc') }}</p>
                <button 
                    wire:click="openForm"
                    class="mt-4 inline-flex items-center px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors"
                >
                    <svg class="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('messages.account.add_first_address') }}
                </button>
            </div>
        @else
            <div class="space-y-4">
                @foreach($addresses as $address)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 {{ $address->is_default ? 'ring-2 ring-violet-500' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-semibold text-gray-900">{{ $address->full_name }}</h3>
                                    @if($address->is_default)
                                        <span class="px-2 py-0.5 text-xs font-medium bg-violet-100 text-violet-800 rounded-full">
                                            {{ __('messages.account.default') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-gray-600 text-sm mb-1" dir="ltr">{{ $address->phone }}</p>
                                <p class="text-gray-600 text-sm">{{ $address->full_address }}</p>
                                @if($address->landmark)
                                    <p class="text-gray-500 text-sm mt-1">
                                        <span class="font-medium">{{ __('messages.account.landmark') }}:</span> {{ $address->landmark }}
                                    </p>
                                @endif
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @if(!$address->is_default)
                                    <button 
                                        wire:click="setDefault({{ $address->id }})"
                                        class="p-2 text-gray-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors"
                                        title="{{ __('messages.account.set_as_default') }}"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                    </button>
                                @endif
                                <button 
                                    wire:click="openForm({{ $address->id }})"
                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="{{ __('messages.account.edit') }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button 
                                    wire:click="confirmDelete({{ $address->id }})"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="{{ __('messages.account.delete') }}"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Pagination --}}
            <div class="mt-6">
                {{ $addresses->links() }}
            </div>
        @endif
        
        {{-- Delete Confirmation Modal --}}
        @if($confirmingDelete)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>
                    
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                    
                    <div class="inline-block align-bottom bg-white rounded-lg text-start overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ms-4 sm:text-start">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        {{ __('messages.account.delete_address') }}
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            {{ __('messages.account.delete_address_confirm') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                            <button 
                                wire:click="delete"
                                type="button" 
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm"
                            >
                                {{ __('messages.account.delete') }}
                            </button>
                            <button 
                                wire:click="cancelDelete"
                                type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 sm:mt-0 sm:w-auto sm:text-sm"
                            >
                                {{ __('messages.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
