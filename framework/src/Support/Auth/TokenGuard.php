<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Support\Auth;

if (class_exists('\\Finella\\Auth\\TokenGuard') && !class_exists(__NAMESPACE__ . '\\TokenGuard')) {
    class_alias('\\Finella\\Auth\\TokenGuard', __NAMESPACE__ . '\\TokenGuard');
}


