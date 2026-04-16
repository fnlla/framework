<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Http\Middleware;

if (class_exists('\\Finella\\SecurityHeaders\\SecurityHeadersMiddleware') && !class_exists(__NAMESPACE__ . '\\SecurityHeadersMiddleware')) {
    class_alias('\\Finella\\SecurityHeaders\\SecurityHeadersMiddleware', __NAMESPACE__ . '\\SecurityHeadersMiddleware');
}


