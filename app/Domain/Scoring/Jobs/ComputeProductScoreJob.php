<?php

namespace App\Domain\Scoring\Jobs;

use App\Domain\Scoring\Services\ScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeProductScoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(
        public int $productId,
    ) {
        $this->onQueue('scoring');
    }

    public function handle(ScoringService $scoring): void
    {
        $scoring->compute($this->productId);
    }
}
