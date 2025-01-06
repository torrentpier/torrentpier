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
 * Class IndexNowHelper
 * @package TorrentPier\Helpers
 */
class IndexNowHelper
{
    /**
     * Key-file extension
     *
     * @var string
     */
    private static string $keyFileExtension = '.txt';

    /**
     * Returns path to key-file (relative URL)
     *
     * @return string
     */
    public static function getKeyLocation(): string
    {
        global $bb_cfg;

        return FULL_URL . $bb_cfg['indexnow_key'] . self::$keyFileExtension;
    }

    /**
     * Returns path to key-file
     *
     * @return string
     */
    public static function getKeyPath(): string
    {
        global $bb_cfg;

        return BB_ROOT . $bb_cfg['indexnow_key'] . self::$keyFileExtension;
    }

    /**
     * Returns IndexNow unique key
     *
     * @return string
     */
    public static function getKey(): string
    {
        global $bb_cfg;

        return $bb_cfg['indexnow_key'];
    }
}
