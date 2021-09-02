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

$dc_min = '2.6';
$mod_id = 'licenseBootstrap';
$mod_conf = array(
    array(
        'overwrite',
        'Overwrite existing licence',
        false,
        'boolean'
    ),
    array(
        'write_full',
        'Add complete licence file',
        true,
        'boolean'
    ),
    array(
        'write_php',
        'Write license into php files',
        true,
        'boolean'
    ),
    array(
        'write_js',
        'Write license into js files',
        false,
        'boolean'
    ),
    array(
        'exclude_locales',
        'Exclude locales from license',
        true,
        'boolean'
    ),
    array(
        'license_name',
        'License short name',
        'gpl2',
        'string'
    ),
    array(
        'license_head',
        'File header licence text',
        licenseBootstrap::encode(
            licenseBootstrap::getHead('gpl2')
        ),
        'string'
    ),
    array(
        'behavior_packman',
        'Add LicenceBootstrap to plugin pacKman',
        false,
        'boolean'
    )
);

# -- Nothing to change below --

try {

    # Check module version
    if (version_compare(
        $core->getVersion($mod_id),
        $core->plugins->moduleInfo($mod_id, 'version'),
        '>='
    )) {

        return null;
    }

    # Check Dotclear version
    if (!method_exists('dcUtils', 'versionsCompare') 
     || dcUtils::versionsCompare(DC_VERSION, $dc_min, '<', false)) {
        throw new Exception(sprintf(
            '%s requires Dotclear %s', $mod_id, $dc_min
        ));
    }

    # Set module settings
    $core->blog->settings->addNamespace($mod_id);
    foreach($mod_conf as $v) {
        $core->blog->settings->{$mod_id}->put(
            $v[0], $v[2], $v[3], $v[1], false, true
        );
    }

    # Set module version
    $core->setVersion(
        $mod_id,
        $core->plugins->moduleInfo($mod_id, 'version')
    );

    return true;
}
catch (Exception $e) {
    $core->error->add($e->getMessage());

    return false;
}