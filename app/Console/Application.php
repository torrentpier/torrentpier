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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
use TorrentPier\Application as Container;

/**
 * Bull Console Application
 *
 * Auto-discovers commands from app/Console/Commands/ and registers them
 * via a lazy FactoryCommandLoader: each command is only instantiated
 * when actually invoked.
 */
class Application extends SymfonyApplication
{
    /**
     * PSR-4 prefixes mapped to the commands directory, tried in order.
     *
     * @var list<string>
     */
    private const array COMMAND_NAMESPACES = [
        'TorrentPier\\Console\\Commands\\',
        'App\\Console\\Commands\\',
    ];

    public function __construct(
        private readonly Container $container,
    ) {
        parent::__construct('Bull CLI', 'v' . $container->version());

        $this->setCommandLoader(new FactoryCommandLoader($this->discoverFactories()));
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
     * Walk the commands directory and build a `name => factory` map.
     *
     * @return array<string, callable(): Command>
     */
    private function discoverFactories(): array
    {
        $commandsDir = __DIR__ . '/Commands';

        if (!is_dir($commandsDir)) {
            return [];
        }

        $factories = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($commandsDir, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->resolveClassName($file->getPathname(), $commandsDir);
            if ($className === null) {
                continue;
            }

            $reflection = new ReflectionClass($className);
            if ($reflection->isAbstract() || !$reflection->isSubclassOf(Command::class)) {
                continue;
            }

            $attribute = $reflection->getAttributes(AsCommand::class)[0] ?? null;
            if ($attribute === null) {
                continue;
            }

            /** @var AsCommand $asCommand */
            $asCommand = $attribute->newInstance();
            $factory = fn (): Command => $this->container->make($className);

            // AsCommand encodes aliases (and hidden flag) in `name` as a `|`-separated
            // string: "primary|alias1|alias2", or "|hidden:cmd" when hidden.
            foreach (explode('|', $asCommand->name) as $alias) {
                if ($alias !== '') {
                    $factories[$alias] = $factory;
                }
            }
        }

        return $factories;
    }

    /**
     * Convert a command file path to its FQCN via PSR-4 prefix lookup.
     */
    private function resolveClassName(string $filePath, string $commandsDir): ?string
    {
        $relative = substr($filePath, \strlen($commandsDir) + 1, -4);
        $suffix = str_replace(['/', '\\'], '\\', $relative);

        foreach (self::COMMAND_NAMESPACES as $prefix) {
            $candidate = $prefix . $suffix;
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
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
