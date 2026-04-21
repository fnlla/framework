<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\Cookie\\CookieServiceProvider') && !class_exists(__NAMESPACE__ . '\\CookieServiceProvider')) {
    class_alias('\\Finella\\Cookie\\CookieServiceProvider', __NAMESPACE__ . '\\CookieServiceProvider');
}


