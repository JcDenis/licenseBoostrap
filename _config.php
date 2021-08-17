<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_MODULE')) {

	return null;
}

$redir = empty($_REQUEST['redir']) ? 
	$list->getURL().'#plugins' : $_REQUEST['redir'];

# -- Get settings --
$core->blog->settings->addNamespace('licenseBootstrap');
$s = $core->blog->settings->licenseBootstrap;

$lb_overwrite		= (boolean) $s->overwrite;
$lb_write_full		= (boolean) $s->write_full;
$lb_write_php		= (boolean) $s->write_php;
$lb_write_js		= (boolean) $s->write_js;
$lb_exclude_locales	= (boolean) $s->exclude_locales;
$lb_license_name	= licenseBootstrap::getName($s->license_name);
$lb_license_head	= licenseBootstrap::gethead($s->license_name, licenseBootstrap::decode($s->license_head));

# -- Set settings --
if (!empty($_POST['save'])) {

	try {
		$lb_overwrite		= !empty($_POST['lb_overwrite']);
		$lb_write_full		= !empty($_POST['lb_write_full']);
		$lb_write_php		= !empty($_POST['lb_write_php']);
		$lb_write_js		= !empty($_POST['lb_write_js']);
		$lb_exclude_locales	= !empty($_POST['lb_exclude_locales']);
		$lb_license_name	= $_POST['lb_license_name'];
		$lb_license_head	= licenseBootstrap::gethead($lb_license_name, !empty($_POST['lb_license_head_'.$lb_license_name]) ? $_POST['lb_license_head_'.$lb_license_name] : '');

		$s->put('overwrite',		$lb_overwrite);
		$s->put('write_full',		$lb_write_full);
		$s->put('write_php',		$lb_write_php);
		$s->put('write_js',			$lb_write_js);
		$s->put('exclude_locales',	$lb_exclude_locales);
		$s->put('license_name',		licenseBootstrap::getName($lb_license_name));
		$s->put('license_head',		licenseBootstrap::encode($lb_license_head));

		dcPage::addSuccessNotice(
			__('Configuration has been successfully updated.')
		);
		http::redirect(
			$list->getURL('module=licenseBootstrap&conf=1&redir='.
			$list->getRedir())
		);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# -- Display form --
echo '
<div class="fieldset">
<h4>'.__('Files').'</h4>

<p><label class="classic" for="lb_overwrite">'.
form::checkbox('lb_overwrite', 1, $lb_overwrite).' '.
__('Overwrite existing licenses').
'</label></p>

<p><label class="classic" for="lb_write_full">'.
form::checkbox('lb_write_full', 1, $lb_write_full).' '.
__('Add full LICENSE file to module root').
'</label></p>

<p><label class="classic" for="lb_write_php">'.
form::checkbox('lb_write_php', 1, $lb_write_php).' '.
__('Add license block to PHP files').
'</label></p>

<p><label class="classic" for="lb_write_js">'.
form::checkbox('lb_write_js', 1, $lb_write_js).' '.
__('Add license block to JS files').
'</label></p>

<p><label class="classic" for="lb_exclude_locales">'.
form::checkbox('lb_exclude_locales', 1, $lb_exclude_locales).' '.
__('Do not add license block to files from locales folder').
'</label></p>

</div>

<div class="fieldset">
<h4>'.__('Licenses').'</h4>';

foreach(licenseBootstrap::getLicenses() as $name) {

	$check = false;
	$head = licenseBootstrap::getHead($name);
	if ($name == $lb_license_name) {
		$check = true;
		$head = licenseBootstrap::getHead($name, $lb_license_head);
	}
	echo '
	<p><label class="classic" for="license_'.$name.'">'.
	form::radio(array('lb_license_name', 'license_'.$name), $name, $check).' '.
	sprintf(__('License %s:'),$name).'</label></p>
	<p class="area">'.
	form::textarea('lb_license_head_'.$name, 50, 10, html::escapeHTML($head)).'
	</p>';
}

echo '
</div>

<div class="fieldset">
<h4>'.__('Files').'</h4>

</div>';
