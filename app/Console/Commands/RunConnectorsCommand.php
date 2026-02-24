<?php

namespace App\Console\Commands;

use App\Domain\Connector\Jobs\RunConnectorListJob;
use App\Domain\Connector\Models\Source;
use Illuminate\Console\Command;

class RunConnectorsCommand extends Command
{
    protected $signature = 'winning:run-connectors';

    protected $description = 'Dispatch list jobs for all enabled connectors';

    public function handle(): int
    {
        $sources = Source::where('is_enabled', true)->get();
        foreach ($sources as $source) {
            if ($this->circuitOpen($source)) {
                $this->warn("Circuit open for source {$source->id}, skipping.");
                continue;
            }
            RunConnectorListJob::dispatch($source->id);
        }
        return self::SUCCESS;
    }

    private function circuitOpen(Source $source): bool
    {
        $failures = (int) config('winning.circuit_breaker_failures', 5);
        return $source->consecutive_failures >= $failures;
    }
}
