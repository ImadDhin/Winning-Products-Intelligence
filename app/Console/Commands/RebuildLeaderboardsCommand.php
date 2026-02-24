<?php

namespace App\Console\Commands;

use App\Domain\Leaderboard\Jobs\RebuildLeaderboardSegmentJob;
use Illuminate\Console\Command;

class RebuildLeaderboardsCommand extends Command
{
    protected $signature = 'winning:rebuild-leaderboards';

    protected $description = 'Rebuild leaderboard segments';

    public function handle(): int
    {
        $segments = config('winning.segments.default_list', ['default:24h', 'default:7d', 'default:30d']);
        foreach ($segments as $key) {
            RebuildLeaderboardSegmentJob::dispatch($key);
        }
        return self::SUCCESS;
    }
}
