<?php
/**
 * fnlla (finella)
 * (c) TechAyo.co.uk
 * Proprietary License
 */
namespace Finella\Security;

if (class_exists('\\Finella\\Csrf\\CsrfTokenManager') && !class_exists(__NAMESPACE__ . '\\CsrfTokenManager')) {
    class_alias('\\Finella\\Csrf\\CsrfTokenManager', __NAMESPACE__ . '\\CsrfTokenManager');
}


