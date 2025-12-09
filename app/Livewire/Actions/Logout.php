<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Logout action - handles logout for both customers and admins
 * 
 * This action logs out from ALL guards (web for admins, customer for customers)
 * to ensure a complete logout regardless of which guard was used.
 */
class Logout
{
    /**
     * Log the current user out of the application.
     * Logs out from both web (admin) and customer guards.
     */
    public function __invoke(): void
    {
        // Logout from customer guard (frontend customers)
        Auth::guard('customer')->logout();

        // Logout from web guard (admin/staff users) 
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
