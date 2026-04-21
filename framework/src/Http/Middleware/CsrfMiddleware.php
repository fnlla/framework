<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Http\Middleware;

if (class_exists('\\Finella\\Csrf\\CsrfMiddleware') && !class_exists(__NAMESPACE__ . '\\CsrfMiddleware')) {
    class_alias('\\Finella\\Csrf\\CsrfMiddleware', __NAMESPACE__ . '\\CsrfMiddleware');
}


