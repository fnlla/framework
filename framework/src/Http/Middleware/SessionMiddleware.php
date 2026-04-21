<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Http\Middleware;

if (class_exists('\\Finella\\Session\\SessionMiddleware') && !class_exists(__NAMESPACE__ . '\\SessionMiddleware')) {
    class_alias('\\Finella\\Session\\SessionMiddleware', __NAMESPACE__ . '\\SessionMiddleware');
}


