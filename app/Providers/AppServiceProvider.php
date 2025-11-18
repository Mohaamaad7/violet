<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind TranslationService as a singleton for app-wide reuse
        $this->app->singleton(\App\Services\TranslationService::class, function ($app) {
            return new \App\Services\TranslationService();
        });

        // Override Laravel translation loader to combine DB + files
        $this->app->singleton('translation.loader', function ($app) {
            return new \App\Translation\CombinedLoader($app['files'], $app['path.lang']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\MergeCartOnLogin::class
        );
    }
}
