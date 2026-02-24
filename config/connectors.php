<?php

return [
    'connectors' => [
        'example_trending' => [
            'class' => \App\Domain\Connector\Connectors\ExampleTrendingConnector::class,
            'schedule_cron' => '*/5 * * * *',
            'rate_limit_per_minute' => 30,
            'enabled' => false,
        ],
    ],
];
