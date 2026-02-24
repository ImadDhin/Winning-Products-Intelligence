<?php

namespace App\Domain\Leaderboard\Services;

use App\Domain\Leaderboard\Models\LeaderboardSegment;
use App\Domain\Product\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class LeaderboardService
{
    public function getProductIdsForSegment(string $segmentKey, int $offset = 0, int $limit = 50): array
    {
        $redisKey = 'leaderboard:' . $segmentKey;
        $ids = Redis::zrevrange($redisKey, $offset, $offset + $limit - 1);
        if ($ids !== [] && $ids !== false) {
            return array_map('intval', $ids);
        }
        return $this->fallbackFromDb($segmentKey, $offset, $limit);
    }

    public function rebuildSegment(string $segmentKey): void
    {
        $parts = explode(':', $segmentKey);
        $categoryId = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : null;
        $ttl = config('winning.leaderboard_ttl', 300);
        $topN = config('winning.leaderboard_top_n', 500);
        $query = Product::query()
            ->whereNotNull('current_score')
            ->orderByDesc('current_score')
            ->orderByDesc('score_updated_at')
            ->limit($topN);
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }
        $products = $query->pluck('current_score', 'id');
        $redisKey = 'leaderboard:' . $segmentKey;
        Redis::del($redisKey);
        foreach ($products as $id => $score) {
            Redis::zadd($redisKey, (float) $score, (string) $id);
        }
        Redis::expire($redisKey, $ttl);
        LeaderboardSegment::updateOrCreate(
            ['segment_key' => $segmentKey],
            ['redis_key' => $redisKey, 'updated_at' => now()]
        );
        Cache::put('leaderboard:meta:' . $segmentKey, json_encode([
            'updated_at' => now()->toIso8601String(),
            'count' => $products->count(),
        ]), $ttl);
    }

    private function fallbackFromDb(string $segmentKey, int $offset, int $limit): array
    {
        $parts = explode(':', $segmentKey);
        $categoryId = null;
        foreach ($parts as $i => $p) {
            if ($i === 1 && is_numeric($p)) {
                $categoryId = (int) $p;
                break;
            }
        }
        $query = Product::query()
            ->whereNotNull('current_score')
            ->orderByDesc('current_score')
            ->orderByDesc('score_updated_at')
            ->offset($offset)
            ->limit($limit);
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }
        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
