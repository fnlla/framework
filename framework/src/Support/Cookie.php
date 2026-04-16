<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\Cookie\\Cookie') && !class_exists(__NAMESPACE__ . '\\Cookie')) {
    class_alias('\\Finella\\Cookie\\Cookie', __NAMESPACE__ . '\\Cookie');
}


