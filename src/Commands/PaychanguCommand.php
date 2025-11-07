<?php

namespace Mzati\PaychanguSDK\Commands;

use Illuminate\Console\Command;

class PaychanguCommand extends Command
{
    public $signature = 'paychangu:status';

    public $description = 'Check Paychangu integration status and configuration';

    public function handle(): int
    {
        $this->info('Checking Paychangu integration status...');
        $this->newLine();

        try {
            $service = app('paychangu');
            $environment = $service->getEnvironment();
            $isConfigured = ! empty(config('paychanguConfig.'.($environment === 'live' ? 'secret_key' : 'test_key')));

            $this->line('Environment: '.($environment === 'live' ? '🚀 LIVE' : '🔧 TEST'));
            $this->line('Base URL: '.config('paychanguConfig.base_url'));
            $this->line('Default Currency: '.config('paychanguConfig.currency'));
            $this->line('Timeout: '.config('paychanguConfig.timeout').' seconds');
            $this->line('API Key: '.($isConfigured ? '✓ Configured' : '✗ Missing'));

            if (! $isConfigured) {
                $this->warn('API key not configured for '.$environment.' mode.');
                $this->line('Run `php artisan paychangu:setup` to configure the SDK.');

                return self::FAILURE;
            }

            $this->newLine();
            $this->info('Paychangu SDK is properly configured and ready to use.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Error: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
