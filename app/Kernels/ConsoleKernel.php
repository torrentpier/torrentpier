<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Kernels;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Application;
use TorrentPier\Console\Application as ConsoleApplication;

/**
 * Console Kernel
 *
 * Handles console commands by bootstrapping the application
 * and delegating to the Symfony Console application.
 *
 * Commands are auto-discovered from app/Console/Commands/ directory.
 */
class ConsoleKernel
{
    /**
     * The console application instance
     */
    protected ?ConsoleApplication $console = null;

    /**
     * Create a new Console Kernel instance
     */
    public function __construct(
        private readonly Application $_app,
    ) {}

    /**
     * The application instance (read-only)
     */
    public Application $app {
        get => $this->_app;
    }

    /**
     * Handle an incoming console command
     * @throws Exception
     */
    public function handle(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        if (!$this->app->isBooted()) {
            $this->app->boot();
        }

        $this->bootstrap();

        return $this->getConsoleApplication()->run($input, $output);
    }

    /**
     * Perform any final actions after the command has been executed
     */
    public function terminate(InputInterface $input, int $exitCode): void
    {
        // Cleanup after command execution (logging, etc.)
    }

    /**
     * Bootstrap the console layer
     */
    protected function bootstrap(): void
    {
        // Console-specific bootstrapping (memory limits, time limits, etc.)
        // Remove the time limit for long-running commands (migrations, queue workers, etc.)
        set_time_limit(0);
    }

    /**
     * Get the Symfony Console application instance
     */
    protected function getConsoleApplication(): ConsoleApplication
    {
        return $this->console ??= new ConsoleApplication($this->app);
    }
}
