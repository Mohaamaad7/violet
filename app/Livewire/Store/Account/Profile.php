<?php

namespace App\Livewire\Store\Account;

use App\Models\User;
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
    
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
    }
    
    public function updateProfile(): void
    {
        $user = Auth::user();
        
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
        
        $emailChanged = $user->email !== $validated['email'];
        
        $user->fill($validated);
        
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        
        $user->save();
        
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
        $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);
        
        $user = Auth::user();
        $user->update([
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
