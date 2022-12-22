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
if (!defined('DC_CONTEXT_MODULE')) {
    return null;
}

$redir = empty($_REQUEST['redir']) ?
    dcCore::app()->admin->__get('list')->getURL() . '#plugins' : $_REQUEST['redir'];

# -- Get settings --
$s = dcCore::app()->blog->settings->addNamespace(basename(__DIR__));

$lb_overwrite       = (bool) $s->get('overwrite');
$lb_write_full      = (bool) $s->get('write_full');
$lb_write_php       = (bool) $s->get('write_php');
$lb_write_js        = (bool) $s->get('write_js');
$lb_exclude_locales = (bool) $s->get('exclude_locales');
$lb_license_name    = licenseBootstrap::getName($s->get('license_name'));
$lb_license_head    = licenseBootstrap::gethead($s->get('license_name'), licenseBootstrap::decode($s->get('license_head')));

# -- Set settings --
if (!empty($_POST['save'])) {
    try {
        $lb_overwrite       = !empty($_POST['lb_overwrite']);
        $lb_write_full      = !empty($_POST['lb_write_full']);
        $lb_write_php       = !empty($_POST['lb_write_php']);
        $lb_write_js        = !empty($_POST['lb_write_js']);
        $lb_exclude_locales = !empty($_POST['lb_exclude_locales']);
        $lb_license_name    = $_POST['lb_license_name'];
        $lb_license_head    = licenseBootstrap::gethead($lb_license_name, !empty($_POST['lb_license_head_' . $lb_license_name]) ? $_POST['lb_license_head_' . $lb_license_name] : '');

        $s->put('overwrite', $lb_overwrite);
        $s->put('write_full', $lb_write_full);
        $s->put('write_php', $lb_write_php);
        $s->put('write_js', $lb_write_js);
        $s->put('exclude_locales', $lb_exclude_locales);
        $s->put('license_name', licenseBootstrap::getName($lb_license_name));
        $s->put('license_head', licenseBootstrap::encode($lb_license_head));

        dcPage::addSuccessNotice(
            __('Configuration has been successfully updated.')
        );
        http::redirect(
            dcCore::app()->admin->__get('list')->getURL('module=' . basename(__DIR__) . '&conf=1&redir=' .
            dcCore::app()->admin->__get('list')->getRedir())
        );
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

# -- Display form --
echo '
<div class="fieldset">
<h4>' . __('Files') . '</h4>

<p><label class="classic" for="lb_overwrite">' .
form::checkbox('lb_overwrite', 1, $lb_overwrite) . ' ' .
__('Overwrite existing licenses') .
'</label></p>

<p><label class="classic" for="lb_write_full">' .
form::checkbox('lb_write_full', 1, $lb_write_full) . ' ' .
__('Add full LICENSE file to module root') .
'</label></p>

<p><label class="classic" for="lb_write_php">' .
form::checkbox('lb_write_php', 1, $lb_write_php) . ' ' .
__('Add license block to PHP files') .
'</label></p>

<p><label class="classic" for="lb_write_js">' .
form::checkbox('lb_write_js', 1, $lb_write_js) . ' ' .
__('Add license block to JS files') .
'</label></p>

<p><label class="classic" for="lb_exclude_locales">' .
form::checkbox('lb_exclude_locales', 1, $lb_exclude_locales) . ' ' .
__('Do not add license block to files from locales folder') .
'</label></p>

</div>

<div class="fieldset">
<h4>' . __('Licenses') . '</h4>';

foreach (licenseBootstrap::getLicenses() as $name) {
    $check = false;
    $head  = licenseBootstrap::getHead($name);
    if ($name == $lb_license_name) {
        $check = true;
        $head  = licenseBootstrap::getHead($name, $lb_license_head);
    }
    echo '
    <p><label class="classic" for="license_' . $name . '">' .
    form::radio(['lb_license_name', 'license_' . $name], $name, $check) . ' ' .
    sprintf(__('License %s:'), $name) . '</label></p>
    <p class="area">' .
    form::textarea('lb_license_head_' . $name, 50, 10, html::escapeHTML($head)) . '
    </p>';
}

echo '
</div>';
