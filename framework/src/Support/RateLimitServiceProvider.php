<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\RateLimit\\RateLimitServiceProvider') && !class_exists(__NAMESPACE__ . '\\RateLimitServiceProvider')) {
    class_alias('\\Finella\\RateLimit\\RateLimitServiceProvider', __NAMESPACE__ . '\\RateLimitServiceProvider');
}


