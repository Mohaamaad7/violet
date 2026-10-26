<?php

namespace App\Support;

use Illuminate\Http\Request;
use Spatie\ResponseCache\Hasher\DefaultHasher;

class LocaleAwareResponseHasher extends DefaultHasher
{
    public function getHashFor(Request $request): string
    {
        // SetLocale runs before CacheResponse in the web middleware pipeline.
        // Preserve Spatie's URL, query string, user, and custom suffix isolation.
        return parent::getHashFor($request).'-locale-'.app()->getLocale();
    }
}
