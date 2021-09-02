<?php
/**
 * @brief licenseBootstrap, a plugin for Dotclear 2
 * 
 * @package Dotclear
 * @subpackage Plugin
 * 
 * @author Jean-Christian Denis
 * 
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('DC_RC_PATH')) {
    return null;
}

$d = dirname(__FILE__).'/inc/';

$__autoload['licenseBootstrap'] = $d.'class.license.bootstrap.php';
$__autoload['libLicenseBootstrap']  = $d.'lib.license.bootstrap.php';