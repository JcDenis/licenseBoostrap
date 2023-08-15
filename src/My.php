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

use dcCore;
use Dotclear\Module\MyPlugin;

/**
 * This module definitions.
 */
class My extends MyPlugin
{
    /** @var    string  Licenses default templates folder name */
    public const TEMPLATE_FOLDER = 'licenses';

    protected static function checkCustomContext(int $context): ?bool
    {
        return $context === My::INSTALL ? null :
            defined('DC_CONTEXT_ADMIN') && dcCore::app()->auth->isSuperAdmin();
    }
}
