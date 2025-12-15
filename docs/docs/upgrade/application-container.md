---
sidebar_position: 13
title: Application Container
---

# Application Container & Architecture Migration

TorrentPier has introduced a modern application architecture with a full-featured dependency injection container, service providers, and a modular bootstrap pipeline. This provides better testability, explicit dependencies, and cleaner code organization.

## No Code Changes Required (for most users)

**Important**: All existing helper functions (`config()`, `DB()`, `CACHE()`, `request()`, etc.) continue to work exactly as before. This is an internal modernization that requires **zero code changes** for standard usage.

## Key Improvements

### Modern Foundation

- **DI Container**: Centralized service resolution with auto-wiring support
- **Service Providers**: Modular service registration with `register()` and `boot()` lifecycle
- **Bootstrap Pipeline**: Explicit, ordered application initialization
- **Fluent Configuration**: Clean, expressive `bootstrap/app.php` setup

### Enhanced Architecture

- **Testability**: Services can be mocked and replaced for testing
- **Explicit Dependencies**: No hidden global state or magic singletons
- **Memory Efficiency**: Shared service instances managed by container
- **IDE Support**: Better autocompletion and type inference

## Application Container (`src/Application.php`)

The new `Application` class serves as the central DI container:

```php
// Get the application instance
$app = app();

// Resolve services from container
$database = app(Database::class);
$config = app(Config::class);

// Or use the familiar helper functions (unchanged)
$database = DB();
$config = config();
```

### Path Helpers

```php
app()->basePath();      // /path/to/torrentpier
app()->appPath();       // /path/to/torrentpier/app
app()->configPath();    // /path/to/torrentpier/config
app()->publicPath();    // /path/to/torrentpier/public
app()->storagePath();   // /path/to/torrentpier/storage
app()->resourcePath();  // /path/to/torrentpier/resources
app()->databasePath();  // /path/to/torrentpier/database
```

### Environment Helpers

```php
app()->isDebug();       // Check if debug mode is enabled
app()->isBooted();      // Check if application has booted
app()->environment();   // Get current environment (production/development)
```

## Service Providers (`app/Providers/`)

Services are now registered through dedicated providers:

| Provider | Responsibility |
|----------|----------------|
| `ConfigServiceProvider` | Configuration management |
| `DatabaseServiceProvider` | Database connections |
| `CacheServiceProvider` | Unified caching system |
| `SessionServiceProvider` | User sessions |
| `HttpServiceProvider` | HTTP kernel, router, request handling |
| `TemplateServiceProvider` | Twig template engine |
| `LegacyServiceProvider` | BBCode, Html, User, LogAction |
| `SearchServiceProvider` | Manticore search integration |
| `AppServiceProvider` | Application-specific bindings |

### Creating Custom Providers

```php
<?php

namespace App\Providers;

use TorrentPier\ServiceProvider;

class MyCustomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings (called immediately)
        $this->app->singleton(MyService::class, function ($app) {
            return new MyService($app->make(Config::class));
        });
    }

    public function boot(): void
    {
        // Bootstrap logic (called after all providers registered)
        // Safe to use other services here
    }
}
```

Add your provider to `bootstrap/providers.php`:

```php
return [
    // ... existing providers
    App\Providers\MyCustomServiceProvider::class,
];
```

## Bootstrap Pipeline (`app/Bootstrap/`)

Application initialization follows explicit order:

1. **LoadEnvironmentVariables** - `.env` loading, base constants (`TIMESTART`, `TIMENOW`, `BB_PATH`)
2. **SetTrustedProxies** - CDN/proxy IP extraction (Cloudflare, Fastly, etc.)
3. **LoadConfiguration** - Config files, `FORUM_PATH` definition
4. **RegisterHelpers** - Helper functions (`app()`, `config()`, `DB()`, `request()`)
5. **HandleExceptions** - Whoops & Tracy error handlers
6. **RegisterProviders** - Service provider registration
7. **BootProviders** - Service provider boot phase
8. **BootApplication** - Legacy initialization (`init_bb.php`)

