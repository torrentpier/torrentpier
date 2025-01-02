<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

/**
 * Class VersionHelper
 * @package TorrentPier\Helpers
 */
class VersionHelper
{
    /**
     * Version prefix
     *
     * @var string
     */
    private const VERSION_PREFIX = 'v';

    /**
     * Returns version without prefix (v)
     *
     * @param string $version
     * @return string
     */
    public static function removerPrefix(string $version): string
    {
        $version = trim($version);
        $version = mb_strtolower($version, 'UTF-8');
        return str_replace(self::VERSION_PREFIX, '', $version);
    }

    /**
     * Returns version with prefix (v)
     *
     * @param string $version
     * @return string
     */
    public static function addPrefix(string $version): string
    {
        return self::VERSION_PREFIX . trim($version);
    }
}
