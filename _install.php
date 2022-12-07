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
    # Grab info
    $mod_id = basename(__DIR__);
    $dc_min = dcCore::app()->plugins->moduleInfo($mod_id, 'requires')[0][1];

    # Check module version
    if (version_compare(
        dcCore::app()->getVersion($mod_id),
        dcCore::app()->plugins->moduleInfo($mod_id, 'version'),
        '>='
    )) {
        return null;
    }

    # Check Dotclear version
    if (!method_exists('dcUtils', 'versionsCompare')
     || dcUtils::versionsCompare(DC_VERSION, $dc_min, '<', false)) {
        throw new Exception(sprintf(
            '%s requires Dotclear %s',
            $mod_id,
            $dc_min
        ));
    }

    # Set module settings
    dcCore::app()->blog->settings->addNamespace($mod_id);
    foreach ($mod_conf as $v) {
        dcCore::app()->blog->settings->{$mod_id}->put(
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
