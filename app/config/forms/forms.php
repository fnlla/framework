<?php

declare(strict_types=1);

return [
    'honeypot' => [
        'enabled' => env('FORMS_HONEYPOT_ENABLED', true),
        'field' => env('FORMS_HONEYPOT_FIELD', 'website'),
        'time_field' => env('FORMS_HONEYPOT_TIME_FIELD', '_form_time'),
        'min_seconds' => env('FORMS_HONEYPOT_MIN_SECONDS', 3),
    ],
];
