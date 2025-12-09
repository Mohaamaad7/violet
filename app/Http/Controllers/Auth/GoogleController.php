<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * GoogleController - Handles Google OAuth authentication for Customers
 * 
 * This controller creates/logins Customers (NOT Users) via Google OAuth.
 * Admin/Staff users should use Filament admin panel login.
 * 
 * Routes:
 *   GET /auth/google          - Redirect to Google
 *   GET /auth/google/callback - Handle Google callback
 * 
 * @see https://laravel.com/docs/11.x/socialite
 */
class GoogleController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     * Creates a Customer (not User) with the Google account info.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if customer already exists with this email
            $customer = Customer::where('email', $googleUser->getEmail())->first();

            // Get guest session ID before login (for cart merge)
            $guestSessionId = Cookie::get('cart_session_id');

            if ($customer) {
                // Existing customer - just log them in
                Auth::guard('customer')->login($customer, remember: true);

                Log::info('Google OAuth: Existing customer logged in', [
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                ]);
            } else {
                // New customer - create with Customer model
                $customer = Customer::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'phone' => null,
                    'status' => 'active',
                    'email_verified_at' => now(), // Google emails are pre-verified
                ]);

                Auth::guard('customer')->login($customer, remember: true);

                Log::info('Google OAuth: New customer created', [
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                ]);
            }

            // Merge guest cart if exists
            if ($guestSessionId) {
                try {
                    $cartService = app(CartService::class);
                    $cartService->mergeGuestCart($guestSessionId, $customer->id);
                } catch (\Exception $e) {
                    Log::warning('Google OAuth: Cart merge failed', ['error' => $e->getMessage()]);
                }
            }

            // Redirect to intended URL or home
            return redirect()->intended('/');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning('Google OAuth: Invalid state', ['error' => $e->getMessage()]);

            return redirect('/login')
                ->with('error', __('auth.google_session_expired'));

        } catch (\Exception $e) {
            Log::error('Google OAuth: Authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/login')
                ->with('error', __('auth.google_login_failed'));
        }
    }
}
