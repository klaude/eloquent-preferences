<?php

namespace KLaude\EloquentPreferences;

use Illuminate\Support\ServiceProvider;

/**
 * Load model preference configs and schema migrations into Laravel.
 *
 * @codeCoverageIgnore
 */
class EloquentPreferencesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/eloquent-preferences.php',
            'eloquent-preferences'
        );
    }

    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/config/eloquent-preferences.php' => config_path('eloquent-preferences.php'),
        ], 'config');

        $this->publishes([
            dirname(__DIR__) . '/database/migrations/' => database_path('migrations'),
        ], 'migrations');
    }
}
