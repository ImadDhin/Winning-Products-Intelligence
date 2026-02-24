<?php

namespace App\Domain\Scoring\DTOs;

readonly class ScoreResult
{
    public function __construct(
        public float $score,
        public float $confidence,
        public array $components,
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
