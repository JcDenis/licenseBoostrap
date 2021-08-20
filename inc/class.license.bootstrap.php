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

class licenseBootstrap
{
    protected static $licenses = array();

    /**
     * Add license to a module.
     *
     * Note that you must check if module exists
     * before calling this method.
     * 
     * @param object $core   dcCore instance
     * @param array  $module Module info
     */
    public static function addLicense($core, $module)
    {
        $s = $core->blog->settings->licenseBootstrap;

        # --BEHAVIOR-- licenseBootstrapBeforeAddLicense
        $core->callBehavior(
            'licenseBootstrapBeforeAddLicense',
            $core,
            $module
        );

        if ($s->write_full) {
            licenseBootstrap::writeFullContent(
                $s->license_name,
                $module,
                $s->overwrite
            );
        }
        licenseBootstrap::writeHeadContent(
            $s->license_name,
            licenseBootstrap::decode($s->license_head),
            $module,
            $core->auth,
            $s->overwrite,
            $s->write_php,
            $s->write_js,
            $s->exclude_locales
        );

        # --BEHAVIOR-- licenseBootstrapAfterAddLicense
        $core->callBehavior(
            'licenseBootstrapAfterAddLicense',
            $core,
            $module
        );
    }

    /**
     * Get available licenses.
     * 
     * @return array List of licenses names
     */
    public static function getLicenses()
    {
        if (empty(licenseBootstrap::$licenses)) {
            $file_reg = '/^([a-z0-9]+)\.head\.txt$/';
            $res = array();
            foreach (files::scandir(dirname(__FILE__) . '/licenses/') as $file) {
                if (preg_match($file_reg, $file, $matches)) {
                    $res[] = $matches[1];
                }
            }
            licenseBootstrap::$licenses = $res;
        }

        return licenseBootstrap::$licenses;
    }

    /**
     * Get license name.
     *
     * Check requested license name and return existing one.
     * 
     * @param  string $name License name
     * @return string       License name
     */
    public static function getName($name='gpl2')
    {
        return in_array($name, self::getLicenses()) ? $name : 'gpl2';
    }
 
    /**
     * Get license header.
     * 
     * @param  string $name    License name
     * @param  string $content Header content
     * @return string          Header content
     */
    public static function getHead($name='gpl2', $content='')
    {
        if (!in_array($name, self::getLicenses())) {
            $name = 'gpl2';
            $content = '';
        }

        return empty($content) ? 
            self::getContent($name, 'head') : $content;
    }

    /**
     * Get full license.
     * 
     * @param  string $name License name
     * @return string       Full license content
     */
    public static function getFull($name='gpl2')
    {
        return self::getContent($name, 'full');
    }

    /**
     * Get original license content.
     * 
     * @param  string $name License name
     * @param  string $part License part (head or full)
     * @return string       License content
     */
    public static function getContent($name='gpl2', $part='head')
    {
        if (!in_array($name, self::getLicenses())) {
            $name = 'gpl2';
        }
        if (!in_array($part, array('head', 'full'))) {
            $part = 'head';
        }

        return file_get_contents(
            dirname(__FILE__) . '/licenses/' . $name . '.' . $part . '.txt'
        );
    }

    /**
     * Write license block into module files headers
     * 
     * @param  string  $name       License name
     * @param  string  $content    License block content
     * @param  array   $module     Module info
     * @param  object  $user       dcAuth instance
     * @param  boolean $overwrite  Overwrite existing license
     * @param  boolean $php        Write license in PHP
     * @param  boolean $js         Write license in JS
     * @param  boolean $locales    Excludes locales folder
     */
    public static function writeHeadContent($name, $content, $module, $user, $overwrite, $php, $js, $locales)
    {
        if (!isset($module['root']) || !is_writable($module['root'])) {
            throw new Exception();
        }

        $license = self::replaceInfo(
            self::getHead($name, $content),
            $module,
            $user
        );

        foreach(self::getModuleFiles($module['root']) as $file) {

            if ($locales && preg_match('/(\.lang\.php|libs)/', $file)) {
                continue;
            }

            $path = $module['root'] . '/' . $file;
            $extension = files::getExtension($file);

            if ($php && $extension == 'php') {
                file_put_contents(
                    $file,
                    self::replacePhpContent(
                        file_get_contents($file),
                        $license,
                        $overwrite
                    )
                );
            }
            elseif ($js && $extension == 'js') {
                file_put_contents(
                    $file,
                    self::replaceJsContent(
                        file_get_contents($file),
                        $license,
                        $overwrite
                    )
                );
            }
        }
    }

