<?php

namespace Mzati\PaychanguSDK\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PaychanguSetupCommand extends Command
{
    public $signature = 'paychangu:setup {--force : Overwrite existing configuration}';

    public $description = 'Setup Paychangu SDK configuration and environment variables';

    protected array $envVariables = [
        'PAYCHANGU_SECRET_KEY' => 'sk_test_xxxxx',
        'PAYCHANGU_TEST_KEY' => 'pk_test_xxxxx',
        'PAYCHANGU_ENVIRONMENT' => 'test',
        'PAYCHANGU_BASE_URL' => 'https://api.paychangu.com',
        'PAYCHANGU_CURRENCY' => 'MWK',
        'PAYCHANGU_TIMEOUT' => '30',
    ];

    public function handle(): int
    {
        $this->info('Setting up Paychangu SDK...');
        $this->newLine();

        // Step 1: Publish configuration file
        if (! $this->publishConfig()) {
            return self::FAILURE;
        }

        // Step 2: Setup environment variables
        if (! $this->setupEnvironmentVariables()) {
            return self::FAILURE;
        }

        // Step 3: Display success message and next steps
        $this->displaySuccessMessage();

        return self::SUCCESS;
    }

    protected function publishConfig(): bool
    {
        $configPath = config_path('paychanguConfig.php');

        if (File::exists($configPath) && ! $this->option('force')) {
            if (! $this->confirm('Configuration file already exists. Do you want to overwrite it?', false)) {
                $this->info('✓ Using existing configuration file');

                return true;
            }
        }

        $this->info('Publishing configuration file...');

        $this->call('vendor:publish', [
            '--tag' => 'paychanguConfig-config',
            '--force' => $this->option('force'),
        ]);

        if (File::exists($configPath)) {
            $this->info('✓ Configuration file published successfully');

            return true;
        }

        $this->error('✗ Failed to publish configuration file');

        return false;
    }

    protected function setupEnvironmentVariables(): bool
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $this->error('✗ .env file not found');
            $this->warn('Please create a .env file in your project root first');

            return false;
        }

        $this->info('Configuring environment variables...');
        $this->newLine();

        // Read current .env content
        $envContent = File::get($envPath);

        // Add or update each environment variable
        foreach ($this->envVariables as $key => $value) {
            $envContent = $this->updateOrAddEnvVariable($envContent, $key, $value);
        }

        // Write back to .env file
        if (File::put($envPath, $envContent)) {
            $this->info('✓ Environment variables configured successfully');
            $this->newLine();

            return true;
        }

        $this->error('✗ Failed to update .env file');

        return false;
    }

    protected function updateOrAddEnvVariable(string $envContent, string $key, string $value): string
    {
        // Escape special characters in the value
        $escapedValue = $this->escapeEnvValue($value);

        // Check if the key already exists
        if (preg_match("/^{$key}=.*/m", $envContent)) {
            // Update existing key
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$escapedValue}",
                $envContent
            );
            $this->line("  → Updated {$key}");
        } else {
            // Add new key at the end
            $envContent = rtrim($envContent)."\n\n# Paychangu Configuration\n{$key}={$escapedValue}\n";
            $this->line("  → Added {$key}");
        }

        return $envContent;
    }

    protected function escapeEnvValue(string $value): string
    {
        // If value contains spaces or special characters, wrap in quotes
        if (preg_match('/\s/', $value) || empty($value)) {
            return '"'.str_replace('"', '\\"', $value).'"';
        }

        return $value;
    }

    protected function displaySuccessMessage(): void
    {
        $this->newLine();
        $this->info('Paychangu SDK setup completed successfully!');
        $this->newLine();

        $this->line('Next steps:');
        $this->line('  1. Update your API keys in the .env file with your actual Paychangu keys');
        $this->line('  2. Run: php artisan config:clear');
        $this->newLine();

        $this->line('Quick start example:');
        $this->line('  use Mzati\PaychanguSDK\Facades\Paychangu;');
        $this->newLine();
        $this->line('  $transaction = Paychangu::initiateTransaction([');
        $this->line('      \'amount\' => 1000,');
        $this->line('      \'email\' => \'customer@example.com\',');
        $this->line('      \'tx_ref\' => \'TXN-\' . time(),');
        $this->line('      \'callback_url\' => route(\'payment.callback\'),');
        $this->line('  ]);');
        $this->newLine();

        $this->line('Documentation: https://github.com/Mzati1/PaychanguLaravelSDK');
        $this->info('ℹThe SDK is configured in TEST mode by default. Update PAYCHANGU_ENVIRONMENT in .env for production.');
    }
}
