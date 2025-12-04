<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * GoogleController - Handles Google OAuth authentication
 * 
 * Schema-Dependent: This controller aligns with Violet's users table schema.
 * Role assignment via assignRole('customer') is the source of truth for permissions.
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
     * 
     * User fields aligned with Violet's users table:
     * - name, email, password (required)
     * - phone, profile_photo_path (optional)
     * - type, status, locale (system fields)
     * - email_verified_at (timestamp)
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists with this email
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if ($user) {
                // Existing user - just log them in
                Auth::login($user, remember: true);
                
                Log::info('Google OAuth: Existing user logged in', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } else {
                // New user - create with Violet's users table schema
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'phone' => null,                    // Optional field
                    'profile_photo_path' => null,       // Optional field
                    'type' => 'customer',               // User type
                    'status' => 'active',               // Account status
                    'locale' => config('app.locale'),   // User language preference
                    'email_verified_at' => now(),       // Google emails are pre-verified
                ]);
                
                // Role assignment is the source of truth for permissions
                $user->assignRole('customer');
                
                Auth::login($user, remember: true);
                
                Log::info('Google OAuth: New user created', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
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
