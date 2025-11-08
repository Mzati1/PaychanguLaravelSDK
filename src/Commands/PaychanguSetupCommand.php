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

        if (! $this->setupEnvironmentVariables()) {
            return self::FAILURE;
        }

        $this->displaySuccessMessage();

        return self::SUCCESS;
    }

    protected function setupEnvironmentVariables(): bool
    {
        $envPath = $this->locateEnvFile();

        if (! $envPath) {
            $this->error('No .env file found.');
            $this->warn('Create a .env file first.');

            return false;
        }

        $this->info("Using environment file: {$envPath}");
        $this->newLine();

        try {
            $envContent = File::get($envPath);
        } catch (\Throwable $e) {
            $this->error('Unable to read .env file: '.$e->getMessage());

            return false;
        }

        $addedHeader = false;

        foreach ($this->envVariables as $key => $value) {
            $envContent = $this->updateOrAddEnvVariable($envContent, $key, $value, $addedHeader);
        }

        try {
            File::put($envPath, $envContent);
        } catch (\Throwable $e) {
            $this->error('Failed to update .env file: '.$e->getMessage());

            return false;
        }

        $this->info('Environment variables updated.');

        return true;
    }

    /**
     * Find `.env` or fallback `.env.*` files
     */
    protected function locateEnvFile(): ?string
    {
        $root = base_path();
        $candidates = glob("{$root}/.env*");

        if (! $candidates) {
            return null;
        }

        // Prefer `.env`
        $primary = "{$root}/.env";
        if (File::exists($primary)) {
            return $primary;
        }

        $files = array_filter($candidates, fn ($f) => ! str_ends_with($f, '.example'));

        // If only one .env-like file exists, use it
        if (count($files) === 1) {
            return reset($files);
        }

        // Ask user to pick if multiple exist
        $file = $this->choice(
            'Multiple environment files found — choose one:',
            $files
        );

        return $file;
    }

    protected function updateOrAddEnvVariable(string $content, string $key, string $value, &$addedHeader): string
    {
        $escaped = $this->escapeEnvValue($value);

        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$escaped}",
                $content
            );
            $this->line("Updated: {$key}");
        } else {
            if (! $addedHeader) {
                $content .= "\n\n# Paychangu Configuration\n";
                $addedHeader = true;
            }
            $content .= "{$key}={$escaped}\n";
            $this->line("Added: {$key}");
        }

        return $content;
    }

    protected function escapeEnvValue(string $value): string
    {
        return preg_match('/\s/', $value) ? '"'.addslashes($value).'"' : $value;
    }

    protected function displaySuccessMessage(): void
    {
        $this->newLine();
        $this->info('Paychangu SDK setup complete.');
        $this->newLine();

        $this->line('Next Steps:');
        $this->line('  1) Update .env with LIVE keys');
        $this->line('  2) Run: php artisan config:clear');
        $this->newLine();

        $this->line('Docs: https://github.com/Mzati1/PaychanguLaravelSDK');
        $this->line('Default mode: TEST.');
    }
}
