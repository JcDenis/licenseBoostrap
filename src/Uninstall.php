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
declare(strict_types=1);

namespace Dotclear\Plugin\licenseBootstrap;

class Uninstall
{
    protected static $init = false;

    public static function init(): bool
    {
        self::$init = defined('DC_RC_PATH');

        return self::$init;
    }

    public static function process($uninstaller): ?bool
    {
        if (!self::$init) {
            return false;
        }

        $uninstaller->addUserAction(
            /* type */
            'settings',
            /* action */
            'delete_all',
            /* ns */
            My::id(),
            /* desc */
            __('delete all settings')
        );

        $uninstaller->addUserAction(
            /* type */
            'plugins',
            /* action */
            'delete',
            /* ns */
            My::id(),
            /* desc */
            __('delete plugin files')
        );

        $uninstaller->addDirectAction(
            /* type */
            'settings',
            /* action */
            'delete_all',
            /* ns */
            My::id(),
            /* desc */
            sprintf(__('delete all %s settings'), My::id())
        );

        $uninstaller->addDirectAction(
            /* type */
            'plugins',
            /* action */
            'delete',
            /* ns */
            My::id(),
            /* desc */
            sprintf(__('delete %s plugin files'), My::id())
        );

        return true;
    }
}
