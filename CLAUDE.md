# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TorrentPier is a BitTorrent tracker engine written in PHP, designed for hosting BitTorrent communities with forum functionality. The project is undergoing a major 3.0 rewrite, transitioning from legacy code to modern PHP practices. **Backward compatibility is not supported in 3.0** - legacy APIs will break and are not maintained as the focus is on moving forward with clean, modern architecture.

## Technology Stack & Architecture

- **PHP 8.3+** with modern features
- **MySQL/MariaDB/Percona** database
- **Nette Database** for data access (primary)
- **Illuminate Database** for Eloquent ORM (optional)
- **Illuminate Container** for dependency injection
- **Illuminate Support** for collections and helpers
- **Illuminate Collections** for enhanced collection handling
- **Illuminate Events** for event-driven architecture
- **Illuminate Routing** for Laravel-style routing
- **Illuminate Validation** for request validation
- **Illuminate HTTP** for request/response handling
- **Composer** for dependency management
- **Custom BitTorrent tracker** implementation
- **Laravel-style MVC Architecture** with familiar patterns

## Key Directory Structure

### Laravel-style Structure
- `/app/` - Main application directory (PSR-4 autoloaded as `App\`)
  - `/Console/Commands/` - Artisan-style CLI commands for Dexter
  - `/Http/Controllers/` - Web, API, and Admin controllers
  - `/Http/Middleware/` - HTTP middleware
  - `/Http/Routing/` - Routing components (uses Illuminate Routing)
  - `/Models/` - Data models using Nette Database  
  - `/Services/` - Business logic services
  - `/Providers/` - Service providers
  - `/Container/` - Container wrapper and extensions
  - `/Support/` - Helper classes and utilities
- `/bootstrap/` - Application bootstrap files (app.php, container.php)
- `/config/` - Laravel-style configuration files (app.php, database.php, etc.)
- `/database/` - Migrations, seeders, factories
- `/public/` - Web root with front controller (index.php)
- `/resources/` - Views, language files, assets
- `/routes/` - Route definitions (web.php, api.php, admin.php)
- `/storage/` - Application storage (app/, framework/, logs/)
- `dexter` - CLI interface

### Core Utilities & Legacy
- `/src/` - Core utilities and services (PSR-4 autoloaded as `TorrentPier\`)
- `/library/` - Legacy core application logic
- `/controllers/` - Legacy PHP controllers (being migrated)
- `/admin/` - Legacy administrative interface
- `/bt/` - BitTorrent tracker functionality (announce.php, scrape.php)
- `/styles/` - Legacy templates, CSS, JS, images
- `/internal_data/` - Legacy cache, logs, compiled templates

## Entry Points & Key Files

### Modern Entry Points
- `public/index.php` - Laravel-style front controller (web requests)
- `dexter` - CLI interface (console commands)
- `bootstrap/app.php` - Application bootstrap
- `bootstrap/container.php` - Container setup and configuration
- `bootstrap/console.php` - Console bootstrap

### Legacy Entry Points (Backward Compatibility)
- `bt/announce.php` - BitTorrent announce endpoint
- `bt/scrape.php` - BitTorrent scrape endpoint
- `admin/index.php` - Legacy administrative panel
- `cron.php` - Background task runner

## Development Commands

### Installation & Setup
```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

### Maintenance & Operations
```bash
# Run background maintenance tasks
php cron.php
```

### Code Quality
The project uses **StyleCI** with PSR-2 preset for code style enforcement. StyleCI configuration is in `.styleci.yml` targeting `src/` directory.

## MVC Architecture Components

### Models (`/app/Models/`)
- **Simple Active Record pattern** using Nette Database (primary)
- **Eloquent ORM** available via Illuminate Database (optional)
- Base `Model` class provides common CRUD operations
- No complex ORM required, just straightforward database access
- Example: `Torrent`, `User`, `Forum`, `Post` models

### Controllers (`/app/Http/Controllers/`)
- **Thin controllers** that delegate to services
- Organized by area: `Web/`, `Api/`, `Admin/`
- `LegacyController` maintains backward compatibility
- Base `Controller` class provides common methods

### Services (`/app/Services/`)
- **Business logic layer** between controllers and models
- Handles complex operations and workflows
- Example: `TorrentService`, `AuthService`, `ForumService`
- Injected via dependency injection

### Views (`/resources/views/`)
- **PHP templates** (planning future Twig integration)
- Organized by feature areas
- Layouts for consistent structure
- Partials for reusable components

## Infrastructure Components

### Database Layer (`/src/Database/`)
- **Nette Database** for all data access (primary)
- **Illuminate Database** available for Eloquent ORM features
- Modern singleton pattern accessible via `DB()` function
- Support for multiple database connections and debug functionality
- Direct SQL queries when needed

### Cache System (`/src/Cache/`)
- **Unified caching** using Nette Caching internally
- Replaces existing `CACHE()` and $datastore systems
- Supports file, SQLite, memory, and Memcached storage
- Used by services and repositories

### Configuration Management
- Environment-based config with `.env` files
- **Illuminate Config** for Laravel-style configuration
- Modern singleton `Config` class accessible via `config()` function
- Configuration files in `/config/` directory

### Event System (`/app/Events/` & `/app/Listeners/`)
- **Illuminate Events** for decoupled, event-driven architecture
- Event classes in `/app/Events/`
- Listener classes in `/app/Listeners/`
- Event-listener mappings in `EventServiceProvider`
- Global `event()` helper function for dispatching events
- Support for queued listeners (when queue system is configured)

### Routing System
- **Illuminate Routing** for full Laravel-compatible routing (as of TorrentPier 3.0)
- Route definitions in `/routes/` directory (web.php, api.php, admin.php)
- Support for route groups, middleware, named routes, route model binding
- Resource controllers and RESTful routing patterns
- Custom `IlluminateRouter` wrapper for smooth Laravel integration
- Legacy custom router preserved as `LegacyRouter` for reference
- Backward compatibility maintained through `Router` alias

### Validation Layer
- **Illuminate Validation** for robust input validation
- Form request classes in `/app/Http/Requests/`
- Base `FormRequest` class for common validation logic
- Custom validation rules and messages
- Automatic validation exception handling

### HTTP Layer
- **Illuminate HTTP** for request/response handling
- Middleware support for request filtering
- JSON response helpers and content negotiation

## Configuration Files
- `.env` - Environment variables (copy from `.env.example`)
- `library/config.php` - Main application configuration
- `library/config.local.php` - Local configuration overrides
- `composer.json` - Dependencies and PSR-4 autoloading

## Development Workflow

### CI/CD Pipeline
- **GitHub Actions** for automated testing and deployment
- **StyleCI** for code style enforcement
- **Dependabot** for dependency updates

### Installation Methods
1. **Composer**: `composer create-project torrentpier/torrentpier`
2. **Manual**: Git clone + `composer install`

## Database & Schema

- **Database migrations** managed via Phinx in `/database/migrations/` directory
- Initial schema: `20250619000001_initial_schema.php`
- Initial seed data: `20250619000002_seed_initial_data.php`
- UTF-8 (utf8mb4) character set required
- Multiple database alias support for different components

### Migration Commands
```bash
# Run all pending migrations
php vendor/bin/phinx migrate --configuration=phinx.php

# Check migration status
php vendor/bin/phinx status --configuration=phinx.php

# Mark migrations as applied (for existing installations)
php vendor/bin/phinx migrate --fake --configuration=phinx.php
```

## TorrentPier 3.0 Modernization Strategy

The TorrentPier 3.0 release represents a major architectural shift to Laravel-style MVC:

- **Laravel-style MVC Architecture**: Clean Model-View-Controller pattern
- **Illuminate Container**: Laravel's dependency injection container
- **Modern PHP practices**: PSR standards, namespaces, autoloading
- **Developer friendly**: Familiar Laravel patterns for easier contribution
- **Performance improvements**: Optimized database queries, efficient caching
- **Breaking changes**: Legacy code removal and API modernization

**Important**: TorrentPier 3.0 will introduce breaking changes to achieve these modernization goals. Existing deployments should remain on 2.x versions until they're ready to migrate to the new architecture.

## Current Architecture

### Container & Dependency Injection
- **Illuminate Container**: Laravel's container for dependency injection
- **Bootstrap**: Clean container setup in `/bootstrap/container.php`
- **Service Providers**: Laravel-style providers in `/app/Providers/`
- **Helper Functions**: Global helpers - `app()`, `config()`, `event()`

### MVC Structure
- **Controllers**: All controllers in `/app/Http/Controllers/`
- **Models**: Simple models in `/app/Models/` (using Nette Database or Eloquent)
- **Services**: Business logic in `/app/Services/`
- **Routes**: Laravel-style route definitions in `/routes/`
- **Middleware**: HTTP middleware in `/app/Http/Middleware/`
- **Events**: Event classes in `/app/Events/`
- **Listeners**: Event listeners in `/app/Listeners/`
- **Requests**: Form request validation in `/app/Http/Requests/`

### Migration Steps for New Features
1. Create models in `/app/Models/` extending base `Model` class (or Eloquent models)
2. Add business logic to services in `/app/Services/`
3. Create form request classes in `/app/Http/Requests/` for validation
4. Create thin controllers in `/app/Http/Controllers/`
5. Define routes in `/routes/` files using Illuminate Routing syntax
6. Create events in `/app/Events/` and listeners in `/app/Listeners/`
7. Register event listeners in `EventServiceProvider`
8. Use helper functions: `app()`, `config()`, `event()` for easy access

### What to Avoid
- Don't use complex DDD patterns (aggregates, value objects)
- Don't implement CQRS or event sourcing
- Don't create repository interfaces (use concrete classes if needed)
- Don't over-engineer - keep it simple and Laravel-like

When working with this codebase, prioritize simplicity and maintainability. New features should be built in the `/app/` directory using Laravel-style MVC patterns.

## Example Usage

### Events and Listeners
```php
// Dispatch an event from a service
use App\Events\UserRegistered;

event(new UserRegistered($user));

// Create an event listener
class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        // Send welcome email to $event->user
    }
}
```

### Form Validation
```php
// Create a form request class
class RegisterUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users',
            'username' => 'required|string|min:3|max:20',
            'password' => 'required|string|min:8',
        ];
    }
}

// Use in controller
public function register(RegisterUserRequest $request): JsonResponse
{
    // Request is automatically validated
    $validated = $request->validated();
    // ... create user
}
```

### Routing with Groups and Middleware
```php
// In routes/api.php
$router->group(['prefix' => 'v1', 'middleware' => 'auth'], function () use ($router) {
    $router->resource('torrents', 'TorrentController');
    $router->get('stats', 'StatsController::index');
});
```

### Using Collections
```php
use Illuminate\Support\Collection;

$users = collect(User::all());
$activeUsers = $users->filter(fn($user) => $user->isActive())
                    ->sortBy('last_seen')
                    ->take(10);
```

### Middleware Usage
```php
// Apply middleware to routes
$router->middleware(['auth', 'admin'])->group(function () use ($router) {
    $router->get('/admin/users', 'AdminController::users');
});

// Create custom middleware
class CustomMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        // Middleware logic here
        return $next($request);
    }
}
```

## Markdown File Guidelines

When creating or editing `.md` files in this project, follow these linting rules:

### MD032 - Lists surrounded by blank lines
Always add blank lines before and after lists:

```markdown
Some text here.

- First item
- Second item
- Third item

More text here.
```

### MD047 - Files should end with a single newline
Ensure every markdown file ends with exactly one newline character at the end of the file.

## Tests

- Need comprehensive test coverage for new components
- Utilize Pest PHP for unit and integration / feature testing
- Tests should focus on validating modern architecture components
- Create test suites for critical systems like database, cache, and configuration

## Development Guidelines

- Always ensure that there is one empty line at the end of the file
