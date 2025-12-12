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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Throwable;
use TorrentPier\Console\Commands\Command as BaseCommand;

/**
 * Bull Console Application
 *
 * Main entry point for all TorrentPier CLI commands
 */
class Application extends SymfonyApplication
{
    /**
     * Create a new console application
     */
    public function __construct()
    {
        parent::__construct('Bull CLI', $this->detectVersion());

        $this->discoverCommands();
    }

    /**
     * Detect an application version from config
     */
    private function detectVersion(): string
    {
        // Get a version from config (set by config.php)
        if (function_exists('config')) {
            $version = config()->get('tp_version');
            if ($version) {
                return $version;
            }
        }

        return 'dev';
    }

    /**
     * Auto-discover and register all commands from the Commands directory
     */
    private function discoverCommands(): void
    {
        $commandDir = __DIR__ . '/Commands';

        if (!is_dir($commandDir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($commandDir, FilesystemIterator::SKIP_DOTS)
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

            // Must be instantiable (has a public constructor without required params)
            if (!$reflection->isInstantiable()) {
                continue;
            }

            // Register the command
            try {
                /** @var Command $command */
                $command = $reflection->newInstance();
                $this->add($command);
            } catch (Throwable) {
                // Skip commands that cannot be instantiated
            }
        }
    }

    /**
     * Extract a fully qualified class name from a PHP file
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        $contents = file_get_contents($filePath);

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
     * Shorten path for display (replace home dir with ~)
     */
    private function shortenPath(string $path): string
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE') ?: '';
        if ($home && str_starts_with($path, $home)) {
            return '~' . substr($path, strlen($home));
        }
        return $path;
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
