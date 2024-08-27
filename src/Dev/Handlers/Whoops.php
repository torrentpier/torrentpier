<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Dev\Handlers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

/**
 * Class Whoops
 * @package TorrentPier\Dev\Handlers
 */
class Whoops
{
    public Run $whoops;

    public function __construct()
    {
        $this->whoops = new Run;
    }

    public function showOnPage(): void
    {
        global $bb_cfg;

        $prettyPageHandler = new PrettyPageHandler();
        foreach ($bb_cfg['whoops']['blacklist'] as $key => $secrets) {
            foreach ($secrets as $secret) {
                $prettyPageHandler->blacklist($key, $secret);
            }
        }

        $this->whoops->pushHandler($prettyPageHandler);
    }

    public function showInBrowserConsole(): void
    {
        $loggingInConsole = new PlainTextHandler();
        $loggingInConsole->loggerOnly(true);
        $loggingInConsole->setLogger((new Logger(
            APP_NAME,
            [(new BrowserConsoleHandler())
                ->setFormatter((new LineFormatter(null, null, true)))]
        )));

        $this->whoops->pushHandler($loggingInConsole);
    }

    public function showInLogs(): void
    {
        if ((int)ini_get('log_errors') === 1) {
            $loggingInFile = new PlainTextHandler();
            $loggingInFile->loggerOnly(true);
            $loggingInFile->setLogger((new Logger(
                APP_NAME,
                [(new StreamHandler(WHOOPS_LOG_FILE))
                    ->setFormatter((new LineFormatter(null, null, true)))]
            )));

            $this->whoops->pushHandler($loggingInFile);
        }
    }

    public function register(): Run
    {
        return $this->whoops->register();
    }
}
