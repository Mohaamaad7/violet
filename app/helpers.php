<?php

use App\Services\TranslationService;

if (!function_exists('trans_db')) {
    /**
     * Get translation from DB-backed system (with file fallback).
     *
     * @param string $key
     * @param string|null $locale
     * @return string
     */
    function trans_db(string $key, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return app(TranslationService::class)->get($key, $locale);
    }
}

if (!function_exists('set_trans')) {
    /**
     * Set a translation in the database.
     *
     * @param string $key
     * @param string $locale
     * @param string $value
     * @param string|null $group
     * @param bool $active
     * @return \App\Models\Translation
     */
    function set_trans(string $key, string $locale, string $value, ?string $group = null, bool $active = true): \App\Models\Translation
    {
        $userId = optional(auth()->user())->id;
        return app(TranslationService::class)->set($key, $locale, $value, $group, $active, $userId);
    }
}

if (!function_exists('setting')) {
    /**
     * Get a setting value from database.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (!function_exists('setting_set')) {
    /**
     * Set a setting value in database.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @return \App\Models\Setting
     */
    function setting_set(string $key, $value, string $type = 'string', string $group = 'general'): \App\Models\Setting
    {
        return \App\Models\Setting::set($key, $value, $type, $group);
    }
}
