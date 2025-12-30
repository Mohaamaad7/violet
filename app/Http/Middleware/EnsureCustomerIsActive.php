<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure authenticated customer is active (not blocked).
 * If customer is blocked, they will be logged out and redirected to login.
 */
class EnsureCustomerIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();

            if ($customer->status === 'blocked') {
                Auth::guard('customer')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => __('auth.blocked')]);
            }
        }

        return $next($request);
    }
}
