<?php

declare(strict_types=1);

return [
    'enabled' => env('AI_RAG_ENABLED', false),
    'table' => env('AI_RAG_TABLE', 'ai_embeddings'),
    'chunk_size' => (int) env('AI_RAG_CHUNK_SIZE', 1200),
    'chunk_overlap' => (int) env('AI_RAG_CHUNK_OVERLAP', 120),
    'max_candidates' => (int) env('AI_RAG_MAX_CANDIDATES', 200),
    'min_content_length' => (int) env('AI_RAG_MIN_CONTENT', 40),
    'max_content_length' => (int) env('AI_RAG_MAX_CONTENT', 20000),
];

