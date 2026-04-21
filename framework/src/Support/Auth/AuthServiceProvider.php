<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support\Auth;

if (class_exists('\\Finella\\Auth\\AuthServiceProvider') && !class_exists(__NAMESPACE__ . '\\AuthServiceProvider')) {
    class_alias('\\Finella\\Auth\\AuthServiceProvider', __NAMESPACE__ . '\\AuthServiceProvider');
}


