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

class Settings
{
    /** @var Settings self instance */
    private static $settings;

    // Hide distributed modules from lists
    public readonly bool $hide_distrib;

    // Overwrite existing licence
    public readonly bool $overwrite;

    // Add complete licence file
    public readonly bool $write_full;

    // Write license into php files
    public readonly bool $write_php;

    // Write license into js files
    public readonly bool $write_js;

    // Exclude locales from license
    public readonly bool $exclude_locales;

    // License short name
    public readonly string $license_name;

    // File header licence text
    public readonly string $license_head;

    // Add LicenceBootstrap to plugin pacKman
    public readonly bool $behavior_packman;

    /**
     * Constructor set up plugin settings
     */
    public function __construct()
    {
        $s = My::settings();

        $this->hide_distrib     = (bool) ($s?->get('hide_distrib') ?? false);
        $this->overwrite        = (bool) ($s?->get('overwrite') ?? false);
        $this->write_full       = (bool) ($s?->get('write_full') ?? true);
        $this->write_php        = (bool) ($s?->get('pack_overwrite') ?? true);
        $this->write_js         = (bool) ($s?->get('pack_filename') ?? false);
        $this->exclude_locales  = (bool) ($s?->get('exclude_locales') ?? true);
        $this->license_name     = (string) ($s?->get('license_name') ?? 'gpl2');
        $this->license_head     = (string) ($s?->get('license_head') ?? Utils::encode(Utils::getHead('gpl2')));
        $this->behavior_packman = (bool) ($s?->get('behavior_packman') ?? false);
    }

    public static function init(): Settings
    {
        if (!(self::$settings instanceof self)) {
            self::$settings = new self();
        }

        return self::$settings;
    }

    public function getSetting(string $key): mixed
    {
        return $this->{$key} ?? null;
    }

    /**
     * Overwrite a plugin settings (in db)
     *
     * @param   string  $key    The setting ID
     * @param   mixed   $value  The setting value
     *
     * @return  bool True on success
     */
    public function writeSetting(string $key, mixed $value): bool
    {
        if (property_exists($this, $key) && settype($value, gettype($this->{$key})) === true) {
            My::settings()->drop($key);
            My::settings()->put($key, $value, gettype($this->{$key}), '', true, true);

            return true;
        }

        return false;
    }

    /**
     * List defined settings keys
     *
     * @return  array   The settings keys
     */
    public function listSettings(): array
    {
        return array_diff_key(get_object_vars($this), ['settings' => '']);
    }
}
