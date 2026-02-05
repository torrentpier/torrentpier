<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Bootstrap;

use Closure;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use TorrentPier\Application;
use TorrentPier\Config;

/**
 * Load application configuration files
 *
 * Laravel 12-style configuration loading with support for:
 * - Cached configuration for performance
 * - Nested directory support (config/auth/guards.php → auth.guards)
 * - Deep merging for specific options (connections, guards, etc.)
 * - Static config override for testing (alwaysUse)
 */
class LoadConfiguration
{
    /**
     * Static config override closure (useful for testing)
     *
     * @var (Closure(Application): array)|null
     */
    protected static ?Closure $alwaysUseConfig = null;

    public function bootstrap(Application $app): void
    {
        $items = [];
        $loadedFromCache = false;

        // Check for static override first (useful in tests)
        if (self::$alwaysUseConfig !== null) {
            $items = (self::$alwaysUseConfig)($app);
            $loadedFromCache = true;
        } elseif (file_exists($app->getCachedConfigPath())) {
            $items = (fn () => require $app->getCachedConfigPath())();
            $loadedFromCache = true;
        }

        $app->instance('config_loaded_from_cache', $loadedFromCache);

        $app->instance('config', $config = new Config($items));
        $app->instance(Config::class, $config);
        $app->instance(RepositoryContract::class, $config);

        if (!$loadedFromCache) {
            $this->loadConfigurationFiles($app, $config);
        }

        date_default_timezone_set($config->get('app.timezone', $config->get('default_timezone', 'UTC')));

        mb_internal_encoding('UTF-8');
    }

    protected function loadConfigurationFiles(Application $app, RepositoryContract $repository): void
    {
        $configPath = $app->basePath('config');
        $files = $this->getConfigurationFiles($configPath);

        foreach ($files as $name => $path) {
            $this->loadConfigurationFile($repository, $name, $path);
        }
    }

    protected function loadConfigurationFile(RepositoryContract $repository, string $name, string $path): void
    {
        $config = (fn () => require $path)();

        if (!\is_array($config)) {
            return;
        }

        // Get existing config for this key (if any) for deep merging
        $existing = $repository->get($name, []);

        if (\is_array($existing) && !empty($existing)) {
            $config = array_merge($existing, $config);

            // Deep merge for specific options
            foreach ($this->mergeableOptions($name) as $option) {
                if (isset($config[$option], $existing[$option])) {
                    $config[$option] = array_merge($existing[$option], $config[$option]);
                }
            }
        }

        $repository->set($name, $config);
    }

    /**
     * Options that require deep merging (Laravel 12 style)
     */
    protected function mergeableOptions(string $name): array
    {
        return match ($name) {
            'auth' => ['guards', 'providers', 'passwords'],
            'broadcasting', 'database', 'queue' => ['connections'],
            'cache' => ['stores'],
            'filesystems' => ['disks'],
            'logging' => ['channels'],
            'mail' => ['mailers'],
            default => [],
        };
    }

    protected function getConfigurationFiles(string $configPath): array
    {
        $files = [];

        $configPath = realpath($configPath);
        if (!$configPath) {
            return $files;
        }

        // Guard against legacy config files that should not be auto-loaded
        $skipFiles = ['config.php', 'config.local.php'];

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            if (\in_array($file->getFilename(), $skipFiles, true)) {
                continue;
            }

            $filename = $file->getBasename('.php');
            $directory = $this->getNestedDirectory($file, $configPath);
            $files[$directory . $filename] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Get a nested directory path for dot-notation config keys
     */
    protected function getNestedDirectory(SplFileInfo $file, string $configPath): string
    {
        $directory = $file->getPath();

        $nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR);
        if ($nested !== '') {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }

        return $nested;
    }

    /**
     * Get fresh configuration from files (bypasses cache)
     * @throws BindingResolutionException
     */
    public static function getFreshConfiguration(): array
    {
        $loader = new self;
        $configPath = app()->configPath();
        $config = new Config([]);

        $files = $loader->getConfigurationFiles($configPath);
        foreach ($files as $name => $path) {
            $loader->loadConfigurationFile($config, $name, $path);
        }

        return $config->all();
    }

    /**
     * Set static config override (useful for testing)
     *
     * @param (Closure(Application): array)|null $config
     */
    public static function alwaysUse(?Closure $config): void
    {
        self::$alwaysUseConfig = $config;
    }
}
