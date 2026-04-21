<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\Log\\Logger') && !class_exists(__NAMESPACE__ . '\\Logger')) {
    class_alias('\\Finella\\Log\\Logger', __NAMESPACE__ . '\\Logger');
}


