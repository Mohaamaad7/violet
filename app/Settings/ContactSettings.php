<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsCasts\ArrayCast;

class ContactSettings extends Settings
{
    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $working_hours = '';

    public bool $show_map = false;

    public array $social_links = [];

    public static function group(): string
    {
        return 'contact';
    }

    /**
     * Explicit casts for array properties.
     * Required by Spatie to properly serialize/deserialize JSON arrays.
     */
    public static function casts(): array
    {
        return [
            'social_links' => ArrayCast::class,
        ];
    }
}
