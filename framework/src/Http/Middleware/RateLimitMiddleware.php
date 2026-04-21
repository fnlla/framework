<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Http\Middleware;

if (class_exists('\\Finella\\RateLimit\\RateLimitMiddleware') && !class_exists(__NAMESPACE__ . '\\RateLimitMiddleware')) {
    class_alias('\\Finella\\RateLimit\\RateLimitMiddleware', __NAMESPACE__ . '\\RateLimitMiddleware');
}


