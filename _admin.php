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

dcCore::app()->addBehavior('adminDashboardFavoritesV2', [
    'licenseBootstrapBehaviors', 'adminDashboardFavorites',
]);

dcCore::app()->addBehavior('packmanBeforeCreatePackage', [
    'licenseBootstrapBehaviors', 'packmanBeforeCreatePackage',
]);

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('License bootstrap'),
    'plugin.php?p=licenseBootstrap',
    'index.php?pf=licenseBootstrap/icon.png',
    preg_match(
        '/plugin.php\?p=licenseBootstrap(&.*)?$/',
        $_SERVER['REQUEST_URI']
    ),
    dcCore::app()->auth->isSuperAdmin()
);

class licenseBootstrapBehaviors
{
    public static function adminDashboardFavorites($favs)
    {
        $favs->register('licenseBootstrap', [
            'title'       => __('License bootstrap'),
            'url'         => 'plugin.php?p=licenseBootstrap',
            'small-icon'  => 'index.php?pf=licenseBootstrap/icon.png',
            'large-icon'  => 'index.php?pf=licenseBootstrap/icon-big.png',
            'permissions' => dcCore::app()->auth->isSuperAdmin(),
            'active_cb'   => [
                'licenseBootstrapBehaviors',
                'adminDashboardFavoritesActive',
            ],
        ]);
    }

    public static function adminDashboardFavoritesActive($request, $params)
    {
        return $request == 'plugin.php'
            && isset($params['p'])
            && $params['p'] == 'licenseBootstrap';
    }

    public static function packmanBeforeCreatePackage($module)
    {
        licenseBootstrap::addLicense($module);
    }
}
