# Dependency Injection Infrastructure

This directory contains the dependency injection container implementation using PHP-DI, following hexagonal architecture principles.

## Architecture Overview

The DI container is placed in the Infrastructure layer because:
- Dependency injection is a technical implementation detail
- It handles wiring and bootstrapping, not business logic
- The domain layer remains pure without framework dependencies

## Structure

```
DependencyInjection/
├── Container.php           # Wrapper around PHP-DI container
├── ContainerFactory.php    # Factory for building configured containers
├── Bootstrap.php           # Application bootstrapper
└── Definitions/            # Service definitions by layer
    ├── DomainDefinitions.php
    ├── ApplicationDefinitions.php
    ├── InfrastructureDefinitions.php
    └── PresentationDefinitions.php
```

## Usage

### Basic Bootstrap (Works Now)

```php
use TorrentPier\Infrastructure\DependencyInjection\Bootstrap;

// Initialize the container
$container = Bootstrap::init(__DIR__ . '/../..');

// Basic usage
$containerInstance = app(); // Get container itself
$hasService = $container->has('some.service'); // Check if service exists
```

### Manual Container Creation (Works Now)

```php
use TorrentPier\Infrastructure\DependencyInjection\ContainerFactory;

$config = [
    'environment' => 'production',
    'definitions' => [
        'custom.service' => \DI\factory(function () {
            return new CustomService();
        }),
    ],
];

$container = ContainerFactory::create($config);
```

### Future Usage (When Services Are Implemented)

```php
// These will work when the respective layers are implemented:
// $userRepository = $container->get(UserRepositoryInterface::class);
// $commandBus = $container->get(CommandBusInterface::class);
```

## Service Definitions

Services are organized by architectural layer following the hexagonal architecture spec:

### Domain Layer (`DomainDefinitions.php`)
- Repository interface mappings (when implemented in Phase 2)
- Domain service factories
- No direct infrastructure dependencies

### Application Layer (`ApplicationDefinitions.php`)
- Command/Query buses (when implemented in Phase 3)
- Command/Query handlers
- Event dispatcher
- Application services

### Infrastructure Layer (`InfrastructureDefinitions.php`)
- Database connections (when Nette Database integration is ready)
- Cache implementations (when cache infrastructure is ready)
- Repository implementations (when implemented in Phase 4)
- External service adapters

### Presentation Layer (`PresentationDefinitions.php`)
- HTTP controllers (when implemented in Phase 5)
- CLI commands
- Middleware
- Response transformers

**Note**: Most definitions are currently commented out as examples until the actual services are implemented according to the implementation phases.

## Configuration

Configuration is loaded from multiple sources:

1. **Environment Variables** (`.env` file)
2. **Configuration Files** (`/config/*.php`)
3. **Runtime Configuration** (passed to factory)

### Production Optimization

In production mode, the container:
- Compiles definitions for performance
- Generates proxies for lazy loading
- Caches resolved dependencies

Enable by setting `APP_ENV=production` in your `.env` file.

## Best Practices

1. **Use Interfaces**: Define interfaces in domain, implement in infrastructure
2. **Explicit Definitions**: Prefer explicit over magic for complex services
3. **Layer Separation**: Keep definitions organized by architectural layer
4. **Lazy Loading**: Use factories for expensive services
5. **Immutable Services**: Services should be stateless and immutable

## Example Service Registration

### Current Usage (Works Now)
```php
// In services.php or custom definitions
return [
    'custom.service' => \DI\factory(function () {
        return new CustomService();
    }),
    
    'test.service' => \DI\autowire(TestService::class),
];
```

### Future Examples (When Infrastructure Is Ready)
```php
// These will be uncommented when the services are implemented:
// UserRepositoryInterface::class => autowire(UserRepository::class)
//     ->constructorParameter('connection', get('database.connection.default'))
//     ->constructorParameter('cache', get('cache.factory')),
// 
// 'email.service' => factory(function (ContainerInterface $c) {
//     $config = $c->get('config')['email'];
//     return new SmtpEmailService($config);
// }),
```

## Testing

For testing, create a test container with mocked services:

```php
// Current testing approach (works now)
$testConfig = [
    'definitions' => [
        'test.service' => \DI\factory(function () {
            return new MockTestService();
        }),
    ],
    'environment' => 'testing',
];

$container = ContainerFactory::create($testConfig);

// Future testing (when services are implemented)
// $testConfig = [
//     'definitions' => [
//         UserRepositoryInterface::class => $mockUserRepository,
//         EmailServiceInterface::class => $mockEmailService,
//     ],
// ];
```
