<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    // Removed 'value' cast - let accessor/mutator handle type conversion


    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
    }

    public function getValueAttribute($value)
    {
        // Handle 'false' string explicitly
        if ($value === 'false' || $value === false) {
            $value = null;
        }

        return match ($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'array', 'json' => json_decode($value, true) ?? [],
            default => $value,  // image type returns string like default
        };
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type) {
            'boolean' => $value ? '1' : '0',
            'array', 'json' => json_encode($value),
            'image' => is_array($value) ? ($value[0] ?? null) : ($value ?: null),  // Extract from array if needed, handle empty values
            default => $value !== null ? (string) $value : null,
        };
    }
}
