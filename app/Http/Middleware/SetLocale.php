<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['ar', 'en'];
        
        // Priority Logic: User Preference -> Session/Cookie Fallback -> App Default (ar)
        $locale = null;

        // PRIMARY: If user is logged in, use their preference
        if (auth()->check() && !empty(auth()->user()->locale)) {
            $locale = auth()->user()->locale;
        }
        
        // FALLBACK: For guests or users without preference, check session/cookie
        if (!$locale) {
            $locale = session('locale') ?: $request->cookie('locale');
        }

        // DEFAULT: Use app default if no preference found
        if (!$locale) {
            $locale = config('app.locale', 'ar');
        }

        // VALIDATION: Ensure locale is supported
        if (!in_array($locale, $supported, true)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);
        
        // Maintain session for consistency
        if (session('locale') !== $locale) {
            session(['locale' => $locale]);
        }
        
        return $next($request);
    }
}