## Fluent Configuration (`bootstrap/app.php`)

Application setup uses a clean, expressive API:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withBootstrappers([
        LoadEnvironmentVariables::class,
        SetTrustedProxies::class,
        LoadConfiguration::class,
        // ...
    ])
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        admin: __DIR__ . '/../routes/admin.php',
        tracker: __DIR__ . '/../routes/tracker.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias('auth', Authenticate::class);
    })
    ->withExceptions(function (Handler $exceptions): void {
        // Custom exception handling
    })
    ->create();
```

## Singleton Migration

All major classes migrated from singleton pattern to DI:

| Before | After |
|--------|-------|
| `Config::getInstance()` | `app(Config::class)` or `config()` |
| `Database::getInstance()` | `app(Database::class)` or `DB()` |
| `User::getInstance()` | `app(User::class)` or `user()` |
| `UnifiedCacheSystem::getInstance()` | `app(UnifiedCacheSystem::class)` |
| `Template::getInstance()` | `app(Template::class)` or `template()` |
| `Language::getInstance()` | `app(Language::class)` or `lang()` |
| `Censor::getInstance()` | `app(Censor::class)` or `censor()` |

## HTTP Kernel & Middleware

### Middleware Configuration

```php
// In bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    // Add to web middleware group
    $middleware->web(append: [
        StartSession::class,
    ]);

    // Define middleware aliases
    $middleware->alias('auth', Authenticate::class);
    $middleware->alias('session', StartSession::class);
})
```

### Available Middleware

- `StartSession` - Starts user session and initializes locale
- `Authenticate` - Requires authenticated user
- `BootTracker` - Boots tracker environment for announce/scrape

## Entry Point Changes

All requests now go through the front controller:

```php
// public/index.php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->handleRequest();
```

:::note Important
Direct script access (e.g., `/viewtopic.php`) is redirected to clean URLs by the web server.
:::

## Breaking Changes

### Removed Files

- `library/common.php` - Replaced by bootstrap pipeline
- `src/Env.php` - Environment handling moved to bootstrapper
- `src/Database/DatabaseFactory.php` - Replaced by DI
- `src/Router/FrontController.php` - Replaced by Application
- `public/bt/announce.php`, `public/bt/scrape.php` - Replaced by controllers

### Removed Constants

- `IN_DEMO_MODE` - Use `app()->isDebug()` or environment checks

### Removed Functions

- `utime()` - Use `microtime(true)`
- `str_compact()` - Use `Illuminate\Support\Str::squish()`
- `make_rand_str()` - Use `Illuminate\Support\Str::random()`

## Verification

To verify the migration is working correctly:

```php
// Test container resolution
$app = app();
echo "App booted: " . ($app->isBooted() ? 'yes' : 'no');

// Test service resolution
$config = app(TorrentPier\Config::class);
echo "Site name: " . $config->get('sitename');

// Test helper functions (unchanged)
echo "DB version: " . DB()->server_version();
echo "Cache test: " . (CACHE('bb_cache')->get('test') ?? 'empty');

// Test path helpers
echo "Base path: " . app()->basePath();
echo "Storage path: " . app()->storagePath();
```

## Migration for Custom Code

If you have custom code using old patterns:

```php
// Old singleton pattern
$db = TorrentPier\Database\Database::getInstance();

// New DI pattern
$db = app(TorrentPier\Database\Database::class);

// Or use helper (recommended)
$db = DB();
```

For dependency injection in your classes:

```php
// Old way - hidden dependencies
class MyService {
    public function doSomething() {
        $db = DB();  // Hidden dependency
    }
}

// New way - explicit dependencies
class MyService {
    public function __construct(
        private Database $db,
        private Config $config
    ) {}

    public function doSomething() {
        $this->db->sql_query(...);
    }
}

// Register in a service provider
$this->app->singleton(MyService::class);  // Auto-wired!
```
