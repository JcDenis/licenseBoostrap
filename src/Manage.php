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
use dcPage;
use dcThemes;
use dcNsProcess;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Hidden,
    Label,
    Para,
    Submit,
    Text
};
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Network\Http;
use Exception;

class Manage extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && dcCore::app()->auth?->isSuperAdmin();

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        $type = in_array($_POST['type'] ?? '', ['plugins', 'themes']) ? $_POST['type'] : '';

        if (($_POST['action'] ?? '') != 'addlicense'
         || empty($type)
         || empty($_POST['modules'])
         || !is_array($_POST['modules'])
        ) {
            return true;
        }

        $m = self::loadModules();

        # Actions
        try {
            $modules = array_values($_POST['modules']);

            foreach ($modules as $id) {
                if (!$m[$type]->moduleExists($id)) {
                    throw new Exception('No such module');
                }

                $module         = $m[$type]->getModules($id);
                $module['id']   = $id;
                $module['type'] = $type == 'themes' ? 'theme' : 'plugin';

                Utils::addLicense($module);
            }

            dcPage::addSuccessNotice(
                __('License successfully added.')
            );
            Http::redirect(
                empty($_POST['redir']) ?
                dcCore::app()->admin->getPageURL() : $_POST['redir']
            );
        } catch(Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        $m = self::loadModules();

        # Display
        dcPage::openModule(
            My::name(),
            dcPage::jsPageTabs() .
            dcPage::jsModuleLoad(My::id() . '/js/licensebootstrap.js') .

            # --BEHAVIOR-- licenseBootstrapAdminHeader
            dcCore::app()->callBehavior('licenseBootstrapAdminHeader')
        );

        echo
        dcPage::breadcrumb([
            __('Plugins') => '',
            My::name()    => '',
        ]) .
        dcPage::notices();

        self::displayModulesList(
            $m['plugins']->getModules(),
            'plugins',
            __('Installed plugins')
        );

        self::displayModulesList(
            $m['themes']->getModules(),
            'themes',
            __('Installed themes')
        );

        dcPage::helpBlock('licenseBootstrap');

        dcPage::closeModule();
    }

    private static function displayModulesList(array $modules, string $type, string $title): void
    {
        if (Settings::init()->hide_distrib) {
            $modules = array_diff_key($modules, array_flip(array_values(array_merge(explode(',', DC_DISTRIB_PLUGINS), explode(',', DC_DISTRIB_THEMES)))));
        }

        echo
        '<div class="multi-part" ' .
        'id="packman-' . $type . '" title="' . $title . '">' .
        '<h3>' . $title . '</h3>';

        if (empty($modules)) {
            echo
            '<p><strong>' . __('There are no modules.') . '</strong></p>' .
            '<div>';

            return;
        }

        echo
        '<form action="plugin.php" method="post">' .
        '<table class="clear"><tr>' .
        '<th class="nowrap">' . __('Id') . '</th>' .
        '<th class="nowrap">' . __('Version') . '</th>' .
        '<th class="nowrap maximal">' . __('Name') . '</th>' .
        '<th class="nowrap">' . __('Root') . '</th>' .
        '</tr>';

        foreach (self::sortModules($modules) as $id => $module) {
            echo
            '<tr class="line">' .
            '<td class="nowrap">' .
                (new Para())->items([
                    (new Checkbox(['modules[]', 'modules_' . Html::escapeHTML($id)], false))->value(Html::escapeHTML($id)),
                    (new Label(Html::escapeHTML($id), Label::OUTSIDE_LABEL_AFTER))->for('modules_' . Html::escapeHTML($id))->class('classic'),
                ])->render() .
            '</label></td>' .
            '<td class="nowrap count">' .
                Html::escapeHTML($module['version']) .
            '</td>' .
            '<td class="nowrap maximal">' .
                __(Html::escapeHTML($module['name'])) .
            '</td>' .
            '<td class="nowrap">' .
                dirname((string) Path::real($module['root'], false)) .
            '</td>' .
            '</tr>';
        }

        echo
        '</table>' .
        '<p class="checkboxes-helpers"></p>' .
        (new Para())->items([
            (new Hidden(['redir'], empty($_REQUEST['redir']) ? '' : Html::escapeHTML($_REQUEST['redir']))),
            (new Hidden(['p'], My::id())),
            (new Hidden(['type'], $type)),
            (new Hidden(['action'], 'addlicense')),
            (new Submit('addlicense'))->accesskey('s')->value(__('Add license to selected modules')),
            dcCore::app()->formNonce(false),
        ])->render() .
        '</form>' .

        '</div>';
    }

    private static function sortModules(array $modules): array
    {
        $sorter = [];
        foreach ($modules as $id => $module) {
            $sorter[$id] = $id;
        }
        array_multisort($sorter, SORT_ASC, $modules);

        return $modules;
    }

    private static function loadModules(): array
    {
        if (!(dcCore::app()->themes instanceof dcThemes)) {
            dcCore::app()->themes = new dcThemes();
            dcCore::app()->themes->loadModules((string) dcCore::app()->blog?->themes_path, null);
        }

        return [
            'themes'  => dcCore::app()->themes,
            'plugins' => dcCore::app()->plugins,
        ];
    }
}
