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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        // TEMPORARILY DISABLED - OrderObserver causing infinite loop
        // \App\Models\Order::observe(\App\Observers\OrderObserver::class);

        // Register event listeners
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\MergeCartOnLogin::class
        );
    }
}
