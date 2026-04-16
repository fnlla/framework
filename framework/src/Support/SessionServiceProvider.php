<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\Session\\SessionServiceProvider') && !class_exists(__NAMESPACE__ . '\\SessionServiceProvider')) {
    class_alias('\\Finella\\Session\\SessionServiceProvider', __NAMESPACE__ . '\\SessionServiceProvider');
}


