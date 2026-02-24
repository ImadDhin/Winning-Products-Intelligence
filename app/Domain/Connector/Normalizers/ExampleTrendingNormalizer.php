<?php

namespace App\Domain\Connector\Normalizers;

use App\Domain\Connector\Contracts\NormalizerInterface;
use App\Domain\Ingestion\DTOs\NormalizedProductDto;

class ExampleTrendingNormalizer implements NormalizerInterface
{
    public function normalize(string $sourceSlug, mixed $rawPayload, \DateTimeInterface $fetchedAt): NormalizedProductDto
    {
        $data = is_array($rawPayload) ? $rawPayload : (array) $rawPayload;
        return new NormalizedProductDto(
            title: $data['title'] ?? $data['name'] ?? '',
            brand: $data['brand'] ?? null,
            category_external_id: $data['category_id'] ?? $data['category_external_id'] ?? null,
            description: $data['description'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            currency: $data['currency'] ?? 'USD',
            variants: $data['variants'] ?? [],
            images: $data['images'] ?? $data['image'] ? [$data['image']] : [],
            external_url: $data['url'] ?? $data['external_url'] ?? null,
            metrics: [
                'engagement_velocity' => $data['metrics']['engagement_velocity'] ?? 0,
                'review_count' => $data['metrics']['review_count'] ?? 0,
                'rating' => $data['metrics']['rating'] ?? 0,
                'seller_count_proxy' => $data['metrics']['seller_count_proxy'] ?? 0,
            ],
            fetched_at: $fetchedAt,
        );
    }
}
