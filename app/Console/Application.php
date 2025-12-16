<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console;

use FilesystemIterator;
use Illuminate\Contracts\Container\BindingResolutionException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Throwable;
use TorrentPier\Application as Container;
use TorrentPier\Console\Commands\Command as BaseCommand;

/**
 * Bull Console Application
 *
 * Main entry point for all TorrentPier CLI commands.
 * Supports dependency injection via the Application container.
 */
class Application extends SymfonyApplication
{
    /**
     * The DI container instance
     */
    protected ?Container $container = null;

    /**
     * Create a new console application
     *
     * @param Container|null $container The DI container (optional for backward compatibility)
     */
    public function __construct(?Container $container = null)
    {
        $this->container = $container;

        parent::__construct('Bull CLI', $this->detectVersion());

        $this->discoverCommands();
    }

    /**
     * Get the DI container instance
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }

    /**
     * Set the DI container instance
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Gets the help message
     */
    public function getHelp(): string
    {
        $version = $this->getVersion();
        $cwd = $this->shortenPath(getcwd() ?: '.');

        return <<<HELP

              <fg=#b5651d>∩ ▄███▄ ∩</>    <options=bold>Bull CLI</> {$version}
              <fg=#b5651d> ▐◉ █ ◉▌</>     TorrentPier Console
              <fg=#b5651d>  ▐▄◎▄▌</>      {$cwd}

            HELP;
    }

    /**
     * Gets the long version string
     */
    public function getLongVersion(): string
    {
        return \sprintf(
            '<info>%s</info> version <comment>%s</comment>',
            $this->getName(),
            $this->getVersion(),
        );
    }

    /**
     * Get the application version
     */
    private function detectVersion(): string
    {
        return 'v' . $this->container->version();
    }

    /**
     * Auto-discover and register all commands from the Commands directory
     * @throws BindingResolutionException
     */
    private function discoverCommands(): void
    {
        $commandDir = __DIR__ . '/Commands';

        if (!files()->isDirectory($commandDir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($commandDir, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file->getPathname());

            if ($className === null) {
                continue;
            }

            // Skip the base Command class
            if ($className === BaseCommand::class) {
                continue;
            }

            // Check if a class exists and is a valid command
            if (!class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            // Skip abstract classes and interfaces
            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            // Must extend Symfony Command
            if (!$reflection->isSubclassOf(Command::class)) {
                continue;
            }

            // Register the command (using container if available)
            try {
                $command = $this->resolveCommand($className, $reflection);
                if ($command !== null) {
                    $this->add($command);
                }
            } catch (Throwable) {
                // Skip commands that cannot be instantiated
            }
        }
    }

    /**
     * Resolve a command instance, using the DI container if available
     * @throws ReflectionException
     */
    private function resolveCommand(string $className, ReflectionClass $reflection): ?Command
    {
        // Try to resolve via container first (enables constructor injection)
        if ($this->container !== null) {
            try {
                /** @var Command $command */
                $command = $this->container->make($className);

                return $command;
            } catch (Throwable) {
                // Container couldn't resolve - fall through to direct instantiation
            }
        }

        // Fall back to direct instantiation (requires no constructor params)
        if (!$reflection->isInstantiable()) {
            return null;
        }

        // Check if the constructor has required parameters
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            // If there's a required parameter without a default, skip this command (it needs DI, but no container is available)
            if (array_any($constructor->getParameters(), fn ($param) => !$param->isOptional() && !$param->isDefaultValueAvailable())) {
                return null;
            }
        }

        /** @var Command $command */
        $command = $reflection->newInstance();

        return $command;
    }

    /**
     * Extract a fully qualified class name from a PHP file
     * @throws BindingResolutionException
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        $contents = files()->get($filePath);

        if ($contents === false) {
            return null;
        }

        $namespace = null;
        $class = null;

        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = $matches[1];
        }

        // Extract class name
        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $class = $matches[1];
        }

        if ($namespace === null || $class === null) {
            return null;
        }

        return $namespace . '\\' . $class;
    }

    /**
     * Shorten path for display (replace home dir with ~)
     */
    private function shortenPath(string $path): string
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE') ?: '';
        if ($home && str_starts_with($path, $home)) {
            return '~' . substr($path, \strlen($home));
        }

        return $path;
    }
}
