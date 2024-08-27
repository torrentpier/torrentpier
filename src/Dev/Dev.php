<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Dev;

use Whoops\Run;

use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;

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
     * Whoops instance
     *
     * @var Run
     */
    private static Run $whoops;

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

        self::$whoops = new Run();
        // TODO ...
        self::$whoops->register();
    }

    /**
     * Log something into target file
     *
     * @param string $message
     * @param string $file
     * @param Level $level
     * @return void
     */
    public static function log(string $message, string $file, Level $level = Level::Debug): void
    {
        $log = new Logger(APP_NAME);
        $log->pushHandler(new StreamHandler(LOG_DIR . '/' . $file . '.' . LOG_EXT));
        $log->log($level, $message);
    }
}
