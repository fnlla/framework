<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support\Auth;

if (interface_exists('\\Finella\\Auth\\UserProviderInterface') && !interface_exists(__NAMESPACE__ . '\\UserProviderInterface')) {
    class_alias('\\Finella\\Auth\\UserProviderInterface', __NAMESPACE__ . '\\UserProviderInterface');
}


