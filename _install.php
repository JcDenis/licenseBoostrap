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

# -- Module specs --

$mod_conf = [
    [
        'overwrite',
        'Overwrite existing licence',
        false,
        'boolean',
    ],
    [
        'write_full',
        'Add complete licence file',
        true,
        'boolean',
    ],
    [
        'write_php',
        'Write license into php files',
        true,
        'boolean',
    ],
    [
        'write_js',
        'Write license into js files',
        false,
        'boolean',
    ],
    [
        'exclude_locales',
        'Exclude locales from license',
        true,
        'boolean',
    ],
    [
        'license_name',
        'License short name',
        'gpl2',
        'string',
    ],
    [
        'license_head',
        'File header licence text',
        licenseBootstrap::encode(
            licenseBootstrap::getHead('gpl2')
        ),
        'string',
    ],
    [
        'behavior_packman',
        'Add LicenceBootstrap to plugin pacKman',
        false,
        'boolean',
    ],
];

# -- Nothing to change below --

try {
    # Check module version
    if (!dcCore::app()->newVersion(
        basename(__DIR__), 
        dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version')
    )) {
        return null;
    }

    // version < 2022.12.22 : upgrade settings ns and array
    $current = dcCore::app()->getVersion(basename(__DIR__));
    if ($current && version_compare($current, '2022.12.22', '<')) {
        $record = dcCore::app()->con->select(
            'SELECT * FROM ' . dcCore::app()->prefix . dcNamespace::NS_TABLE_NAME . ' ' .
            "WHERE setting_ns = 'licenseBootstrap' "
        );
        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcNamespace::NS_TABLE_NAME);
        while ($record->fetch()) {
            if (in_array($record->setting_id, ['license_head'])) {
                $cur->setting_value = (string) unserialize(base64_decode($record->setting_value));
            }
            $cur->setting_ns = basename(__DIR__);
            $cur->update(
                "WHERE setting_id = '" . $record->setting_id . "' and setting_ns = 'licenseBootstrap' " .
                'AND blog_id ' . (null === $record->blog_id ? 'IS NULL ' : ("= '" . dcCore::app()->con->escape($record->blog_id) . "' "))
            );
            $cur->clean();
        }
    }
    # Set module settings
    dcCore::app()->blog->settings->addNamespace(basename(__DIR__));
    foreach ($mod_conf as $v) {
        dcCore::app()->blog->settings->__get(basename(__DIR__))->put(
            $v[0],
            $v[2],
            $v[3],
            $v[1],
            false,
            true
        );
    }

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());

    return false;
}
