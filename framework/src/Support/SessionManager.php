<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (class_exists('\\Finella\\Session\\SessionManager') && !class_exists(__NAMESPACE__ . '\\SessionManager')) {
    class_alias('\\Finella\\Session\\SessionManager', __NAMESPACE__ . '\\SessionManager');
}


