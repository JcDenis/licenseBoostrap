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
use dcNsProcess;
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Div,
    Fieldset,
    Label,
    Legend,
    Para,
    Radio,
    Textarea
};
use Exception;

class Config extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init == defined('DC_CONTEXT_ADMIN')
            && dcCore::app()->auth?->isSuperAdmin();

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        if (empty($_POST['save'])) {
            return true;
        }

        $s = Settings::init();

        # -- Set settings --
        try {
            $license_name = $_POST['lb_license_name'];
            $license_head = Utils::gethead(
                $license_name,
                !empty($_POST['lb_license_head_' . $license_name]) ? $_POST['lb_license_head_' . $license_name] : ''
            );

            $s->writeSetting('hide_distrib', !empty($_POST['lb_hide_distrib']));
            $s->writeSetting('overwrite', !empty($_POST['lb_overwrite']));
            $s->writeSetting('write_full', !empty($_POST['lb_write_full']));
            $s->writeSetting('write_php', !empty($_POST['lb_write_php']));
            $s->writeSetting('write_js', !empty($_POST['lb_write_js']));
            $s->writeSetting('exclude_locales', !empty($_POST['lb_exclude_locales']));
            $s->writeSetting('license_name', Utils::getName($license_name));
            $s->writeSetting('license_head', Utils::encode($license_head));

            dcPage::addSuccessNotice(
                __('Configuration has been successfully updated.')
            );
            dcCore::app()->adminurl?->redirect('admin.plugins', [
                'module' => My::id(),
                'conf'   => '1',
                'redir'  => dcCore::app()->admin->__get('list')->getRedir(),
            ]);
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        $s = Settings::init();

        $licences = [];
        foreach (Utils::getLicenses() as $name) {
            $check = false;
            $head  = Utils::getHead($name);
            if ($name == Utils::getName($s->license_name)) {
                $check = true;
                $head  = Utils::getHead($name, Utils::getHead($s->license_name, Utils::decode($s->license_head)));
            }
            $licences[] = (new Para())->items([
                (new Radio(['lb_license_name', 'license_' . $name], $check))->value($name),
                (new Label(sprintf(__('License %s:'), $name), Label::OUTSIDE_LABEL_AFTER))->for('license_' . $name)->class('classic'),
            ]);
            $licences[] = (new Para())->items([
                (new Textarea('lb_license_head_' . $name, $head))->class('maximal')->cols(50)->rows(10),
            ]);
        }

        echo
        (new Div())->items([
            (new Fieldset())->class('fieldset')->legend((new Legend(__('Files'))))->fields([
                // hide_distrib
                (new Para())->items([
                    (new Checkbox('lb_hide_distrib', $s->hide_distrib))->value(1),
                    (new Label(__('Hide distributed modules from lists'), Label::OUTSIDE_LABEL_AFTER))->for('lb_hide_distrib')->class('classic'),
                ]),
                // overwrite
                (new Para())->items([
                    (new Checkbox('lb_overwrite', $s->overwrite))->value(1),
                    (new Label(__('Overwrite existing licenses'), Label::OUTSIDE_LABEL_AFTER))->for('lb_overwrite')->class('classic'),
                ]),
                // write_full
                (new Para())->items([
                    (new Checkbox('lb_write_full', $s->write_full))->value(1),
                    (new Label(__('Add full LICENSE file to module root'), Label::OUTSIDE_LABEL_AFTER))->for('lb_write_full')->class('classic'),
                ]),
                // write_php
                (new Para())->items([
                    (new Checkbox('lb_write_php', $s->write_php))->value(1),
                    (new Label(__('Add license block to PHP files'), Label::OUTSIDE_LABEL_AFTER))->for('lb_write_php')->class('classic'),
                ]),
                // write_js
                (new Para())->items([
                    (new Checkbox('lb_write_js', $s->write_js))->value(1),
                    (new Label(__('Add license block to JS files'), Label::OUTSIDE_LABEL_AFTER))->for('lb_write_js')->class('classic'),
                ]),
                // exclude_locales
                (new Para())->items([
                    (new Checkbox('lb_exclude_locales', $s->exclude_locales))->value(1),
                    (new Label(__('Do not add license block to files from locales folder'), Label::OUTSIDE_LABEL_AFTER))->for('lb_exclude_locales')->class('classic'),
                ]),
            ]),
            (new Fieldset())->class('fieldset')->legend((new Legend(__('Licenses'))))->fields($licences),
        ])->render();
    }
}
