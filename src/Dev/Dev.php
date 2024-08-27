<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Dev;

use TorrentPier\Dev\Handlers\Whoops;

use TorrentPier\Dev\Traits\EnvironmentTrait;
use TorrentPier\Dev\Traits\ShowingErrorsTrait;

/**
 * Class Dev
 * @package TorrentPier\Dev
 */
final class Dev
{
    use EnvironmentTrait, ShowingErrorsTrait;

    /**
     * Dev constructor
     */
    public static function startup(): void
    {
        if (self::$isProduction) {
            self::disableShowingErrors();
        } else {
            self::enableShowingErrors();
        }

        $whoops = new Whoops();

        if (!self::$isProduction) {
            $whoops->showOnPage();
            $whoops->showInBrowserConsole();
            $whoops->showInLogs();
        }

        $whoops->register();
    }

    public static function log(string $message, string $file, $level)
    {

    }
}
