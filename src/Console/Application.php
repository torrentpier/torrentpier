<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use TorrentPier\Console\Command\Command as BaseCommand;

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
     * Detect application version from config
     */
    private function detectVersion(): string
    {
        // Try to get version from config (set by config.php)
        if (function_exists('config')) {
            $version = config()->get('tp_version');
            if ($version) {
                return $version;
            }
        }

        // Fallback: try to read from composer.json
        $composerPath = BB_ROOT . 'composer.json';
        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);
            if (isset($composer['version'])) {
                return $composer['version'];
            }
        }

        // Final fallback
        return 'dev';
    }

    /**
     * Auto-discover and register all commands from the Command directory
     */
    private function discoverCommands(): void
    {
        $commandDir = __DIR__ . '/Command';

        if (!is_dir($commandDir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($commandDir, RecursiveDirectoryIterator::SKIP_DOTS)
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

            // Check if class exists and is a valid command
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

            // Register the command
            $this->add($reflection->newInstance());
        }
    }

    /**
     * Extract fully qualified class name from a PHP file
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
