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

class libLicenseBootstrap
{

    public static function modules($core, $modules, $type, $title)
    {
        $type = $type == 'themes' ? 'themes' : 'plugins';

        echo 
        '<div class="multi-part" ' .
        'id="packman-' . $type . '" title="' . $title . '">' .
        '<h3>' . $title . '</h3>';

        if (empty($modules) && !is_array($modules)) {
            echo 
            '<p><strong>' . __('There are no modules.') . '</strong></p>' .
            '<div>';

            return null;
        }

        echo
        '<form action="plugin.php" method="post">' .
        '<table class="clear"><tr>' .
        '<th class="nowrap">' . __('Id') .'</th>' .
        '<th class="nowrap">' . __('Version') . '</th>' .
        '<th class="nowrap maximal">' . __('Name') . '</th>' .
        '<th class="nowrap">' . __('Root') . '</th>' .
        '</tr>';

        foreach (self::sort($modules) as $id => $module) {  
            echo
            '<tr class="line">' .
            '<td class="nowrap"><label class="classic">' .
                form::checkbox(array('modules[' . html::escapeHTML($id) . ']'), 1) .
                html::escapeHTML($id) .
            '</label></td>'.
            '<td class="nowrap count">' .
                html::escapeHTML($module['version']) .
            '</td>' .
            '<td class="nowrap maximal">' .
                __(html::escapeHTML($module['name'])) .
            '</td>' .
            '<td class="nowrap">' .
                dirname(path::real($module['root'], false)) .
            '</td>' .
            '</tr>';
        }

        echo
        '</table>' .
        '<p class="checkboxes-helpers"></p>' .
        '<p>' .
        (!empty($_REQUEST['redir']) ?
            form::hidden(
                array('redir'),
                html::escapeHTML($_REQUEST['redir'])
            ) : ''
        ).
        form::hidden(array('p'), 'licenseBootstrap') .
        form::hidden(array('type'), $type) .
        form::hidden(array('action'),'addlicense') .
        '<input type="submit" name="addlicense" value="' .
         __('Add license to selected modules') . '" />' .
        $core->formNonce() . '</p>' .
        '</form>' .

        '</div>';
    }

    protected static function sort($modules)
    {
        $sorter = array();
        foreach($modules as $id => $module) {
            $sorter[$id] = $id;
        }
        array_multisort($sorter, SORT_ASC, $modules);

        return $modules;
    }
}