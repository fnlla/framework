<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Http\Middleware;

if (class_exists('\\Finella\\Cookie\\CookieMiddleware') && !class_exists(__NAMESPACE__ . '\\CookieMiddleware')) {
    class_alias('\\Finella\\Cookie\\CookieMiddleware', __NAMESPACE__ . '\\CookieMiddleware');
}


