<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\RateLimit\\RateLimiter') && !class_exists(__NAMESPACE__ . '\\RateLimiter')) {
    class_alias('\\Finella\\RateLimit\\RateLimiter', __NAMESPACE__ . '\\RateLimiter');
}