    /**
     * Write full license file
     * 
     * @param  string  $name      License name
     * @param  array   $module    Module info
     * @param  boolean $overwrite Overwrite existing license
     */
    public static function writeFullContent($name, $module, $overwrite)
    {
        if (!isset($module['root']) || !is_writable($module['root'])) {
            throw new Exception();
        }
        if (file_exists($module['root'] . '/LICENSE') && !$overwrite) {

            return null;
        }

        file_put_contents(
            $module['root'] . '/LICENSE',
            self::getFull($name)
        );
    }

    /**
     * Replace license block in PHP file
     * 
     * @param  string  $content   File content
     * @param  string  $license   License content
     * @param  boolean $overwrite Overwrite existing license
     * @return string             File content
     */
    protected static function replacePhpContent($content, $license, $overwrite)
    {
        $clean = preg_replace(
            '/((# -- BEGIN LICENSE BLOCK ([-]+))(.*?)' .
            '(# -- END LICENSE BLOCK ([-]+))([\n|\r\n]+))/msi',
            '',
            $content
        );

        if ($clean != $content && !$overwrite) {

            return $content;
        }

        return preg_replace(
            '/(\<\?php)/',
            '<?php' .
            "\r\n# -- BEGIN LICENSE BLOCK ----------------------------------\r\n" .
            "#\r\n" .
            '# ' . str_replace("\n", "\n# ", trim($license)).
            "\r\n#" .
            "\r\n# -- END LICENSE BLOCK ------------------------------------\r\n",
            $clean,
            1
        );
    }

    /**
     * Replace license block in JS files
     * 
     * @param  string  $content   File content
     * @param  string  $license   License content
     * @param  boolean $overwrite Overwrite existing license
     * @return string             File content
     */
    protected static function replaceJsContent($content, $license, $overwrite)
    {
        $clean = preg_replace(
            '/((\/\* -- BEGIN LICENSE BLOCK ([-]+))(.*?)' .
            '(\* -- END LICENSE BLOCK ([-]+)\*\/)([\n|\r\n]+))/msi',
            '',
            $content
        );

        if ($clean != $content && !$overwrite) {

            return $content;
        }

        return 
        "/* -- BEGIN LICENSE BLOCK ----------------------------------\r\n" .
        " *\r\n" .
        ' * ' . str_replace("\n", "\n * ", trim($license)).
        "\r\n *" .
        "\r\n * -- END LICENSE BLOCK ------------------------------------*/\r\n\r\n" .
        $clean;
    }

    /**
     * Replace info in license
     * 
     * @param  string $content License content
     * @param  array $module   Module info
     * @param  array $user     User info
     * @return string          License content
     */
    protected static function replaceInfo($content, $module, $user)
    {
        return str_replace(
            array(
                '%year%',
                '%module_id%',
                '%module_name%',
                '%module_author%',
                '%module_type%',
                '%user_cn%',
                '%user_name%',
                '%user_email%',
                '%user_url%'
            ),
            array(
                date('Y'),
                $module['id'],
                $module['name'],
                $module['author'],
                $module['type'],
                $user->getInfo('user_cn'),
                $user->getinfo('user_name'),
                $user->getInfo('user_email'),
                $user->getInfo('user_url')
            ),
            $content
        );
    }

    /**
     * Get list of module files
     * 
     * @param  string $path Path to scan
     * @param  string $dir  Ignore
     * @param  array  $res  Ignore
     * @return array        List of files
     */
    protected static function getModuleFiles($path, $dir='', $res=array())
    {
        $path = path::real($path);
        if (!is_dir($path) || !is_readable($path)) {

            return array();
        }

        if (!$dir) {
            $dir = $path;
        }
        
        $files = files::scandir($path);
        
        foreach($files AS $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }

            if (is_dir($path . '/' . $file)) {
                $res = self::getModuleFiles(
                    $path . '/' . $file, $dir . '/' . $file,
                    $res
                );
            }
            else {
                $res[] = empty($dir) ? $file : $dir . '/' . $file;
            }
        }

        return $res;
    }

    public static function encode($a)
    {
        return base64_encode(serialize($a));
    }
    
    public static function decode($a)
    {
        return unserialize(base64_decode($a));
    }
}