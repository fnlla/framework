<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support\Auth;

if (class_exists('\\Finella\\Auth\\AuthManager') && !class_exists(__NAMESPACE__ . '\\AuthManager')) {
    class_alias('\\Finella\\Auth\\AuthManager', __NAMESPACE__ . '\\AuthManager');
}


