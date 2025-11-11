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
        
        // Redirect back
        return redirect()->back();
    }
}
