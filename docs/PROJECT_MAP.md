# Project Map

## Recent Modifications

### Mobile Hero & Slider Cache Fix (2026-05-13)
- **Issue**: 
  1. The hero text overlay was obscuring the newly designed mobile images.
  2. Disabling a slider in the Filament dashboard did not reflect on the frontend due to `spatie/laravel-responsecache`.
- **Solution**:
  - `app/Models/Slider.php`: Added `booted` method to listen for `saved` and `deleted` events to clear the `ResponseCache`.
  - `resources/views/livewire/store/hero-slider.blade.php`: Added conditional classes (`hidden md:block` and `hidden md:flex`) to hide the text content and gradient overlay on mobile screens *if* the slider has a custom `mobile_image_path`.
- **Tests**:
  - `tests/Feature/HeroSliderTest.php`: Added test cases for cache clearing and inactive slider visibility.

## Deprecated Code / Missing Features
- No code was deprecated in this surgery.
- The `violet_testing` database in MySQL or SQLite testing configuration requires proper alignment if using `RefreshDatabase` alongside non-standard migrations.
