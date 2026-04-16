<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support\Auth;

if (class_exists('\\Finella\\Auth\\CallableUserProvider') && !class_exists(__NAMESPACE__ . '\\CallableUserProvider')) {
    class_alias('\\Finella\\Auth\\CallableUserProvider', __NAMESPACE__ . '\\CallableUserProvider');
}


