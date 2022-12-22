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

if (!empty($_REQUEST['module']) && $_REQUEST['module'] == 'licenseBootstrap') {
    dcCore::app()->resources['help']['core_plugins_conf'] = __DIR__ . '/help/help.html';
}
