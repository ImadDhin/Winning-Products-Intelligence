<?php

namespace App\Domain\Connector\Jobs;

use App\Domain\Connector\Models\JobAudit;
use App\Domain\Connector\Models\Source;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunConnectorListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $sourceId,
        public ?string $listPageUrl = null,
        public ?string $cursor = null,
    ) {
        $this->onQueue('connectors');
    }

    public function handle(): void
    {
        $source = Source::find($this->sourceId);
        if (! $source || ! $source->is_enabled) {
            return;
        }
        $audit = JobAudit::create([
            'source_id' => $source->id,
            'type' => 'scrape_list',
            'started_at' => now(),
            'status' => 'running',
        ]);
        try {
            $connector = $source->getConnector();
            $result = $connector->fetchListPage($source, $this->cursor);
            $audit->update([
                'finished_at' => now(),
                'status' => 'success',
                'items_processed' => count($result->items),
            ]);
            $source->update(['last_run_at' => now(), 'consecutive_failures' => 0]);
        } catch (\Throwable $e) {
            $audit->update([
                'finished_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            $source->increment('consecutive_failures');
            throw $e;
        }
    }
}
