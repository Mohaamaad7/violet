<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        {{ __('استعادة كلمة المرور') }}
    </div>

    <div class="mb-4 text-xs text-gray-500 text-center">
        {{ __('أدخل كلمة المرور الجديدة أدناه.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('البريد الإلكتروني')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('كلمة المرور')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('تأكيد كلمة المرور')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('استعادة كلمة المرور') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6 text-center text-xs text-gray-500">
        <a href="{{ route('home') }}" class="underline hover:text-gray-900" wire:navigate>
            {{ __('متابعة التسوق') }}
        </a>
    </div>
</x-guest-layout>
