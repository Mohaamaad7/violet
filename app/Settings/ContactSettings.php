<?php

namespace Spatie\LaravelSettings\SettingsCasts {
    if (!class_exists(ArrayCast::class)) {
        class ArrayCast implements SettingsCast
        {
            public function get($payload)
            {
                if (is_string($payload)) {
                    return json_decode($payload, true) ?: [];
                }
                return is_array($payload) ? $payload : [];
            }

            public function set($payload)
            {
                return $payload;
            }
        }
    }
}

namespace App\Settings {

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
}
