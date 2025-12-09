<?php

namespace App\Livewire\Store\Account;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Profile extends Component
{
    // Profile Information
    public string $name = '';
    public string $email = '';
    public string $phone = '';

    // Password Change
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // UI State
    public bool $showPasswordForm = false;

    /**
     * Get the currently authenticated customer
     */
    private function getCustomer(): ?Customer
    {
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->user();
        }
        return null;
    }

    public function mount(): void
    {
        $customer = $this->getCustomer();

        if (!$customer) {
            redirect()->route('login');
            return;
        }

        $this->name = $customer->name ?? '';
        $this->email = $customer->email ?? '';
        $this->phone = $customer->phone ?? '';
    }

    public function updateProfile(): void
    {
        $customer = $this->getCustomer();

        if (!$customer) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(Customer::class)->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $emailChanged = $customer->email !== $validated['email'];

        $customer->fill($validated);

        if ($emailChanged) {
            $customer->email_verified_at = null;
        }

        $customer->save();

        $this->dispatch('show-toast', message: __('messages.account.profile_updated'), type: 'success');
    }

    public function togglePasswordForm(): void
    {
        $this->showPasswordForm = !$this->showPasswordForm;
        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->resetValidation();
    }

    public function updatePassword(): void
    {
        $customer = $this->getCustomer();

        if (!$customer) {
            return;
        }

        $this->validate([
            'current_password' => ['required', 'string', 'current_password:customer'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        $customer->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->showPasswordForm = false;

        $this->dispatch('show-toast', message: __('messages.account.password_updated'), type: 'success');
    }

    public function render()
    {
        return view('livewire.store.account.profile')
            ->layout('layouts.store', ['title' => __('messages.account.profile')]);
    }
}
