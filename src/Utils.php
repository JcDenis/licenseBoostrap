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
declare(strict_types=1);

namespace Dotclear\Plugin\licenseBootstrap;

use dcAuth;
use dcCore;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Exception;

class Utils
{
    protected static array $licenses = [];

    /**
     * Add license to a module.
     *
     * Note that you must check if module exists
     * before calling this method.
     *
     * @param array  $module Module info
     */
    public static function addLicense(array $module): void
    {
        if (is_null(dcCore::app()->auth)) {
            return;
        }

        $s = Settings::init();

        # --BEHAVIOR-- licenseBootstrapBeforeAddLicense
        dcCore::app()->callBehavior(
            'licenseBootstrapBeforeAddLicense',
            $module
        );

        if ($s->write_full) {
            self::writeFullContent(
                $s->license_name,
                $module,
                $s->overwrite
            );
        }
        self::writeHeadContent(
            $s->license_name,
            self::decode($s->license_head),
            $module,
            dcCore::app()->auth,
            $s->overwrite,
            $s->write_php,
            $s->write_js,
            $s->exclude_locales
        );

        # --BEHAVIOR-- licenseBootstrapAfterAddLicense
        dcCore::app()->callBehavior(
            'licenseBootstrapAfterAddLicense',
            $module
        );
    }

    /**
     * Get available licenses.
     *
     * @return array List of licenses names
     */
    public static function getLicenses(): array
    {
        if (empty(self::$licenses)) {
            $file_reg = '/^([a-z0-9]+)\.head\.txt$/';
            $res      = [];
            foreach (Files::scandir(__DIR__ . DIRECTORY_SEPARATOR . My::TEMPLATE_FOLDER . DIRECTORY_SEPARATOR) as $file) {
                if (preg_match($file_reg, $file, $matches)) {
                    $res[] = $matches[1];
                }
            }
            self::$licenses = $res;
        }

        return self::$licenses;
    }

    /**
     * Get license name.
     *
     * Check requested license name and return existing one.
     *
     * @param  string $name License name
     * @return string       License name
     */
    public static function getName(string $name = 'gpl2'): string
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
    public static function getHead(string $name = 'gpl2', string $content = ''): string
    {
        if (!in_array($name, self::getLicenses())) {
            $name    = 'gpl2';
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
    public static function getFull(string $name = 'gpl2'): string
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
    public static function getContent(string $name = 'gpl2', string $part = 'head'): string
    {
        if (!in_array($name, self::getLicenses())) {
            $name = 'gpl2';
        }
        if (!in_array($part, ['head', 'full'])) {
            $part = 'head';
        }

        return (string) file_get_contents(
            implode(DIRECTORY_SEPARATOR, [__DIR__, My::TEMPLATE_FOLDER, $name . '.' . $part . '.txt'])
        );
    }

    /**
     * Write license block into module files headers
     *
     * @param  string  $name       License name
     * @param  string  $content    License block content
     * @param  array   $module     Module info
     * @param  dcAuth  $user       dcAuth instance
     * @param  boolean $overwrite  Overwrite existing license
     * @param  boolean $php        Write license in PHP
     * @param  boolean $js         Write license in JS
     * @param  boolean $locales    Excludes locales folder
     */
    public static function writeHeadContent(string $name, string $content, array $module, dcAuth $user, bool $overwrite, bool $php, bool $js, bool $locales): void
    {
        if (!isset($module['root']) || !is_writable($module['root'])) {
            throw new Exception();
        }

        $license = self::replaceInfo(
            self::getHead($name, $content),
            $module,
            $user
        );

        foreach (self::getModuleFiles($module['root']) as $file) {
            if ($locales && preg_match('/(\.lang\.php|libs)/', $file)) {
                continue;
            }

            $path      = $module['root'] . DIRECTORY_SEPARATOR . $file;
            $extension = Files::getExtension($file);

            if ($php && $extension == 'php') {
                file_put_contents(
                    $file,
                    self::replacePhpContent(
                        (string) file_get_contents($file),
                        $license,
                        $overwrite
                    )
                );
            } elseif ($js && $extension == 'js') {
                file_put_contents(
                    $file,
                    self::replaceJsContent(
                        (string) file_get_contents($file),
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
    public static function writeFullContent(string $name, array $module, bool $overwrite): void
    {
        if (!isset($module['root']) || !is_writable($module['root'])) {
            throw new Exception();
        }
        if (file_exists($module['root'] . DIRECTORY_SEPARATOR . 'LICENSE') && !$overwrite) {
            return;
        }

        file_put_contents(
            $module['root'] . DIRECTORY_SEPARATOR . 'LICENSE',
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
    protected static function replacePhpContent(string $content, string $license, bool $overwrite): string
    {
        $clean = (string) preg_replace(
            '/((# -- BEGIN LICENSE BLOCK ([-]+))(.*?)' .
            '(# -- END LICENSE BLOCK ([-]+))([\n|\r\n]+))/msi',
            '',
            $content
        );

        if ($clean != $content && !$overwrite) {
            return $content;
        }

        return (string) preg_replace(
            '/(\<\?php)/',
            '<?php' .
            "\r\n# -- BEGIN LICENSE BLOCK ----------------------------------\r\n" .
            "#\r\n" .
            '# ' . str_replace("\n", "\n# ", trim($license)) .
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
    protected static function replaceJsContent(string $content, string $license, bool $overwrite): string
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
        ' * ' . str_replace("\n", "\n * ", trim($license)) .
        "\r\n *" .
        "\r\n * -- END LICENSE BLOCK ------------------------------------*/\r\n\r\n" .
        $clean;
    }

    /**
     * Replace info in license
     *
     * @param   string  $content    License content
     * @param   array   $module     Module info
     * @param   dcAuth  $user       User info
     * @return  string              License content
     */
    protected static function replaceInfo(string $content, array $module, dcAuth $user): string
    {
        return str_replace(
            [
                '%year%',
                '%module_id%',
                '%module_name%',
                '%module_author%',
                '%module_type%',
                '%user_cn%',
                '%user_name%',
                '%user_email%',
                '%user_url%',
            ],
            [
                date('Y'),
                $module['id'],
                $module['name'],
                $module['author'],
                $module['type'],
                $user->getInfo('user_cn'),
                $user->getinfo('user_name'),
                $user->getInfo('user_email'),
                $user->getInfo('user_url'),
            ],
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
    protected static function getModuleFiles(string $path, string $dir = '', array $res = []): array
    {
        $path = Path::real($path);
        if ($path === false || !is_dir($path) || !is_readable($path)) {
            return [];
        }

        if (!$dir) {
            $dir = $path;
        }

        $files = Files::scandir($path);

        foreach ($files as $file) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }

            if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                $res = self::getModuleFiles(
                    $path . DIRECTORY_SEPARATOR . $file,
                    $dir . DIRECTORY_SEPARATOR . $file,
                    $res
                );
            } else {
                $res[] = empty($dir) ? $file : $dir . DIRECTORY_SEPARATOR . $file;
            }
        }

        return $res;
    }

    public static function encode(string $a): string
    {
        $r = json_encode($a);

        return $r === false ? '' : $r;
    }

    public static function decode(string $a): string
    {
        $r = json_decode($a, true);

        return $r === false ? '' : $r;
    }
}
