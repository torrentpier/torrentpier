# Illuminate Package Integration Guide

This document outlines the integration of Laravel's Illuminate packages in TorrentPier 3.0.

> **Note**: After these changes, run `composer update` to install the newly added `illuminate/config` package.

## Installed Illuminate Packages

The following Illuminate packages are available in TorrentPier:

1. **illuminate/collections** - Powerful array/collection manipulation
2. **illuminate/config** - Configuration repository system
3. **illuminate/container** - Dependency injection container
4. **illuminate/database** - Database query builder and Eloquent ORM
5. **illuminate/events** - Event dispatcher system
6. **illuminate/http** - HTTP request/response handling
7. **illuminate/routing** - Routing system
8. **illuminate/support** - Support utilities (Str, Arr, etc.)
9. **illuminate/validation** - Data validation

## Current Integration Status

### ✅ Fully Integrated

1. **Container (illuminate/container)**
   - Used as the core DI container via `bootstrap/container.php`
   - Custom wrapper in `App\Container\Container`
   - Global `app()` helper function available

2. **Support (illuminate/support)**
   - Helper functions: `collect()`, `str()`, `data_get()`, `data_set()`, `tap()`, `optional()`
   - String and array manipulation utilities
   - Collection class for data manipulation

3. **Events (illuminate/events)**
   - Event dispatcher registered in `AppServiceProvider`
   - Event classes in `app/Events/`
   - Listener classes in `app/Listeners/`
   - `EventServiceProvider` for registering event listeners
   - Global `event()` helper function

4. **Config (illuminate/config)**
   - Configuration repository registered in `AppServiceProvider`
   - Global `config()` helper function
   - Loads all PHP files from `/config/` directory

5. **Validation (illuminate/validation)**
   - Used in `App\Http\Requests\FormRequest` base class
   - Provides Laravel-style request validation

### ⚠️ Partially Integrated

1. **HTTP (illuminate/http)**
   - Request/Response classes used in routing
   - Not fully utilizing all HTTP features

2. **Collections (illuminate/collections)**
   - Available via `collect()` helper
   - Could be used more extensively in models/services

### ❌ Not Yet Integrated

1. **Database (illuminate/database)**
   - Package installed but not used
   - Project uses Nette Database instead
   - Could migrate to Eloquent ORM in future

2. **Routing (illuminate/routing)**
   - Package installed but custom router is used
   - Could migrate to full Illuminate routing

## Usage Examples

### Using the Event System

```php
// Dispatch an event
use App\Events\UserRegistered;

event(new UserRegistered(
    userId: $userId,
    username: $username,
    email: $email,
    registeredAt: new DateTime()
));

// In a listener
class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        // Send welcome email
        mail($event->getEmail(), 'Welcome!', 'Welcome to TorrentPier!');
    }
}
```

### Using Configuration

```php
// Get a config value
$appName = config('app.name');
$debug = config('app.debug', false);

// Set a config value
config(['app.timezone' => 'UTC']);

// Get entire config array
$databaseConfig = config('database');
```

### Using Collections

```php
// Create a collection
$users = collect($userArray);

// Chain methods
$activeAdmins = $users
    ->where('status', 'active')
    ->where('role', 'admin')
    ->sortBy('name')
    ->values();

// Use collection helpers
$names = $users->pluck('name');
$grouped = $users->groupBy('role');
```

### Using Validation

```php
use App\Http\Requests\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:25',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];
    }
}

// In controller
public function register(RegisterRequest $request)
{
    $validated = $request->validated();
    // Process registration with validated data
}
```

## Service Providers

Service providers bootstrap the Illuminate packages:

1. **AppServiceProvider** - Registers config and events
2. **EventServiceProvider** - Maps events to listeners
3. **RouteServiceProvider** - Loads route files

Register new providers in `bootstrap/container.php`.

## Future Integration Opportunities

1. **Migrate to Eloquent ORM**
   - Replace Nette Database with Eloquent
   - Use migrations and model relationships
   - Leverage query scopes and mutators

2. **Full Routing Integration**
   - Replace custom router with Illuminate routing
   - Use route model binding
   - Implement route caching

3. **Add Queue System**
   - Install `illuminate/queue`
   - Process long-running tasks asynchronously
   - Integrate with event listeners

4. **Add Cache Integration**
   - Install `illuminate/cache`
   - Replace custom cache with Laravel's cache
   - Use cache tags and TTL

5. **Add Filesystem Integration**
   - Already have `league/flysystem`
   - Add `illuminate/filesystem` for Laravel integration
   - Unified file operations

## Best Practices

1. **Use Dependency Injection**
   - Inject services via constructor
   - Use the container for resolution
   - Avoid service location pattern

2. **Leverage Events**
   - Decouple components with events
   - Use listeners for side effects
   - Consider queued listeners for heavy tasks

3. **Configuration Management**
   - Keep environment-specific values in `.env`
   - Use config files for application settings
   - Cache configuration in production

4. **Follow Laravel Conventions**
   - Use Laravel naming conventions
   - Structure code like a Laravel app
   - Leverage Laravel patterns and practices