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

if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

$mod_id = 'pacKman';

$this->addUserAction(
    /* type */  'settings',
    /* action */    'delete_all',
    /* ns */        $mod_id,
    /* desc */  __('delete all settings')
);

$this->addUserAction(
    /* type */  'plugins',
    /* action */    'delete',
    /* ns */        $mod_id,
    /* desc */  __('delete plugin files')
);

$this->addDirectAction(
    /* type */  'settings',
    /* action */    'delete_all',
    /* ns */        $mod_id,
    /* desc */  sprintf(__('delete all %s settings'), $mod_id)
);

$this->addDirectAction(
    /* type */  'plugins',
    /* action */    'delete',
    /* ns */        $mod_id,
    /* desc */  sprintf(__('delete %s plugin files'), $mod_id)
);