<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

/**
 * Class HttpHelper
 * @package TorrentPier\Helpers
 */
class HttpHelper
{
    /**
     * Return true if server have SSL
     *
     * Note: Uses $_SERVER directly because this is called during config loading
     * before request() helper is available.
     */
    public static function isHTTPS(): bool
    {
        return
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on')
            || (($_SERVER['SERVER_PORT'] ?? 0) == 443)
            || (($_SERVER['HTTP_X_FORWARDED_PORT'] ?? 0) == 443)
            || (($_SERVER['REQUEST_SCHEME'] ?? '') === 'https');
    }
}
