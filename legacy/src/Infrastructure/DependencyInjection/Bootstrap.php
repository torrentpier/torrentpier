<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\DependencyInjection;

use Dotenv\Dotenv;

class Bootstrap
{
    private static ?Container $container = null;

    public static function init(string $rootPath, array $config = []): Container
    {
        if (self::$container !== null) {
            return self::$container;
        }

        // Load environment variables
        self::loadEnvironment($rootPath);

        // Merge configuration
        $config = self::loadConfiguration($rootPath, $config);

        // Create and configure container
        self::$container = ContainerFactory::create($config);

        // Register container instance with itself
        self::$container->getWrappedContainer()->set(Container::class, self::$container);
        self::$container->getWrappedContainer()->set('container', self::$container);

        return self::$container;
    }

    public static function getContainer(): ?Container
    {
        return self::$container;
    }

    public static function reset(): void
    {
        self::$container = null;
    }

    private static function loadEnvironment(string $rootPath): void
    {
        if (file_exists($rootPath.'/.env')) {
            $dotenv = Dotenv::createImmutable($rootPath);
            $dotenv->load();
        }
    }

    private static function loadConfiguration(string $rootPath, array $config): array
    {
        // Load base configuration
        $configPath = $rootPath.'/config';

        // Container configuration
        if (file_exists($configPath.'/container.php')) {
            $containerConfig = require $configPath.'/container.php';
            $config = array_merge($config, $containerConfig);
        }

        // Services configuration
        if (file_exists($configPath.'/services.php')) {
            $servicesConfig = require $configPath.'/services.php';
            $config['definitions'] = array_merge(
                $config['definitions'] ?? [],
                $servicesConfig
            );
        }

        // Database configuration
        if (file_exists($configPath.'/database.php')) {
            $config['database'] = require $configPath.'/database.php';
        }

        // Cache configuration
        if (file_exists($configPath.'/cache.php')) {
            $config['cache'] = require $configPath.'/cache.php';
        }

        // Environment from .env
        $config['environment'] = $_ENV['APP_ENV'] ?? 'development';
        $config['debug'] = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return $config;
    }
}
