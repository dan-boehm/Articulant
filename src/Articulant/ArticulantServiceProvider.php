<?php

namespace DanBoehm\Articulant;

use Illuminate\Support\ServiceProvider;

/**
 * Class ArticulantServiceProvider
 *
 * @package DanBoehm\Articulant
 */
class ArticulantServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register() : void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'articulant');
    }

    /**
     * Boot the service provider.
     */
    public function boot() : void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('articulant.php'),
            ], 'config');
        }
    }
}