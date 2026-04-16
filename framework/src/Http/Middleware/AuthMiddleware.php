<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Http\Middleware;

if (class_exists('\\Finella\\Auth\\Middleware\\AuthMiddleware') && !class_exists(__NAMESPACE__ . '\\AuthMiddleware')) {
    class_alias('\\Finella\\Auth\\Middleware\\AuthMiddleware', __NAMESPACE__ . '\\AuthMiddleware');
}


