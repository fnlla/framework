<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\Cookie\\CookieJar') && !class_exists(__NAMESPACE__ . '\\CookieJar')) {
    class_alias('\\Finella\\Cookie\\CookieJar', __NAMESPACE__ . '\\CookieJar');
}


