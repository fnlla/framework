<?php

declare(strict_types=1);

return [
    'enabled' => env('AI_POLICY_ENABLED', true),
    'default_temperature' => (float) env('AI_POLICY_DEFAULT_TEMPERATURE', 0.2),
    'max_temperature' => (float) env('AI_POLICY_MAX_TEMPERATURE', 1.0),
    'default_output_tokens' => (int) env('AI_POLICY_DEFAULT_OUTPUT_TOKENS', 800),
    'max_output_tokens' => (int) env('AI_POLICY_MAX_OUTPUT_TOKENS', (function () {
        $envValue = strtolower((string) env('APP_ENV', 'prod'));
        $debugValue = strtolower((string) env('APP_DEBUG', '0'));
        $isProd = $envValue === 'prod' && !in_array($debugValue, ['1', 'true', 'yes'], true);
        return $isProd ? 800 : 1200;
    })()),
    'max_input_chars' => (int) env('AI_POLICY_MAX_INPUT_CHARS', 12000),
    'require_rag' => env('AI_POLICY_REQUIRE_RAG', (function () {
        $envValue = strtolower((string) env('APP_ENV', 'prod'));
        $debugValue = strtolower((string) env('APP_DEBUG', '0'));
        return $envValue === 'prod' && !in_array($debugValue, ['1', 'true', 'yes'], true);
    })()),
    'rag_min_sources' => (int) env('AI_POLICY_RAG_MIN_SOURCES', (function () {
        $envValue = strtolower((string) env('APP_ENV', 'prod'));
        $debugValue = strtolower((string) env('APP_DEBUG', '0'));
        $isProd = $envValue === 'prod' && !in_array($debugValue, ['1', 'true', 'yes'], true);
        return $isProd ? 2 : 1;
    })()),
    'rag_min_score' => (float) env('AI_POLICY_RAG_MIN_SCORE', 0.2),
    'packs' => is_file(__DIR__ . '/policy_packs.php') ? require __DIR__ . '/policy_packs.php' : [],
];
