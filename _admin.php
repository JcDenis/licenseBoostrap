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

$core->blog->settings->addNamespace('licenseBootstrap');

$core->addBehavior('adminDashboardFavorites', [
    'licenseBootstrapBehaviors', 'adminDashboardFavorites'
]);

$core->addBehavior('packmanBeforeCreatePackage', [
    'licenseBootstrapBehaviors', 'packmanBeforeCreatePackage'
]);

$_menu['Plugins']->addItem(
    __('License bootstrap'),
    'plugin.php?p=licenseBootstrap',
    'index.php?pf=licenseBootstrap/icon.png',
    preg_match(
        '/plugin.php\?p=licenseBootstrap(&.*)?$/',
        $_SERVER['REQUEST_URI']
    ),
    $core->auth->isSuperAdmin()
);

class licenseBootstrapBehaviors
{
    public static function adminDashboardFavorites($core, $favs)
    {
        $favs->register('licenseBootstrap', array(
            'title'     => __('License bootstrap'),
            'url'       => 'plugin.php?p=licenseBootstrap',
            'small-icon'    => 'index.php?pf=licenseBootstrap/icon.png',
            'large-icon'    => 'index.php?pf=licenseBootstrap/icon-big.png',
            'permissions'   => $core->auth->isSuperAdmin(),
            'active_cb' => array(
                'licenseBootstrapBehaviors', 
                'adminDashboardFavoritesActive'
            )
        ));
    }

    public static function adminDashboardFavoritesActive($request, $params)
    {
        return $request == 'plugin.php' 
            && isset($params['p']) 
            && $params['p'] == 'licenseBootstrap';
    }

    public static function packmanBeforeCreatePackage($core, $module)
    {
        licenseBootstrap::addLicense($core, $module);
    }
}