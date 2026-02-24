<?php

namespace App\Domain\Leaderboard\Jobs;

use App\Domain\Leaderboard\Services\LeaderboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildLeaderboardSegmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public string $segmentKey,
    ) {
        $this->onQueue('leaderboard');
    }

    public function handle(LeaderboardService $service): void
    {
        $service->rebuildSegment($this->segmentKey);
    }
}
