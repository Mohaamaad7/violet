<?php

namespace App\Models\Concerns;

use App\Support\LegacyCatalogTranslation;
use Illuminate\Database\Eloquent\Builder;
use Spatie\ResponseCache\Facades\ResponseCache;

trait HasCatalogTranslations
{
    public function initializeHasCatalogTranslations(): void
    {
        foreach (static::translatedCatalogFields() as $field) {
            $this->fillable[] = $field.'_ar';
            $this->fillable[] = $field.'_en';
        }
    }

    public static function bootHasCatalogTranslations(): void
    {
        static::saving(function ($model): void {
            // The legacy name is retained as a stable source for imports/admin/order snapshots.
            if (! filled($model->getAttribute('name'))) {
                $model->setAttribute('name', $model->name_en ?: $model->name_ar ?: '');
            }
        });
        static::saved(function ($model): void {
            $fields = collect(static::translatedCatalogFields())
                ->flatMap(fn ($field) => [$field.'_ar', $field.'_en'])->all();
            if ($model->wasRecentlyCreated || $model->wasChanged(['name', 'status', 'is_active', ...$fields])) {
                ResponseCache::clear();
            }
        });
        static::deleted(fn () => ResponseCache::clear());
        static::restored(fn () => ResponseCache::clear());
    }

    public static function translatedCatalogFields(): array
    {
        return ['name', 'description', 'meta_title', 'meta_description'];
    }

    public function catalogTranslation(string $field, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->getAttribute($field.'_'.$locale);
        if (filled($value)) {
            return (string) $value;
        }

        return LegacyCatalogTranslation::extract($this->getAttribute($field))[$locale] ?? '';
    }

    public function getLocalizedNameAttribute(): string
    {
        return $this->catalogTranslation('name') ?: __('catalog.untranslated_name', ['id' => $this->getKey()]);
    }

    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->catalogTranslation('description');
    }

    public function getLocalizedShortDescriptionAttribute(): string
    {
        return $this->catalogTranslation('short_description');
    }

    public function getLocalizedLongDescriptionAttribute(): string
    {
        return $this->catalogTranslation('long_description');
    }

    public function getLocalizedSpecificationsAttribute(): string
    {
        return $this->catalogTranslation('specifications');
    }

    public function getLocalizedHowToUseAttribute(): string
    {
        return $this->catalogTranslation('how_to_use');
    }

    public function getLocalizedMetaTitleAttribute(): string
    {
        return $this->catalogTranslation('meta_title');
    }

    public function getLocalizedMetaDescriptionAttribute(): string
    {
        return $this->catalogTranslation('meta_description');
    }

    public function getLocalizedMetaKeywordsAttribute(): string
    {
        return $this->catalogTranslation('meta_keywords');
    }

    public function getHasCompleteNameTranslationsAttribute(): bool
    {
        return filled($this->name_ar) && filled($this->name_en);
    }

    public function validateCatalogPublication(): void
    {
        $errors = [];
        foreach (['ar', 'en'] as $locale) {
            if (! filled($this->getAttribute('name_'.$locale))) {
                $errors['name_'.$locale] = __('validation.required', ['attribute' => __('catalog.name').' ('.$locale.')']);
            }
        }
        if ($errors !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    public function scopeMissingNameTranslations(Builder $query): Builder
    {
        return $query->where(fn ($query) => $query
            ->whereNull('name_ar')->orWhere('name_ar', '')
            ->orWhereNull('name_en')->orWhere('name_en', ''));
    }

    public function scopeSearchCatalog(Builder $query, string $term, array $fields = ['name', 'description']): Builder
    {
        return $query->where(function ($query) use ($term, $fields) {
            foreach ($fields as $field) {
                foreach ([$field, $field.'_ar', $field.'_en'] as $column) {
                    $query->orWhere($this->qualifyColumn($column), 'like', $term);
                }
            }
        });
    }
}
