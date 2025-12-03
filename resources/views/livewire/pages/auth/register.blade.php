<?php

use App\Models\User;
use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     * Includes cart merge for guest carts.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['type'] = 'customer'; // Default to customer type
        $validated['status'] = 'active';

        // Create user
        $user = User::create($validated);
        
        // Assign default customer role if spatie permissions is available
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('customer');
        }

        event(new Registered($user));

        // Get guest session ID before login (for cart merge)
        $guestSessionId = Cookie::get('cart_session_id');

        Auth::login($user);

        // Merge guest cart if exists
        if ($guestSessionId) {
            $cartService = app(CartService::class);
            $cartService->mergeGuestCart($guestSessionId, $user->id);
        }

        $this->redirect(route('home', absolute: false), navigate: true);
    }
}; ?>

<div class="p-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.register') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('auth.create_account') }}</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('messages.full_name')" class="text-sm font-medium text-gray-700" />
            <x-text-input 
                wire:model="name" 
                id="name" 
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-violet-500 focus:border-violet-500 transition-colors" 
                type="text" 
                name="name" 
                required 
                autofocus 
                autocomplete="name"
                placeholder="{{ __('auth.name_placeholder') }}" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('messages.email')" class="text-sm font-medium text-gray-700" />
            <x-text-input 
                wire:model="email" 
                id="email" 
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-violet-500 focus:border-violet-500 transition-colors" 
                type="email" 
                name="email" 
                required 
                autocomplete="username"
                placeholder="{{ __('auth.email_placeholder') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone (Optional) -->
        <div>
            <x-input-label for="phone" :value="__('messages.phone')" class="text-sm font-medium text-gray-700" />
            <x-text-input 
                wire:model="phone" 
                id="phone" 
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-violet-500 focus:border-violet-500 transition-colors" 
                type="tel" 
                name="phone" 
                autocomplete="tel"
                placeholder="{{ __('auth.phone_placeholder') }}" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('auth.password')" class="text-sm font-medium text-gray-700" />
            <x-text-input 
                wire:model="password" 
                id="password" 
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-violet-500 focus:border-violet-500 transition-colors"
                type="password"
                name="password"
                required 
                autocomplete="new-password"
                placeholder="{{ __('auth.password_placeholder') }}" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" class="text-sm font-medium text-gray-700" />
            <x-text-input 
                wire:model="password_confirmation" 
                id="password_confirmation" 
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-violet-500 focus:border-violet-500 transition-colors"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password"
                placeholder="{{ __('auth.confirm_password_placeholder') }}" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 transition-colors"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75 cursor-wait">
            <span wire:loading.remove>{{ __('messages.register') }}</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('auth.creating_account') }}
            </span>
        </button>
    </form>

    <!-- Login Link -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            {{ __('auth.already_have_account') }}
            <a href="{{ route('login') }}" class="text-violet-600 hover:text-violet-800 font-medium" wire:navigate>
                {{ __('messages.login') }}
            </a>
        </p>
    </div>
</div>
