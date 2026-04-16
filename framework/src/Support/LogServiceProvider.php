<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\Log\\LogServiceProvider') && !class_exists(__NAMESPACE__ . '\\LogServiceProvider')) {
    class_alias('\\Finella\\Log\\LogServiceProvider', __NAMESPACE__ . '\\LogServiceProvider');
}


