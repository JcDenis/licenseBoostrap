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

dcCore::app()->blog->settings->addNamespace('licenseBootstrap');

dcCore::app()->addBehavior('adminDashboardFavoritesV2', function ($favs) {
    $favs->register('licenseBootstrap', [
        'title'      => __('License bootstrap'),
        'url'        => dcCore::app()->adminurl->get('admin.plugin.licenseBootstrap'),
        'small-icon' => urldecode(dcPage::getPF('licenseBootstrap/icon.svg')),
        'large-icon' => urldecode(dcPage::getPF('licenseBootstrap/icon.svg')),
        //'permissions' => dcCore::app()->auth->isSuperAdmin(),
    ]);
});

dcCore::app()->addBehavior('packmanBeforeCreatePackage', function ($module) {
    licenseBootstrap::addLicense($module);
});

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('License bootstrap'),
    dcCore::app()->adminurl->get('admin.plugin.licenseBootstrap'),
    urldecode(dcPage::getPF('licenseBootstrap/icon.svg')),
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.licenseBootstrap')) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->isSuperAdmin()
);
