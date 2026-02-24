<?php

namespace App\Domain\Connector\Contracts;

use App\Domain\Connector\Models\Source;
use App\Domain\Connector\DTOs\ListPageResult;

interface ConnectorInterface
{
    public function fetchListPage(Source $source, ?string $cursor = null): ListPageResult;

    public function fetchItem(Source $source, string $externalId, array $context = []): mixed;

    public static function getName(): string;

    public static function getDefaultRateLimitPerMinute(): int;
}
