<?php

use App\Livewire\Forms\LoginForm;
use App\Models\Customer;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     * Cart merge is handled automatically by MergeCartOnLogin listener.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Customer login always goes to home page
        // Note: Admin users should use /admin login page (Filament)
        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    }
}; ?>

<div class="p-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.login') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('auth.welcome_back') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('messages.email')" class="text-sm font-medium text-gray-700" />
            <x-text-input wire:model="form.email" id="email"
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-violet-500 focus:border-violet-500 transition-colors"
                type="email" name="email" required autofocus autocomplete="username"
                placeholder="{{ __('auth.email_placeholder') }}" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('auth.password')" class="text-sm font-medium text-gray-700" />
            <x-text-input wire:model="form.password" id="password"
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-violet-500 focus:border-violet-500 transition-colors"
                type="password" name="password" required autocomplete="current-password"
                placeholder="{{ __('auth.password_placeholder') }}" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center cursor-pointer">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-violet-600 shadow-sm focus:ring-violet-500"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('auth.remember_me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-violet-600 hover:text-violet-800 font-medium" href="{{ route('password.request') }}"
                    wire:navigate>
                    {{ __('auth.forgot_password') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 transition-colors"
            wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-wait">
            <span wire:loading.remove>{{ __('messages.login') }}</span>
            <span wire:loading class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                {{ __('auth.signing_in') }}
            </span>
        </button>
    </form>

    <!-- Register Link -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            {{ __('auth.no_account') }}
            <a href="{{ route('register') }}" class="text-violet-600 hover:text-violet-800 font-medium" wire:navigate>
                {{ __('messages.register') }}
            </a>
        </p>
    </div>
</div>