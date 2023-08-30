<?php

namespace Laraditz\Bayar;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BayarServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'bayar');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bayar');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('bayar.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/bayar'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/bayar'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/bayar'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'bayar');

        // Register the main class to use with the facade
        $this->app->singleton('bayar', function ($app) {
            return new Bayar($app);
        });
    }

    protected function registerRoutes()
    {
        $config = $this->routeConfiguration();

        Route::name($config['name'])
            ->prefix($config['prefix'])
            ->middleware($config['middleware'])
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('bayar.route_prefix'),
            'name' => config('bayar.route_name'),
            'middleware' => config('bayar.middleware'),
        ];
    }
}
