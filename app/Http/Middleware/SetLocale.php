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

        // Priority: user -> cookie -> session -> header -> app default
        $locale = null;

        if (auth()->check() && isset(auth()->user()->locale)) {
            $locale = auth()->user()->locale;
        }

        if (!$locale) {
            $locale = $request->cookie('locale');
        }

        if (!$locale) {
            $locale = session('locale');
        }

        if (!$locale) {
            $locale = $request->getPreferredLanguage($supported);
        }

        if (!$locale) {
            $locale = config('app.locale', 'ar');
        }

        if (!in_array($locale, $supported, true)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);
        session(['locale' => $locale]);
        
        return $next($request);
    }
}
