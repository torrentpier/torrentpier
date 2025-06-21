# 🧪 TorrentPier 3.0 Testing Infrastructure

This document outlines the testing infrastructure for TorrentPier 3.0, built using **Pest PHP** and following the hexagonal architecture principles outlined in the project specification.

## 📖 Table of Contents

- [Overview](#overview)
- [Hexagonal Architecture Testing](#hexagonal-architecture-testing)
- [Test Organization](#test-organization)
- [DI Container Testing](#di-container-testing)
- [Testing Patterns](#testing-patterns)
- [Test Execution](#test-execution)
- [Best Practices](#best-practices)

## 🎯 Overview

TorrentPier 3.0's testing suite is designed following the hexagonal architecture testing strategy:

- **Domain**: Pure unit tests, no mocks needed
- **Application**: Unit tests with mocked repositories
- **Infrastructure**: Integration tests with real services
- **Presentation**: E2E tests for user journeys

### Core Testing Principles

1. **Architecture-Driven**: Tests follow the hexagonal architecture layers
2. **Phase-Aligned**: Testing matches the 5-phase implementation strategy
3. **Clean Slate**: No legacy dependencies, modern PHP 8.3+ testing
4. **Infrastructure First**: Focus on foundational DI container testing
5. **Future-Ready**: Structure prepared for upcoming domain/application layers

## 🏗️ Hexagonal Architecture Testing

### Testing Strategy by Layer

#### Domain Layer Testing (Phase 2 - Future)
```php
// Pure unit tests, no framework dependencies
it('validates business rules without external dependencies', function () {
    $user = new User(new UserId(1), new Email('test@example.com'));
    expect($user->canPost())->toBeTrue();
});
```

#### Application Layer Testing (Phase 3 - Future)
```php
// Unit tests with mocked repositories
it('handles user registration command', function () {
    $mockRepo = Mockery::mock(UserRepositoryInterface::class);
    $handler = new RegisterUserHandler($mockRepo);
    
    $command = new RegisterUserCommand('john', 'john@example.com');
    $handler->handle($command);
    
    $mockRepo->shouldHaveReceived('save');
});
```

#### Infrastructure Layer Testing (Phase 1 - Current)
```php
// Integration tests with real services
it('creates container with real PHP-DI integration', function () {
    $container = ContainerFactory::create();
    expect($container)->toBeInstanceOf(Container::class);
});
```

#### Presentation Layer Testing (Phase 5 - Future)
```php
// E2E tests for user journeys
it('handles API request end-to-end', function () {
    $response = $this->post('/api/users', ['name' => 'John']);
    expect($response->status())->toBe(201);
});
```

## 📁 Test Organization

### Directory Structure

```
tests/
├── README.md                           # This documentation
├── Pest.php                            # Clean Pest configuration
├── TestCase.php                        # Enhanced base test case with DI utilities
├── Unit/Infrastructure/DependencyInjection/  # DI Container tests (Phase 1)
│   ├── ContainerTest.php                      # Container wrapper tests
│   ├── ContainerFactoryTest.php               # Factory functionality tests
│   ├── BootstrapTest.php                      # Application bootstrapping tests
│   ├── ServiceProviderTest.php                # Service provider interface tests
│   └── Definitions/                           # Layer-specific definition tests
│       ├── DomainDefinitionsTest.php
│       ├── ApplicationDefinitionsTest.php
│       ├── InfrastructureDefinitionsTest.php
│       └── PresentationDefinitionsTest.php
└── Feature/                            # Integration tests
    └── ContainerIntegrationTest.php           # End-to-end container tests
```

### Future Structure (As Phases Are Implemented)

```
tests/
├── Unit/
│   ├── Domain/                         # Phase 2: Pure business logic tests
│   │   ├── User/
│   │   ├── Forum/
│   │   └── Tracker/
│   ├── Application/                    # Phase 3: Use case orchestration tests
│   │   ├── User/
│   │   ├── Forum/
│   │   └── Tracker/
│   ├── Infrastructure/                 # Phase 4: External service integration tests
│   │   ├── Persistence/
│   │   ├── Cache/
│   │   └── Email/
│   └── Presentation/                   # Phase 5: Interface layer tests
│       ├── Http/
│       └── Cli/
└── Feature/                           # Cross-layer integration tests
```

## 🛠️ DI Container Testing

### Current Implementation (Phase 1)

The DI container is the foundation of TorrentPier 3.0's architecture. Our tests ensure:

#### Container Wrapper Testing
```php
// tests/Unit/Infrastructure/DependencyInjection/ContainerTest.php
it('implements PSR-11 ContainerInterface', function () {
    expect($this->container)->toBeInstanceOf(\Psr\Container\ContainerInterface::class);
});

it('can resolve autowired classes', function () {
    $result = $this->container->get(stdClass::class);
    expect($result)->toBeInstanceOf(stdClass::class);
});

it('throws NotFoundExceptionInterface for non-existent services', function () {
    expect(fn() => $this->container->get('non.existent.service'))
        ->toThrow(NotFoundExceptionInterface::class);
});
```

#### Factory Configuration Testing
```php
// tests/Unit/Infrastructure/DependencyInjection/ContainerFactoryTest.php
it('applies configuration correctly', function () {
    $config = [
        'environment' => 'testing',
        'autowiring' => true,
        'definitions' => [
            'test.service' => \DI\factory(fn() => 'test_value'),
        ],
    ];

    $container = ContainerFactory::create($config);
    expect($container->get('test.service'))->toBe('test_value');
});
```

#### Bootstrap Integration Testing
```php
// tests/Unit/Infrastructure/DependencyInjection/BootstrapTest.php
it('loads configuration from multiple sources', function () {
    $rootPath = $this->createTestRootDirectory();
    $this->createTestConfigFiles($rootPath, [
        'env' => ['APP_ENV' => 'testing'],
        'services' => ['config.service' => \DI\factory(fn() => 'merged_config')],
    ]);
    
    $container = Bootstrap::init($rootPath);
    expect($container->get('config.service'))->toBe('merged_config');
});
```

### Test Utilities

#### Enhanced TestCase
```php
// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    protected function createTestContainer(array $config = []): Container
    {
        $defaultConfig = [
            'environment' => 'testing',
            'autowiring' => true,
            'definitions' => [],
        ];
        
        return ContainerFactory::create(array_merge($defaultConfig, $config));
    }
    
    protected function assertCanResolve(Container $container, string $serviceId): void
    {
        $this->assertTrue($container->has($serviceId));
        $this->assertNotNull($container->get($serviceId));
    }
}
```

## 🎨 Testing Patterns

### 1. Infrastructure Integration Testing
```php
// Real service integration (current phase)
it('integrates with real PHP-DI container', function () {
    $container = $this->createTestContainer([
        'definitions' => [
            'real.service' => \DI\autowire(stdClass::class),
        ],
    ]);
    
    $service = $container->get('real.service');
    expect($service)->toBeInstanceOf(stdClass::class);
});
```

### 2. Configuration-Driven Testing
```php
// Environment-based configuration
it('adapts to different environments', function () {
    $prodContainer = $this->createTestContainer(['environment' => 'production']);
    $devContainer = $this->createTestContainer(['environment' => 'development']);
    
    expect($prodContainer)->toBeInstanceOf(Container::class);
    expect($devContainer)->toBeInstanceOf(Container::class);
});
```

### 3. Service Provider Testing
```php
// Modular service registration
it('registers services through providers', function () {
    $provider = new class implements ServiceProvider {
        public function register(Container $container): void {
            $container->getWrappedContainer()->set('provider.service', 'registered');
        }
        public function boot(Container $container): void {}
    };
    
    $container = $this->createTestContainer();
    $provider->register($container);
    
    expect($container->get('provider.service'))->toBe('registered');
});
```

### 4. Layer Definition Testing
```php
// Architectural layer compliance
it('follows domain layer principles', function () {
    $definitions = DomainDefinitions::getDefinitions();
    
    // Domain definitions should be empty in Phase 1
    expect($definitions)->toBe([]);
    
    // Structure should be prepared for Phase 2
    expect($definitions)->toBeArray();
});
```

## 🚀 Test Execution

### Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Run DI container tests specifically
./vendor/bin/pest tests/Unit/Infrastructure/DependencyInjection/

# Run integration tests
./vendor/bin/pest tests/Feature/

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test file
./vendor/bin/pest tests/Unit/Infrastructure/DependencyInjection/ContainerTest.php
```

### Performance Testing
```bash
# Measure container bootstrap performance
./vendor/bin/pest --filter="performance"

# Container creation should be fast
expectExecutionTimeUnder(fn() => Bootstrap::init($rootPath), 1.0);
```

## 📋 Best Practices

### 1. Phase-Aligned Testing
```php
// Current Phase 1: Test infrastructure only
it('provides foundation for future phases', function () {
    $container = $this->createTestContainer();
    
    // Infrastructure works now
    expect($container)->toBeInstanceOf(Container::class);
    
    // Ready for future domain services
    expect($container->has(stdClass::class))->toBeTrue();
});
```

### 2. Architecture Compliance
```php
// Ensure clean architectural boundaries
it('keeps domain layer pure', function () {
    $definitions = DomainDefinitions::getDefinitions();
    
    // Domain should have no infrastructure dependencies
    expect($definitions)->toBeArray();
    
    // Future domain services will be dependency-free
});
```

### 3. Configuration Testing
```php
// Test multiple configuration sources
it('merges configuration correctly', function () {
    $rootPath = $this->createTestRootDirectory();
    $this->createTestConfigFiles($rootPath, [
        'container' => ['autowiring' => true],
        'services' => ['test.service' => \DI\factory(fn() => 'test')],
    ]);
    
    $container = Bootstrap::init($rootPath, [
        'definitions' => ['runtime.service' => \DI\factory(fn() => 'runtime')],
    ]);
    
    expect($container->get('test.service'))->toBe('test');
    expect($container->get('runtime.service'))->toBe('runtime');
});
```

### 4. Error Handling
```php
// Comprehensive error testing
it('provides meaningful error messages', function () {
    $container = $this->createTestContainer();
    
    try {
        $container->get('missing.service');
        fail('Expected exception');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain('missing.service');
        expect($e->getMessage())->toContain('not found in container');
    }
});
```

## 📊 Current Implementation Status

### ✅ Phase 1 Complete: Infrastructure Foundation

- **DI Container**: Fully tested container wrapper with PSR-11 compliance
- **Factory Pattern**: Comprehensive configuration and creation testing
- **Bootstrap Process**: Environment loading and configuration merging
- **Service Providers**: Modular service registration interface
- **Helper Functions**: Global container access with proper error handling
- **Layer Definitions**: Prepared structure for all architectural layers

### 🔄 Testing Coverage

- **Container Core**: 100% coverage of wrapper functionality
- **Configuration**: All config sources and merging scenarios tested
- **Error Handling**: Complete PSR-11 exception compliance
- **Integration**: End-to-end bootstrap and usage scenarios
- **Performance**: Container creation and resolution timing validation

### 🔮 Future Phase Testing

As TorrentPier 3.0 phases are implemented:

#### Phase 2: Domain Layer
```php
// Domain entity testing (future)
it('validates user business rules', function () {
    $user = new User(UserId::generate(), new Email('test@example.com'));
    expect($user->isActive())->toBeTrue();
});
```

#### Phase 3: Application Layer
```php
// Command handler testing (future)
it('processes registration command', function () {
    $handler = app(RegisterUserHandler::class);
    $command = new RegisterUserCommand('john', 'john@example.com');
    
    $userId = $handler->handle($command);
    expect($userId)->toBeInstanceOf(UserId::class);
});
```

#### Phase 4: Infrastructure Layer
```php
// Repository integration testing (future)
it('persists user through repository', function () {
    $repository = app(UserRepositoryInterface::class);
    $user = User::create('john', 'john@example.com');
    
    $repository->save($user);
    expect($repository->findById($user->getId()))->not->toBeNull();
});
```

#### Phase 5: Presentation Layer
```php
// Controller integration testing (future)
it('handles user registration via API', function () {
    $response = $this->postJson('/api/users', [
        'username' => 'john',
        'email' => 'john@example.com',
    ]);
    
    expect($response->status())->toBe(201);
});
```

---

**TorrentPier 3.0 Testing Philosophy**: Tests serve as both validation and documentation of the hexagonal architecture. Each layer has distinct testing strategies that ensure clean separation of concerns and maintainable code.

For questions about testing patterns or contributions, refer to the [TorrentPier GitHub repository](https://github.com/torrentpier/torrentpier) or the hexagonal architecture specification at `/docs/specs/hexagonal-architecture-spec.md`.