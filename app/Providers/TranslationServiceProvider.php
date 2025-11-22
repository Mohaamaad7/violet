<?php

namespace App\Providers;

use App\Translation\CombinedLoader;
use Illuminate\Translation\TranslationServiceProvider as BaseTranslationServiceProvider;

class TranslationServiceProvider extends BaseTranslationServiceProvider
{
    /**
     * Register the translation line loader with DB support.
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new CombinedLoader($app['files'], $app['path.lang']);
        });
    }
}
