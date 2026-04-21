<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support\Auth;

if (class_exists('\\Finella\\Auth\\SessionGuard') && !class_exists(__NAMESPACE__ . '\\SessionGuard')) {
    class_alias('\\Finella\\Auth\\SessionGuard', __NAMESPACE__ . '\\SessionGuard');
}


