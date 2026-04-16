<?php

declare(strict_types=1);

return [
    'fintech' => [
        'require_rag' => true,
        'rag_min_sources' => 2,
        'rag_min_score' => 0.3,
        'max_temperature' => 0.6,
        'max_output_tokens' => 900,
    ],
    'health' => [
        'require_rag' => true,
        'rag_min_sources' => 3,
        'rag_min_score' => 0.35,
        'max_temperature' => 0.5,
        'max_output_tokens' => 800,
    ],
    'enterprise' => [
        'require_rag' => true,
        'rag_min_sources' => 2,
        'rag_min_score' => 0.25,
        'max_temperature' => 0.7,
        'max_output_tokens' => 1000,
    ],
];

