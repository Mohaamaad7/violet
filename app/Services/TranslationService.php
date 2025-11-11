<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    public function __construct(private ?CacheRepository $cache = null)
    {
        $this->cache = $cache ?: Cache::driver();
    }

    protected function cacheKey(string $locale, ?string $key = null): string
    {
        return $key
            ? "translation.$locale.$key"
            : "translation.$locale.__all";
    }

    public function get(string $key, string $locale, ?string $fallbackLocale = null): string
    {
        $fallbackLocale = $fallbackLocale ?: config('app.fallback_locale');

        // Try specific key cache first
        $cached = $this->cache->get($this->cacheKey($locale, $key));
        if ($cached !== null) {
            return $cached;
        }

        // Load single from DB
        $record = Translation::query()
            ->where('key', $key)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();

        if ($record) {
            $this->cache->forever($this->cacheKey($locale, $key), $record->value);
            return $record->value;
        }

        // Fallback to file translation
        $fileValue = trans($key, [], $locale);
        if ($fileValue !== $key) {
            // Cache file fallback to avoid repeated file loads
            $this->cache->forever($this->cacheKey($locale, $key), $fileValue);
            return $fileValue;
        }

        // Fallback locale
        if ($fallbackLocale && $fallbackLocale !== $locale) {
            return $this->get($key, $fallbackLocale, null);
        }

        return $key; // As ultimate fallback
    }

    public function has(string $key, string $locale): bool
    {
        if ($this->cache->has($this->cacheKey($locale, $key))) {
            return true;
        }

        return Translation::query()
            ->where('key', $key)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->exists();
    }

    public function set(string $key, string $locale, string $value, ?string $group = null, bool $active = true, ?int $updatedBy = null): Translation
    {
        $record = Translation::updateOrCreate([
            'key' => $key,
            'locale' => $locale,
        ], [
            'value' => $value,
            'group' => $group,
            'is_active' => $active,
            'updated_by' => $updatedBy,
        ]);

        $this->invalidateCache($locale, $key);

        return $record;
    }

    public function bulkImport(array $items, bool $override = false, ?int $updatedBy = null): void
    {
        foreach ($items as $item) {
            $key = $item['key'] ?? null;
            $locale = $item['locale'] ?? null;
            $value = $item['value'] ?? null;
            if (!$key || !$locale || $value === null) {
                continue;
            }
            $existing = Translation::query()
                ->where('key', $key)
                ->where('locale', $locale)
                ->first();
            if ($existing && !$override) {
                continue;
            }
            $this->set($key, $locale, $value, $item['group'] ?? null, $item['is_active'] ?? true, $updatedBy);
        }
    }

    public function invalidateCache(string $locale, ?string $key = null): void
    {
        if ($key) {
            $this->cache->forget($this->cacheKey($locale, $key));
        } else {
            // Simple strategy: flush all per-locale keys by pattern (if store supports it)
            // If not supported, could store index of keys per locale; kept simple here.
            // For non-redis/file stores pattern forget not available; consider Cache::tags in future.
        }
    }
}
