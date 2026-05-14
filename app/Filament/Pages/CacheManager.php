<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Spatie\ResponseCache\Facades\ResponseCache;

class CacheManager extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRocketLaunch;

    protected string $view = 'filament.pages.cache-manager';

    public static function getNavigationLabel(): string
    {
        return __('admin.cache.title');
    }

    public function getTitle(): string
    {
        return __('admin.cache.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.system');
    }

    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    public function clearResponseCache(): void
    {
        try {
            ResponseCache::clear();
            Log::info('ResponseCache cleared by admin via CacheManager page.', ['user' => auth()->user()?->id]);
            Notification::make()->title(__('admin.cache.response_cleared'))->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title(__('admin.cache.failed'))->body($e->getMessage())->danger()->send();
        }
    }

    public function clearAppCache(): void
    {
        try {
            Artisan::call('cache:clear');
            Log::info('Application cache cleared by admin via CacheManager page.', ['user' => auth()->user()?->id]);
            Notification::make()->title(__('admin.cache.app_cleared'))->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title(__('admin.cache.failed'))->body($e->getMessage())->danger()->send();
        }
    }

    public function clearBladeCache(): void
    {
        try {
            Artisan::call('view:clear');
            Log::info('Blade view cache cleared by admin via CacheManager page.', ['user' => auth()->user()?->id]);
            Notification::make()->title(__('admin.cache.blade_cleared'))->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title(__('admin.cache.failed'))->body($e->getMessage())->danger()->send();
        }
    }

    public function clearAllCache(): void
    {
        try {
            ResponseCache::clear();
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Log::info('All caches cleared by admin via CacheManager page.', ['user' => auth()->user()?->id]);
            Notification::make()->title(__('admin.cache.all_cleared'))->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title(__('admin.cache.failed'))->body($e->getMessage())->danger()->send();
        }
    }
}
