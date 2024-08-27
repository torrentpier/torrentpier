<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Dev\Traits;

/**
 * Trait ShowingErrorsTrait
 * @package TorrentPier\Dev\Traits
 */
trait ShowingErrorsTrait
{
    /**
     * Turn off displaying errors
     *
     * @return void
     */
    protected static function disableShowingErrors(): void
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
    }

    /**
     * Turn on displaying errors
     *
     * @return void
     */
    protected static function enableShowingErrors(): void
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    }
}
