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

$this->registerModule(
    'License bootstrap',
    'Add license to your plugins and themes',
    'Jean-Christian Denis',
    '2021.08.17.1',
    [
        'requires' => [['core', '2.19']],
        'permissions' => null,
        'type' => 'plugin',
        'support' => 'https://github.com/JcDenis/licenseBootstrap',
        'details' => 'https://plugins.dotaddict.org/dc2/details/licenseBootstrap',
        'repository' => 'https://raw.githubusercontent.com/JcDenis/licenseBootstrap/master/dcstore.xml'
    ]
);