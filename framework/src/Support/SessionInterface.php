<?php
/**
 * Finella
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support;

if (interface_exists('\\Finella\\Session\\SessionInterface') && !interface_exists(__NAMESPACE__ . '\\SessionInterface')) {
    class_alias('\\Finella\\Session\\SessionInterface', __NAMESPACE__ . '\\SessionInterface');
}


