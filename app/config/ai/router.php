<?php

declare(strict_types=1);

return [
    'enabled' => env('AI_ROUTER_ENABLED', false),
    'default_route' => env('AI_ROUTER_DEFAULT_ROUTE', 'quality'),
    'fast_model' => env('AI_ROUTER_FAST_MODEL', env('OPENAI_MODEL', 'gpt-5.4')),
    'quality_model' => env('AI_ROUTER_QUALITY_MODEL', env('OPENAI_MODEL', 'gpt-5.4')),
    'cost_model' => env('AI_ROUTER_COST_MODEL', ''),
    'fallback_model' => env('AI_ROUTER_FALLBACK_MODEL', ''),
    'fast_provider' => env('AI_ROUTER_FAST_PROVIDER', ''),
    'quality_provider' => env('AI_ROUTER_QUALITY_PROVIDER', ''),
    'cost_provider' => env('AI_ROUTER_COST_PROVIDER', ''),
    'fallback_provider' => env('AI_ROUTER_FALLBACK_PROVIDER', ''),
    'ab_ratio' => (float) env('AI_ROUTER_AB_RATIO', 0.0),
    'ab_provider' => env('AI_ROUTER_AB_PROVIDER', ''),
];

