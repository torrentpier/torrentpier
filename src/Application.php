<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier;

use Illuminate\Container\Container;

/**
 * TorrentPier Application Container
 *
 * Main application class that extends Laravel's Container.
 * Manages service providers, bootstrapping, and dependency injection.
 */
class Application extends Container
{
    /**
     * The TorrentPier version
     */
    public const string VERSION = '3.0.0';

    /**
     * The base path for the application
     */
    protected string $basePath;

    /**
     * Indicates if the application has been bootstrapped
     */
    protected bool $hasBeenBootstrapped = false;

    /**
     * Indicates if the application has booted
     */
    protected bool $booted = false;

    /**
     * All the registered service providers
     *
     * @var ServiceProvider[]
     */
    protected array $serviceProviders = [];

    /**
     * The names of the loaded service providers
     *
     * @var array<string, bool>
     */
    protected array $loadedProviders = [];

    /**
     * The booted service provider callbacks
     *
     * @var array<string, bool>
     */
    protected array $bootedCallbacks = [];

    /**
     * The booting callbacks
     *
     * @var callable[]
     */
    protected array $bootingCallbacks = [];

    /**
     * Create a new Application instance
     */
    public function __construct(string $basePath)
    {
        $this->setBasePath($basePath);

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
    }

    /**
     * Set the base path for the application
     */
    public function setBasePath(string $basePath): static
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Register the basic bindings into the container
     */
    protected function registerBaseBindings(): void
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(Container::class, $this);
        $this->instance(self::class, $this);
    }

    /**
     * Register all the base service providers
     */
    protected function registerBaseServiceProviders(): void
    {
        // Base providers can be registered here if needed
    }

    /**
     * Bind all the application paths in the container
     */
    protected function bindPathsInContainer(): void
    {
        $this->instance('path.base', $this->basePath());
        $this->instance('path.app', $this->appPath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.library', $this->libraryPath());
        $this->instance('path.src', $this->srcPath());
        $this->instance('path.routes', $this->routesPath());
    }

    /**
     * Get the base path of the installation
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the application "app" directory
     */
    public function appPath(string $path = ''): string
    {
        return $this->basePath('app') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the application configuration files
     */
    public function configPath(string $path = ''): string
    {
        return $this->basePath('config') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the public / web directory
     */
    public function publicPath(string $path = ''): string
    {
        return $this->basePath('public') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the storage directory
     */
    public function storagePath(string $path = ''): string
    {
        return $this->basePath('storage') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the resources directory
     */
    public function resourcePath(string $path = ''): string
    {
        return $this->basePath('resources') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the bootstrap directory
     */
    public function bootstrapPath(string $path = ''): string
    {
        return $this->basePath('bootstrap') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the database directory
     */
    public function databasePath(string $path = ''): string
    {
        return $this->basePath('database') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the library directory
     */
    public function libraryPath(string $path = ''): string
    {
        return $this->basePath('library') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the src directory
     */
    public function srcPath(string $path = ''): string
    {
        return $this->basePath('src') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the path to the routes directory
     */
    public function routesPath(string $path = ''): string
    {
        return $this->basePath('routes') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Register a service provider with the application
     */
    public function register(ServiceProvider|string $provider, bool $force = false): ServiceProvider
    {
        if (($registered = $this->getProvider($provider)) && !$force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it
        if (\is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        $provider->register();

        // If there are bindings / singletons set as properties on the provider
        // we will spin through them and register them with the application
        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons as $key => $value) {
                $key = \is_int($key) ? $value : $key;
                $this->singleton($key, $value);
            }
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method
        // on the provider so it can boot its services
        if ($this->isBooted()) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Get the registered service provider instance if it exists
     */
    public function getProvider(ServiceProvider|string $provider): ?ServiceProvider
    {
        $name = \is_string($provider) ? $provider : $provider::class;

        return $this->serviceProviders[$name] ?? null;
    }

    /**
     * Get the registered service provider instances if any exist
     *
     * @return ServiceProvider[]
     */
    public function getProviders(ServiceProvider|string $provider): array
    {
        $name = \is_string($provider) ? $provider : $provider::class;

        return array_filter(
            $this->serviceProviders,
            fn ($value) => $value instanceof $name,
        );
    }

    /**
     * Resolve a service provider instance from the class name
     */
    public function resolveProvider(string $provider): ServiceProvider
    {
        return new $provider($this);
    }

    /**
     * Mark the given provider as registered
     */
    protected function markAsRegistered(ServiceProvider $provider): void
    {
        $this->serviceProviders[$provider::class] = $provider;
        $this->loadedProviders[$provider::class] = true;
    }

    /**
     * Boot the application's service providers
     */
    public function boot(): void
    {
        if ($this->isBooted()) {
            return;
        }

        // Call the booting callbacks
        $this->fireAppCallbacks($this->bootingCallbacks);

        // Boot each service provider
        foreach ($this->serviceProviders as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    /**
     * Boot the given service provider
     */
    protected function bootProvider(ServiceProvider $provider): void
    {
        $providerClass = $provider::class;

        if (isset($this->bootedCallbacks[$providerClass])) {
            return;
        }

        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        $this->bootedCallbacks[$providerClass] = true;
    }

    /**
     * Register a new boot listener
     */
    public function booting(callable $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Fire the given array of callbacks
     *
     * @param callable[] $callbacks
     */
    protected function fireAppCallbacks(array &$callbacks): void
    {
        $index = 0;

        while ($index < \count($callbacks)) {
            $callbacks[$index]($this);
            $index++;
        }
    }

    /**
     * Determine if the application has booted
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Get all the service providers registered with the application
     *
     * @return ServiceProvider[]
     */
    public function getLoadedProviders(): array
    {
        return $this->serviceProviders;
    }

    /**
     * Determine if the given service provider is loaded
     */
    public function providerIsLoaded(string $provider): bool
    {
        return isset($this->loadedProviders[$provider]);
    }

    /**
     * Get the version number of the application
     */
    public function version(): string
    {
        return static::VERSION;
    }

    /**
     * Determine if the application is running in the console
     */
    public function runningInConsole(): bool
    {
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }

    /**
     * Determine if the application is running unit tests
     */
    public function runningUnitTests(): bool
    {
        return \defined('PEST_TESTING') || $this->bound('env') && $this['env'] === 'testing';
    }

    /**
     * Get the current application environment
     */
    public function environment(): string
    {
        if ($this->bound('env')) {
            return $this['env'];
        }

        return env('APP_ENV', 'production');
    }

    /**
     * Determine if the application is in the production environment
     */
    public function isProduction(): bool
    {
        return $this->environment() === 'production';
    }

    /**
     * Determine if the application is in the development environment
     */
    public function isLocal(): bool
    {
        return $this->environment() === 'development';
    }

    /**
     * Flush the container of all bindings and resolved instances
     */
    public function flush(): void
    {
        parent::flush();

        $this->serviceProviders = [];
        $this->loadedProviders = [];
        $this->bootedCallbacks = [];
        $this->bootingCallbacks = [];
        $this->booted = false;
    }
}
