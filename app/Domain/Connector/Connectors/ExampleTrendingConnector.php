<?php

namespace App\Domain\Connector\Connectors;

use App\Domain\Connector\Contracts\ConnectorInterface;
use App\Domain\Connector\DTOs\ListPageResult;
use App\Domain\Connector\Exceptions\ConnectorException;
use App\Domain\Connector\Jobs\RunConnectorItemJob;
use App\Domain\Connector\Models\Source;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExampleTrendingConnector implements ConnectorInterface
{
    public function fetchListPage(Source $source, ?string $cursor = null): ListPageResult
    {
        $this->rateLimit($source);
        $url = $this->buildListUrl($source, $cursor);
        $response = Http::timeout(15)->get($url);
        if (! $response->successful()) {
            throw new ConnectorException('List fetch failed: ' . $response->status());
        }
        $body = $response->json();
        $items = $this->parseListResponse($body);
        $nextCursor = $this->parseNextCursor($body);
        foreach ($items as $item) {
            RunConnectorItemJob::dispatch($source->id, $item['external_id'], $item);
        }
        return new ListPageResult($items, $nextCursor);
    }

    public function fetchItem(Source $source, string $externalId, array $context = []): mixed
    {
        $this->rateLimit($source);
        $url = $this->buildItemUrl($source, $externalId);
        $response = Http::timeout(10)->get($url);
        if (! $response->successful()) {
            throw new ConnectorException('Item fetch failed: ' . $response->status());
        }
        return $response->json();
    }

    private function rateLimit(Source $source): void
    {
        $key = "connector:rate:{$source->id}";
        $limit = $source->rate_limit_per_minute ?? static::getDefaultRateLimitPerMinute();
        $current = (int) Cache::get($key, 0);
        if ($current >= $limit) {
            throw new ConnectorException('Rate limit exceeded for source ' . $source->id);
        }
        Cache::put($key, $current + 1, 60);
    }

    private function buildListUrl(Source $source, ?string $cursor): string
    {
        $base = $source->config['list_url'] ?? 'https://api.example.com/trending';
        return $cursor ? $base . '?cursor=' . urlencode($cursor) : $base;
    }

    private function buildItemUrl(Source $source, string $externalId): string
    {
        $base = $source->config['item_url'] ?? 'https://api.example.com/items';
        return rtrim($base, '/') . '/' . urlencode($externalId);
    }

    /** @return array<int, array{external_id: string, url?: string, context?: array}> */
    private function parseListResponse(?array $body): array
    {
        $items = $body['items'] ?? $body['data'] ?? [];
        $out = [];
        foreach ($items as $i) {
            $id = $i['id'] ?? $i['external_id'] ?? (string) $i;
            $out[] = [
                'external_id' => (string) $id,
                'url' => $i['url'] ?? null,
                'context' => $i,
            ];
        }
        return $out;
    }

    private function parseNextCursor(?array $body): ?string
    {
        return $body['next_cursor'] ?? $body['cursor'] ?? null;
    }

    public static function getName(): string
    {
        return 'Example Trending';
    }

    public static function getDefaultRateLimitPerMinute(): int
    {
        return 30;
    }
}
