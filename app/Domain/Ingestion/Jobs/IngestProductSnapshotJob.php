<?php

namespace App\Domain\Ingestion\Jobs;

use App\Domain\Connector\Models\Source;
use App\Domain\Connector\Contracts\NormalizerInterface;
use App\Domain\Ingestion\Deduplication\DeduplicationService;
use App\Domain\Scoring\Jobs\ComputeProductScoreJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IngestProductSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public int $sourceId,
        public string $externalId,
        public mixed $rawPayload,
        public \DateTimeInterface $fetchedAt,
    ) {
        $this->onQueue('ingestion');
    }

    public function handle(NormalizerInterface $normalizer, DeduplicationService $dedup): void
    {
        $source = Source::find($this->sourceId);
        if (! $source) {
            return;
        }
        $dto = $normalizer->normalize($source->slug, $this->rawPayload, $this->fetchedAt);
        $normalized = [
            'title' => $dto->title,
            'brand' => $dto->brand,
            'category_external_id' => $dto->category_external_id,
            'price' => $dto->price,
            'currency' => $dto->currency,
            'variants' => $dto->variants,
            'images' => $dto->images,
            'metrics' => $dto->metrics,
        ];
        $fingerprint = $dedup->fingerprint($dto);
        $raw = is_array($this->rawPayload) ? $this->rawPayload : ['payload' => $this->rawPayload];
        $product = $dedup->mergeOrCreate($dto, $fingerprint, $this->sourceId, $this->externalId, $raw, $normalized, $this->fetchedAt);
        ComputeProductScoreJob::dispatch($product->id);
    }
}
