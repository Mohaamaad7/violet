<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        // Validate locale
        if (!in_array($locale, ['ar', 'en'])) {
            abort(404);
        }

        // Set locale in session
        session(['locale' => $locale]);

        // Update user preference if logged in (for any guard)
        foreach (['web', 'customer'] as $guard) {
            if (auth($guard)->check()) {
                $user = auth($guard)->user();
                if ($user) {
                    $user->locale = $locale;
                    $user->save();
                }
            }
        }

        // Redirect back
        return redirect()->back();
    }
}
