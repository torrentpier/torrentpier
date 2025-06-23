<?php

declare(strict_types=1);

/**
 * Container Bootstrap
 * 
 * Creates and configures the Illuminate Container instance
 */

use Illuminate\Container\Container;

/**
 * Create and configure the application container
 */
function createContainer(string $rootPath): Container
{
    // Load environment variables first
    $dotenv = \Dotenv\Dotenv::createImmutable($rootPath);
    $dotenv->safeLoad();
    
    $container = new Container();
    
    // Set the container instance globally
    Container::setInstance($container);
    
    // Register base paths
    $container->instance('path.base', $rootPath);
    $container->instance('path.app', $rootPath . '/app');
    $container->instance('path.config', $rootPath . '/config');
    $container->instance('path.database', $rootPath . '/database');
    $container->instance('path.public', $rootPath . '/public');
    $container->instance('path.resources', $rootPath . '/resources');
    $container->instance('path.storage', $rootPath . '/storage');
    
    // Register the container itself
    $container->instance(Container::class, $container);
    $container->alias(Container::class, 'app');
    $container->alias(Container::class, Illuminate\Contracts\Container\Container::class);
    $container->alias(Container::class, Psr\Container\ContainerInterface::class);
    
    // Load configuration
    loadConfiguration($container, $rootPath);
    
    // Register service providers
    registerServiceProviders($container);
    
    return $container;
}

/**
 * Load configuration files
 */
function loadConfiguration(Container $container, string $rootPath): void
{
    $configPath = $rootPath . '/config';
    
    // Create unified config repository
    $config = new \Illuminate\Config\Repository();
    
    // Load services configuration
    if (file_exists($configPath . '/services.php')) {
        $services = require $configPath . '/services.php';
        foreach ($services as $abstract => $concrete) {
            if (is_callable($concrete)) {
                $container->bind($abstract, $concrete);
            } else {
                $container->bind($abstract, $concrete);
            }
        }
    }
    
    // Load all config files into the repository
    foreach (glob($configPath . '/*.php') as $file) {
        $key = basename($file, '.php');
        $value = require $file;
        $config->set($key, $value);
        // Also register individual config files for backward compatibility
        $container->instance("config.{$key}", $value);
    }
    
    // Register the unified config repository
    $container->instance('config', $config);
    $container->bind(\Illuminate\Config\Repository::class, function() use ($config) {
        return $config;
    });
}

/**
 * Register service providers
 */
function registerServiceProviders(Container $container): void
{
    $providers = [
        // Register your service providers here
        \App\Providers\AppServiceProvider::class,
        \App\Providers\EventServiceProvider::class,
        \App\Providers\RouteServiceProvider::class,
        \App\Providers\ConsoleServiceProvider::class,
    ];
    
    foreach ($providers as $providerClass) {
        if (class_exists($providerClass)) {
            $provider = new $providerClass($container);
            
            if (method_exists($provider, 'register')) {
                $provider->register();
            }
            
            if (method_exists($provider, 'boot')) {
                $container->call([$provider, 'boot']);
            }
        }
    }
}