<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Http\Middleware;

if (class_exists('\\Finella\\RequestLogging\\RequestLoggerMiddleware') && !class_exists(__NAMESPACE__ . '\\RequestLoggerMiddleware')) {
    class_alias('\\Finella\\RequestLogging\\RequestLoggerMiddleware', __NAMESPACE__ . '\\RequestLoggerMiddleware');
}


