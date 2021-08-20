<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2021 Jean-Christian Denis and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

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