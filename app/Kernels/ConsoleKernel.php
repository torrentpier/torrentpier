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
 */
class ConsoleKernel
{
    /**
     * The console application instance
     */
    protected ?ConsoleApplication $console = null;

    /**
     * The commands provided by the application
     *
     * @var string[]
     */
    public array $commands {
        get => $this->_commands;
        set => $this->_commands = $value;
    }

    private array $_commands = [];

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
        // Boot the application if not already booted
        if (!$this->app->isBooted()) {
            $this->app->boot();
        }

        // Bootstrap the console layer
        $this->bootstrap();

        // Get the console application
        $this->console = $this->getConsoleApplication();

        // Run the console application
        return $this->console->run($input, $output);
    }

    /**
     * Perform any final actions after the command has been executed
     */
    public function terminate(InputInterface $input, int $exitCode): void
    {
        // Perform cleanup after command execution
        // This can be used for:
        // - Logging command execution
        // - Releasing resources
        // - Sending notifications
    }

    /**
     * Register the commands for the application
     *
     * @param string[] $commands
     */
    public function registerCommands(array $commands): void
    {
        $this->commands = array_merge($this->commands, $commands);
    }

    /**
     * Bootstrap the console layer
     */
    protected function bootstrap(): void
    {
        // Any console-specific bootstrapping can go here
        // For example:
        // - Setting memory limits
        // - Disabling time limits
        // - Loading console-specific configuration
    }

    /**
     * Get the Symfony Console application instance
     */
    protected function getConsoleApplication(): ConsoleApplication
    {
        if ($this->console === null) {
            $this->console = new ConsoleApplication($this->app);
        }

        return $this->console;
    }
}
