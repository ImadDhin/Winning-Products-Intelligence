<?php

namespace App\Domain\Connector\DTOs;

readonly class ListPageResult
{
    /** @param array<int, array{external_id: string, url?: string, context?: array}> $items */
    public function __construct(
        public array $items,
        public ?string $nextCursor,
    ) {}
}
