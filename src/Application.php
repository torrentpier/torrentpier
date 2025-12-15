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

use App\Kernels\ConsoleKernel;
use App\Kernels\HttpKernel;
use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use TorrentPier\Http\Middleware;

/**
 * TorrentPier Application Container
 *
 * Main application class that extends Laravel's Container.
 * Manages service providers, bootstrapping, and dependency injection.
 *
 * Supports fluent style configuration via configure() method.
 */
class Application extends Container
{
    /**
     * The TorrentPier version
     */
    public const string VERSION = '3.0.0-alpha.1';

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
     * The bootstrappers to run during the application bootstrap
     *
     * @var string[]
     */
    protected array $bootstrappers = [];

    /**
     * The middleware configuration
     */
    protected Middleware $middlewareConfig;

    /**
     * The exceptions configuration callback
     */
    protected ?Closure $exceptionsCallback = null;

    /**
     * The routing configuration
     *
     * @var array{web?: string, api?: string, admin?: string, tracker?: string, commands?: string}
     */
    protected array $routingConfig = [];

    /**
     * Create a new Application instance
     */
    public function __construct(string $basePath)
    {
        $this->setBasePath($basePath);
        $this->middlewareConfig = new Middleware;

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
    }

    /**
     * Static entry point for fluent configuration (Laravel 12 style)
     *
     * Usage:
     *   return Application::configure(basePath: dirname(__DIR__))
     *       ->withBootstrappers([...])
     *       ->withRouting(
     *           web: __DIR__ . '/../routes/web.php',
     *           admin: __DIR__ . '/../routes/admin.php',
     *           tracker: __DIR__ . '/../routes/tracker.php',
     *       )
     *       ->withMiddleware(fn ($middleware) => $middleware->prepend(...))
     *       ->create();
     */
    public static function configure(string $basePath): PendingApplicationConfiguration
    {
        return new PendingApplicationConfiguration(
            new static($basePath),
        );
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
     * @throws ReflectionException
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

        // If there are bindings / singletons set as properties on the provider,
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
    protected function fireAppCallbacks(array $callbacks): void
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
     * Cached application environment
     */
    protected ?string $environment = null;

    /**
     * Determine if the application is running unit tests
     */
    public function runningUnitTests(): bool
    {
        return \defined('PEST_TESTING') || $this->environment() === 'testing';
    }

    /**
     * Get the current application environment
     */
    public function environment(): string
    {
        return $this->environment ??= env('APP_ENV', 'production');
    }

    /**
     * Determine if the application is in the production environment
     */
    public function isProduction(): bool
    {
        return $this->environment() === 'production';
    }

    /**
     * Determine if the application is in the local/development environment
     */
    public function isLocal(): bool
    {
        return $this->environment() === 'local';
    }

    /**
     * Determine if debug mode is enabled
     *
     * Debug mode is enabled when running locally OR when APP_DEBUG=true
     */
    public function isDebug(): bool
    {
        return $this->isLocal() || env('APP_DEBUG', false);
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

    /**
     * Set the bootstrappers to run during the application bootstrap
     *
     * @param string[] $bootstrappers Array of bootstrapper class names
     */
    public function setBootstrappers(array $bootstrappers): static
    {
        $this->bootstrappers = $bootstrappers;

        return $this;
    }

    /**
     * Get the registered bootstrappers
     *
     * @return string[]
     */
    public function getBootstrappers(): array
    {
        return $this->bootstrappers;
    }

    /**
     * Set the middleware configuration via callback
     *
     * The callback receives a Middleware instance to configure.
     */
    public function setMiddlewareCallback(Closure $callback): static
    {
        $callback($this->middlewareConfig);

        return $this;
    }

    /**
     * Get the middleware configuration
     */
    public function getMiddlewareConfig(): Middleware
    {
        return $this->middlewareConfig;
    }

    /**
     * Set the exceptions configuration callback
     */
    public function setExceptionsCallback(Closure $callback): static
    {
        $this->exceptionsCallback = $callback;

        return $this;
    }

    /**
     * Get the exceptions configuration callback
     */
    public function getExceptionsCallback(): ?Closure
    {
        return $this->exceptionsCallback;
    }

    /**
     * Set the routing configuration
     *
     * @param array{web?: string, api?: string, admin?: string, tracker?: string, commands?: string} $config
     */
    public function setRoutingConfig(array $config): static
    {
        $this->routingConfig = $config;

        return $this;
    }

    /**
     * Get the routing configuration
     *
     * @return array{web?: string, api?: string, admin?: string, tracker?: string, commands?: string}
     */
    public function getRoutingConfig(): array
    {
        return $this->routingConfig;
    }

    /**
     * Run the given array of bootstrap classes
     *
     * Bootstrappers are classes that perform initialization tasks
     * like loading environment variables, configuration, error handlers, etc.
     *
     * @param string[] $bootstrappers Array of bootstrapper class names
     */
    public function bootstrapWith(array $bootstrappers): void
    {
        if ($this->hasBeenBootstrapped) {
            return;
        }

        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $instance = new $bootstrapper;
            $instance->bootstrap($this);
        }
    }

    /**
     * Determine if the application has been bootstrapped
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Handle an incoming HTTP request
     *
     * This method bootstraps the application, creates the HTTP kernel,
     * and dispatches the request through the routing layer.
     *
     * @param ServerRequestInterface|null $request PSR-7 request or null to capture from globals
     * @throws BindingResolutionException
     */
    public function handleRequest(?ServerRequestInterface $request = null): void
    {
        // Bootstrap the application if not already done
        if (!$this->hasBeenBootstrapped && !empty($this->bootstrappers)) {
            $this->bootstrapWith($this->bootstrappers);
        }

        // Create request from globals if not provided
        if ($request === null) {
            $request = $this->captureRequest();
        }

        // Get or create the HTTP kernel
        $kernel = $this->make(HttpKernel::class);

        // Handle the request
        $response = $kernel->handle($request);

        // Send the response
        $this->sendResponse($response);

        // Terminate the request
        $kernel->terminate($request, $response);
    }

    /**
     * Capture the incoming HTTP request from PHP globals
     */
    protected function captureRequest(): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals();
    }

    /**
     * Send the PSR-7 response to the client
     */
    protected function sendResponse(\Psr\Http\Message\ResponseInterface $response): void
    {
        // Send status line
        $statusLine = \sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase(),
        );
        header($statusLine, true, $response->getStatusCode());

        // Send headers
        foreach ($response->getHeaders() as $name => $values) {
            $replace = strtolower($name) !== 'set-cookie';
            foreach ($values as $value) {
                header("{$name}: {$value}", $replace);
                $replace = false;
            }
        }

        // Send body
        echo $response->getBody();
    }

    /**
     * Handle an incoming console command
     *
     * This method bootstraps the application, creates the console kernel,
     * and dispatches the command.
     *
     * @param InputInterface|null $input Symfony console input or null to use default
     * @throws Exception
     * @throws BindingResolutionException
     * @return int Exit code
     */
    public function handleCommand(?InputInterface $input = null): int
    {
        // Bootstrap the application if not already done
        if (!$this->hasBeenBootstrapped && !empty($this->bootstrappers)) {
            $this->bootstrapWith($this->bootstrappers);
        }

        // Get or create the console kernel
        $kernel = $this->make(ConsoleKernel::class);

        // Handle the command
        $status = $kernel->handle($input);

        // Terminate
        $kernel->terminate($input, $status);

        return $status;
    }
}
