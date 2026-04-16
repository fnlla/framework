<?php

declare(strict_types=1);

return [
    'provider' => env('AI_PROVIDER', 'openai'),
    'driver' => env('AI_DRIVER', 'openai'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'api_key' => env('OPENAI_API_KEY', ''),
    'model' => env('OPENAI_MODEL', 'gpt-5.4'),
    'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
    'realtime_model' => env('OPENAI_REALTIME_MODEL', 'gpt-realtime-1.5'),
    'realtime_voice' => env('OPENAI_REALTIME_VOICE', 'marin'),
    'realtime_instructions' => env('OPENAI_REALTIME_INSTRUCTIONS', ''),
    'organization' => env('OPENAI_ORG', ''),
    'project' => env('OPENAI_PROJECT', ''),
    'fallback_provider' => env('AI_FALLBACK_PROVIDER', ''),
    'providers' => [
        'secondary' => [
            'driver' => env('AI_SECONDARY_DRIVER', 'openai'),
            'base_url' => env('AI_SECONDARY_BASE_URL', ''),
            'api_key' => env('AI_SECONDARY_API_KEY', ''),
            'model' => env('AI_SECONDARY_MODEL', ''),
            'embedding_model' => env('AI_SECONDARY_EMBEDDING_MODEL', ''),
            'realtime_model' => env('AI_SECONDARY_REALTIME_MODEL', ''),
            'realtime_voice' => env('AI_SECONDARY_REALTIME_VOICE', ''),
            'realtime_instructions' => env('AI_SECONDARY_REALTIME_INSTRUCTIONS', ''),
            'organization' => env('AI_SECONDARY_ORG', ''),
            'project' => env('AI_SECONDARY_PROJECT', ''),
        ],
    ],
];



