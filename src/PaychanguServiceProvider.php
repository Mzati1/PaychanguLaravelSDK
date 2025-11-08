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
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/paychangu.php',
            'paychangu'
        );

        $this->app->singleton('paychangu', function ($app) {
            return new PaychanguService;
        });

        $this->app->alias('paychangu', PaychanguService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/paychangu.php' => config_path('paychangu.php'),
        ], 'paychangu-config');

        // Register commands
        if ($this->app->runningInConsole()) {
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
