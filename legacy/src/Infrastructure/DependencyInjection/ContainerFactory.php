<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\DependencyInjection;

use DI\ContainerBuilder;
use TorrentPier\Infrastructure\DependencyInjection\Definitions\ApplicationDefinitions;
use TorrentPier\Infrastructure\DependencyInjection\Definitions\DomainDefinitions;
use TorrentPier\Infrastructure\DependencyInjection\Definitions\InfrastructureDefinitions;
use TorrentPier\Infrastructure\DependencyInjection\Definitions\PresentationDefinitions;

class ContainerFactory
{
    public static function create(array $config = []): Container
    {
        $builder = new ContainerBuilder();

        // Configure container settings
        self::configureContainer($builder, $config);

        // Add definitions from all layers
        self::addDefinitions($builder, $config);

        // Build the container
        $diContainer = $builder->build();
        $container = new Container($diContainer);

        // Register and boot service providers
        self::registerProviders($container, $config);

        return $container;
    }

    private static function configureContainer(ContainerBuilder $builder, array $config): void
    {
        // Enable compilation in production for better performance
        $isProduction = ($config['environment'] ?? 'development') === 'production';

        if ($isProduction) {
            $builder->enableCompilation($config['compilation_dir'] ?? __DIR__.'/../../../internal_data/cache/container');
            $builder->writeProxiesToFile(true, $config['proxies_dir'] ?? __DIR__.'/../../../internal_data/cache/proxies');
        }

        // Enable autowiring by default
        $builder->useAutowiring($config['autowiring'] ?? true);
    }

    private static function addDefinitions(ContainerBuilder $builder, array $config): void
    {
        // Add config definitions first
        if (isset($config['definitions'])) {
            $builder->addDefinitions($config['definitions']);
        }

        // Add layer-specific definitions
        $builder->addDefinitions(DomainDefinitions::getDefinitions());
        $builder->addDefinitions(ApplicationDefinitions::getDefinitions());
        $builder->addDefinitions(InfrastructureDefinitions::getDefinitions($config));
        $builder->addDefinitions(PresentationDefinitions::getDefinitions());

        // Add custom definition files if provided
        if (isset($config['definition_files'])) {
            foreach ($config['definition_files'] as $file) {
                if (file_exists($file)) {
                    $builder->addDefinitions($file);
                }
            }
        }
    }

    private static function registerProviders(Container $container, array $config): void
    {
        $providers = $config['providers'] ?? [];

        // Instantiate providers
        $instances = [];
        foreach ($providers as $providerClass) {
            if (class_exists($providerClass)) {
                $provider = new $providerClass();
                if ($provider instanceof ServiceProvider) {
                    $instances[] = $provider;
                    $provider->register($container);
                }
            }
        }

        // Boot all providers after registration
        foreach ($instances as $provider) {
            $provider->boot($container);
        }
    }
}
