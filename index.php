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
$action = $_POST['action'] ?? '';
$type   = isset($_POST['type']) && in_array($_POST['type'], ['plugins', 'themes']) ? $_POST['type'] : '';

# Settings
$s = dcCore::app()->blog->settings->addNamespace(basename(__DIR__));

# Modules
if (!isset(dcCore::app()->themes)) {
    dcCore::app()->themes = new dcThemes();
    dcCore::app()->themes->loadModules(dcCore::app()->blog->themes_path, null);
}
$themes  = dcCore::app()->themes;
$plugins = dcCore::app()->plugins;

# Rights
$is_editable = !empty($type)
    && !empty($_POST['modules'])
    && is_array($_POST['modules']);

# Actions
try {
    # Add license to modules
    if ($action == 'addlicense' && $is_editable) {
        $modules = array_keys($_POST['modules']);

        foreach ($modules as $id) {
            if (!${$type}->moduleExists($id)) {
                throw new Exception('No such module');
            }

            $module         = ${$type}->getModules($id);
            $module['id']   = $id;
            $module['type'] = $type == 'themes' ? 'theme' : 'plugin';

            licenseBootstrap::addLicense($module);
        }

        dcAdminNotices::addSuccessNotice(
            __('License successfully added.')
        );
        http::redirect(
            empty($_POST['redir']) ?
            dcCore::app()->admin->getPageURL() : $_POST['redir']
        );
    }
} catch(Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

# Display
echo
'<html><head><title>' . __('License bootstrap') . '</title>' .
dcPage::jsPageTabs() .
dcPage::jsModuleLoad(basename(__DIR__) . '/js/licensebootstrap.js') .

# --BEHAVIOR-- licenseBootstrapAdminHeader
dcCore::app()->callBehavior('licenseBootstrapAdminHeader') .

'</head><body>' .

dcPage::breadcrumb(
    [
        __('Plugins')           => '',
        __('License bootstrap') => '',
    ]
) .
dcPage::notices();

libLicenseBootstrap::modules(
    $plugins->getModules(),
    'plugins',
    __('Installed plugins')
);

libLicenseBootstrap::modules(
    $themes->getModules(),
    'themes',
    __('Installed themes')
);

dcPage::helpBlock('licenseBootstrap');

echo
'</body></html>';
