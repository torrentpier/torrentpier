<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use TorrentPier\Console\Command\AboutCommand;
use TorrentPier\Console\Command\Cache\CacheClearCommand;
use TorrentPier\Console\Command\Cache\CacheStatusCommand;
use TorrentPier\Console\Command\Cron\CronRunCommand;
use TorrentPier\Console\Command\Cron\CronListCommand;
use TorrentPier\Console\Command\Migrate\MigrateCommand;
use TorrentPier\Console\Command\Migrate\MigrateStatusCommand;
use TorrentPier\Console\Command\Migrate\MigrateRollbackCommand;

/**
 * Bull Console Application
 *
 * Main entry point for all TorrentPier CLI commands
 */
class Application extends SymfonyApplication
{
    public const VERSION = '1.0.0';

    public function __construct()
    {
        parent::__construct('Bull CLI', self::VERSION);

        $this->registerCommands();
    }

    /**
     * Register all available commands
     */
    private function registerCommands(): void
    {
        // System commands
        $this->add(new AboutCommand());

        // Cache commands
        $this->add(new CacheClearCommand());
        $this->add(new CacheStatusCommand());

        // Cron commands
        $this->add(new CronRunCommand());
        $this->add(new CronListCommand());

        // Migration commands
        $this->add(new MigrateCommand());
        $this->add(new MigrateStatusCommand());
        $this->add(new MigrateRollbackCommand());
    }

    /**
     * Gets the help message
     */
    public function getHelp(): string
    {
        return <<<HELP

  ____        _ _    ____ _     ___
 | __ ) _   _| | |  / ___| |   |_ _|
 |  _ \| | | | | | | |   | |    | |
 | |_) | |_| | | | | |___| |___ | |
 |____/ \__,_|_|_|  \____|_____|___|

 TorrentPier – Bull-powered BitTorrent tracker engine

HELP;
    }

    /**
     * Gets the long version string
     */
    public function getLongVersion(): string
    {
        return sprintf(
            '<info>%s</info> version <comment>%s</comment>',
            $this->getName(),
            $this->getVersion()
        );
    }
}

