<?php

namespace App\Domain\Product\DTOs;

readonly class ProductCardDto
{
    public function __construct(
        public int $id,
        public string $title,
        public float $score,
        public float $confidence,
        public ?string $thumbnail,
        public ?float $priceMin,
        public ?float $priceMax,
        public ?string $currency,
    ) {}

    public function confidenceBand(): string
    {
        return match (true) {
            $this->confidence >= 0.7 => 'High',
            $this->confidence >= 0.4 => 'Medium',
            default => 'Low',
        };
    }
}
