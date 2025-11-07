<?php

namespace Mzati\PaychanguSDK\Commands;

use Illuminate\Console\Command;

class PaychanguCommand extends Command
{
    public $signature = 'paychangu:status';

    public $description = 'Check Paychangu integration status';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
