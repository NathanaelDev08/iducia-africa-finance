<?php
namespace App\Console\Commands;

use App\Services\TelemetryService;
use Illuminate\Console\Command;

class TelemetrySnapshot extends Command
{
    protected $signature = 'telemetry:snapshot';
    protected $description = 'Snapshot de télémétrie (interne)';

    public function handle(TelemetryService $service): int
    {
        $service->snapshot();
        $service->beacon();
        return self::SUCCESS;
    }
}
