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

dcCore::app()->blog->settings->addNamespace(basename(__DIR__));

dcCore::app()->addBehavior('adminDashboardFavoritesV2', function ($favs) {
    $favs->register(basename(__DIR__), [
        'title'      => __('License bootstrap'),
        'url'        => dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
        'small-icon' => urldecode(dcPage::getPF(basename(__DIR__) . '/icon.svg')),
        'large-icon' => urldecode(dcPage::getPF(basename(__DIR__) . '/icon.svg')),
        //'permissions' => dcCore::app()->auth->isSuperAdmin(),
    ]);
});

dcCore::app()->addBehavior('packmanBeforeCreatePackage', function ($module) {
    licenseBootstrap::addLicense($module);
});

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('License bootstrap'),
    dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
    urldecode(dcPage::getPF(basename(__DIR__) . '/icon.svg')),
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__))) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->isSuperAdmin()
);
