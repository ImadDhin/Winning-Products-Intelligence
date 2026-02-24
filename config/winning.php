<?php

return [
    'weights' => [
        'demand_growth' => 0.25,
        'competition' => 0.20,
        'margin_potential' => 0.20,
        'stability' => 0.15,
        'freshness' => 0.10,
        'seasonality' => 0.10,
    ],

    'leaderboard_ttl' => (int) env('WINNING_LEADERBOARD_TTL', 300),
    'product_card_ttl' => (int) env('WINNING_PRODUCT_CARD_TTL', 300),
    'confidence_decay_hours' => 24,
    'volatility_threshold_pct' => 20,
    'sources_for_full_confidence' => 3,
    'circuit_breaker_failures' => 5,
    'leaderboard_top_n' => 500,

    'segments' => [
        'windows' => ['24h', '7d', '30d'],
        'default_list' => ['default:24h', 'default:7d', 'default:30d'],
    ],

    'data_retention_days' => [
        'snapshots' => 90,
        'metrics' => 90,
        'raw_payload' => 30,
    ],
];
