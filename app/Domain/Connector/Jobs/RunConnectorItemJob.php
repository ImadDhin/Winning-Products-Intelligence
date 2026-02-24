<?php

namespace App\Domain\Connector\Jobs;

use App\Domain\Ingestion\Jobs\IngestProductSnapshotJob;
use App\Domain\Connector\Models\JobAudit;
use App\Domain\Connector\Models\Source;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunConnectorItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $sourceId,
        public string $externalId,
        public array $context = [],
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
            'type' => 'scrape_item',
            'started_at' => now(),
            'status' => 'running',
        ]);
        try {
            $connector = $source->getConnector();
            $rawPayload = $connector->fetchItem($source, $this->externalId, $this->context);
            $audit->update([
                'finished_at' => now(),
                'status' => 'success',
                'items_processed' => 1,
            ]);
            IngestProductSnapshotJob::dispatch(
                $this->sourceId,
                $this->externalId,
                $rawPayload,
                now()
            );
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
