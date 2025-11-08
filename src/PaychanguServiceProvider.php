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
        $this->app->singleton('paychangu', function ($app) {
            return new PaychanguService();
        });

        $this->app->alias('paychangu', PaychanguService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
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
