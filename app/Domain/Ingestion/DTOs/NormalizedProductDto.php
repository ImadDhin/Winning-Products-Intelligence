<?php

namespace App\Domain\Ingestion\DTOs;

readonly class NormalizedProductDto
{
    public function __construct(
        public string $title,
        public ?string $brand,
        public ?string $category_external_id,
        public ?string $description,
        public ?float $price,
        public string $currency,
        public array $variants,
        public array $images,
        public ?string $external_url,
        public array $metrics,
        public \DateTimeInterface $fetched_at,
    ) {}
}
