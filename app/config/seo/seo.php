<?php

declare(strict_types=1);

return [
    'defaults' => [
        'title' => env('SEO_DEFAULT_TITLE', ''),
        'description' => env('SEO_DEFAULT_DESCRIPTION', ''),
        'canonical' => env('SEO_CANONICAL', env('APP_URL', '')),
        'meta' => [
            // 'robots' => 'index,follow',
        ],
        'properties' => [
            'og:type' => env('SEO_OG_TYPE', 'website'),
            // 'og:site_name' => env('SEO_OG_SITE_NAME', ''),
        ],
        'json_ld' => [
            // [
            //     '@context' => 'https://schema.org',
            //     '@type' => 'Organization',
            //     'name' => 'TechAyo',
            // ],
        ],
    ],
];
