# Project Map

## Recent Modifications

### Hide Hero on Mobile & Cache Manager (2026-05-14)
- **Issue**: 
  1. The hero section needed to be completely hidden on mobile (not just the text overlay).
  2. The `mobile_image_path` field became unnecessary since the entire hero is hidden on mobile.
  3. Admins needed a clear way to clear cache for images/multimedia updates.
- **Solution**:
  - `resources/views/livewire/store/home.blade.php`: Wrapped `<livewire:store.hero-slider />` in `<div class="hidden md:block">` to hide hero on mobile.
  - `resources/views/store/home.blade.php`: Same wrapping for the standalone blade view.
  - `resources/views/livewire/store/hero-slider.blade.php`: Simplified - removed `<picture>`/`<source>` elements and conditional classes (`hidden md:block`/`hidden md:flex`) since `mobile_image_path` is no longer used.
  - `app/Models/Slider.php`: Removed `mobile_image_path` from `$fillable`.
  - `app/Filament/Resources/Sliders/Schemas/SliderForm.php`: Removed `mobile_image_path` FileUpload field; simplified section to single image upload.
  - `app/Filament/Pages/CacheManager.php` **(NEW)**: Filament page under System group for super-admins to clear response cache, application cache, blade cache, or all caches at once. Uses `ResponseCache::clear()` and `Artisan::call()` with confirmation dialogs.
  - `resources/views/filament/pages/cache-manager.blade.php` **(NEW)**: Admin UI with action buttons and current cache configuration display.
  - `lang/en/admin.php` + `lang/ar/admin.php`: Added cache manager translations.
- **Tests**:
  - `tests/Feature/HeroSliderTest.php`: Added `test_hero_is_hidden_on_mobile_by_css_class` to verify the `hidden md:block` wrapper class exists.
  - `tests/Feature/CacheManagerTest.php` **(NEW)**: Tests for cache manager page accessibility and cache clearing operations.

## Deprecated Code
- `mobile_image_path` field in Slider model/form/blade — replaced by auto-hide on mobile approach. Column remains in database but is no longer used.
