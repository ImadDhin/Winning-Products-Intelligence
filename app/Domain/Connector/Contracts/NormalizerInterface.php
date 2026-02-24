<?php

namespace App\Domain\Connector\Contracts;

use App\Domain\Ingestion\DTOs\NormalizedProductDto;

interface NormalizerInterface
{
    public function normalize(string $sourceSlug, mixed $rawPayload, \DateTimeInterface $fetchedAt): NormalizedProductDto;
}
