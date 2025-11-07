<?php

namespace Mzati\PaychanguSDK;

use Illuminate\Support\ServiceProvider;
use Mzati\PaychanguSDK\Commands\PaychanguCommand;
use Mzati\PaychanguSDK\Commands\PaychanguSetupCommand;

class PaychanguServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package config with user's published config
        $this->mergeConfigFrom(
            __DIR__.'/../config/PaychanguConfig.php',
            'paychanguConfig'
        );

        // Bind the main service to the container as a singleton
        $this->app->singleton('paychangu', function ($app) {
            return new PaychanguService(
                config('paychanguConfig.environment', 'test')
            );
        });

        // Register the facade alias
        $this->app->alias('paychangu', PaychanguService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config file
            $this->publishes([
                __DIR__.'/../config/PaychanguConfig.php' => config_path('paychanguConfig.php'),
            ], 'paychanguConfig-config');

            // Register commands
            $this->commands([
                PaychanguCommand::class,
                PaychanguSetupCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['paychangu', PaychanguService::class];
    }
}
