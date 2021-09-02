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

dcPage::checkSuper();

# Queries
$p_url = 'plugin.php?p=licenseBootstrap';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$type = isset($_POST['type']) && in_array($_POST['type'], array('plugins', 'themes')) ? $_POST['type'] : '';

# Settings
$core->blog->settings->addNamespace('licenseBootstrap');
$s = $core->blog->settings->licenseBootstrap;

# Modules
if (!isset($core->themes)) {
    $core->themes = new dcThemes($core);
    $core->themes->loadModules($core->blog->themes_path, null);
}
$themes = $core->themes;
$plugins = $core->plugins;

# Rights
$is_editable =
    !empty($type)
    && !empty($_POST['modules']) 
    && is_array($_POST['modules']);

# Actions
try
{
    # Add license to modules
    if ($action == 'addlicense' && $is_editable) {

        $modules = array_keys($_POST['modules']);

        foreach ($modules as $id) {

            if (!${$type}->moduleExists($id)) {
                throw new Exception('No such module');
            }

            $module = ${$type}->getModules($id);
            $module['id'] = $id;
            $module['type'] = $type == 'themes' ? 'theme' : 'plugin';

            licenseBootstrap::addLicense($core, $module);
        }

        dcPage::addSuccessNotice(
            __('License successfully added.')
        );
        http::redirect(empty($_POST['redir']) ? 
            $p_url : $_POST['redir']
        );
    }
}
catch(Exception $e) {
    $core->error->add($e->getMessage());
}

# Display
echo 
'<html><head><title>' . __('License bootstrap') . '</title>' .
dcPage::jsPageTabs() .
dcPage::jsLoad('index.php?pf=licenseBootstrap/js/licensebootstrap.js') .

# --BEHAVIOR-- licenseBootstrapAdminHeader
$core->callBehavior('licenseBootstrapAdminHeader', $core) .

'</head><body>' .

dcPage::breadcrumb(
    array(
        __('Plugins') => '',
        __('License bootstrap') => ''
    )
) .
dcPage::notices();

libLicenseBootstrap::modules(
    $core,
    $plugins->getModules(),
    'plugins',
    __('Installed plugins')
);

libLicenseBootstrap::modules(
    $core,
    $themes->getModules(),
    'themes',
    __('Installed themes')
);

dcPage::helpBlock('licenseBootstrap');

echo 
'</body></html>';