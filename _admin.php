<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

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

    public static function packmanBeforeCreatePackage($core, $module, $a, $b, $c, $d)
    {
        licenseBootstrap::addLicense($core, $module);
    }
}