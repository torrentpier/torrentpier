# 🧪 TorrentPier 3.0 Testing Infrastructure

This document outlines the testing infrastructure for TorrentPier 3.0, built using **Pest PHP** and following Laravel-style MVC patterns.

## 📖 Table of Contents

- [Overview](#overview)
- [Laravel-style Testing](#laravel-style-testing)
- [Test Organization](#test-organization)
- [Container Testing](#container-testing)
- [Testing Patterns](#testing-patterns)
- [Test Execution](#test-execution)
- [Best Practices](#best-practices)

## 🎯 Overview

TorrentPier 3.0's testing suite is designed following Laravel-style testing patterns:

- **Unit Tests**: Test individual classes and methods in isolation
- **Feature Tests**: Test HTTP endpoints and user workflows
- **Integration Tests**: Test database operations and external services

### Core Testing Principles

1. **Laravel-style**: Tests follow familiar Laravel testing patterns
2. **Simple & Clear**: Straightforward test organization and execution
3. **Clean Slate**: No legacy dependencies, modern PHP 8.3+ testing
4. **Container-Driven**: Use Illuminate Container for dependency injection
5. **MVC-Focused**: Test controllers, models, and services separately

## 🏗️ Laravel-style Testing

### Testing Strategy by Component

#### Model Testing (Unit Tests)
```php
// Test model behavior and business logic
it('calculates user ratio correctly', function () {
    $user = new \App\Models\User();
    $user->uploaded = 1000000;
    $user->downloaded = 500000;
    
    expect($user->getRatio())->toBe(2.0);
});
```

#### Service Testing (Unit Tests)
```php
// Test service layer with mocked dependencies
it('registers user through service', function () {
    $userService = new \App\Services\UserService();
    
    $result = $userService->register('john', 'john@example.com');
    
    expect($result)->toBeInstanceOf(\App\Models\User::class);
});
```

#### Controller Testing (Feature Tests)
```php
// Test HTTP endpoints and responses
it('handles user registration via API', function () {
    $response = $this->postJson('/api/users/register', [
        'username' => 'john',
        'email' => 'john@example.com',
    ]);
    
    $response->assertStatus(201);
    $response->assertJson(['username' => 'john']);
});
```

#### Container Testing (Integration Tests)
```php
// Test dependency injection and service resolution
it('resolves services from container', function () {
    $container = app();
    
    expect($container->bound(\App\Services\UserService::class))->toBeTrue();
    expect($container->make(\App\Services\UserService::class))->toBeInstanceOf(\App\Services\UserService::class);
});
```

## 📁 Test Organization

### Directory Structure

```
tests/
├── README.md                           # This documentation
├── Pest.php                            # Clean Pest configuration
├── TestCase.php                        # Base test case with container utilities
├── Unit/                               # Unit tests
│   ├── Models/                         # Model tests
│   │   ├── UserTest.php
│   │   ├── TorrentTest.php
│   │   └── ForumTest.php
│   ├── Services/                       # Service tests
│   │   ├── UserServiceTest.php
│   │   ├── TorrentServiceTest.php
│   │   └── AuthServiceTest.php
│   ├── Container/                      # Container and DI tests
│   │   ├── ContainerTest.php
│   │   └── ServiceProviderTest.php
│   └── Support/                        # Helper and utility tests
│       ├── HelperTest.php
│       └── FactoryTest.php
└── Feature/                           # Feature/integration tests
    ├── Http/                          # HTTP endpoint tests
    │   ├── Web/
    │   │   ├── HomeTest.php
    │   │   └── AuthTest.php
    │   └── Api/
    │       ├── UserApiTest.php
    │       └── TorrentApiTest.php
    ├── Database/                      # Database integration tests
    │   ├── MigrationTest.php
    │   └── SeedingTest.php
    └── Cache/                         # Cache integration tests
        └── CacheTest.php
```

## 🛠️ Container Testing

### Current Implementation

The Illuminate Container is the foundation of TorrentPier 3.0's dependency injection. Our tests ensure:

#### Container Resolution Testing
```php
// tests/Unit/Container/ContainerTest.php
it('resolves services from container', function () {
    $container = app();
    expect($container)->toBeInstanceOf(\Illuminate\Container\Container::class);
});

it('can bind and resolve services', function () {
    $container = $this->createTestContainer([
        'test.service' => fn() => 'test_value'
    ]);
    
    expect($container->make('test.service'))->toBe('test_value');
});
```

#### Service Provider Testing
```php
// tests/Unit/Container/ServiceProviderTest.php
it('registers services through providers', function () {
    $provider = new \App\Providers\AppServiceProvider(app());
    $provider->register();
    
    // Assert services are registered
    expect(app()->bound('some.service'))->toBeTrue();
});
```

### Test Utilities

#### Enhanced TestCase
```php
// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    protected function createTestContainer(array $bindings = []): Container
    {
        $container = new Container();
        
        foreach ($bindings as $abstract => $concrete) {
            $container->bind($abstract, $concrete);
        }
        
        return $container;
    }
    
    protected function app(?string $abstract = null): mixed
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }
        
        return Container::getInstance()->make($abstract);
    }
}
```

## 🎨 Testing Patterns

### 1. Unit Testing
```php
// Test individual components
it('validates user email', function () {
    $user = new \App\Models\User();
    $user->email = 'invalid-email';
    
    expect($user->isValidEmail())->toBeFalse();
});
```

### 2. Feature Testing
```php
// Test HTTP endpoints
it('returns user profile data', function () {
    $response = $this->get('/api/users/1');
    
    $response->assertStatus(200);
    $response->assertJsonStructure(['id', 'username', 'email']);
});
```

### 3. Integration Testing
```php
// Test service interactions
it('creates user with dependencies', function () {
    $userService = app(\App\Services\UserService::class);
    $user = $userService->create(['username' => 'john', 'email' => 'john@example.com']);
    
    expect($user)->toBeInstanceOf(\App\Models\User::class);
    expect($user->username)->toBe('john');
});
```

## 🚀 Test Execution

### Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Run unit tests only
./vendor/bin/pest tests/Unit/

# Run feature tests only
./vendor/bin/pest tests/Feature/

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test file
./vendor/bin/pest tests/Unit/Models/UserTest.php
```

### Performance Testing
```bash
# Test specific functionality
./vendor/bin/pest --filter="container"

# Container creation should be fast
expectExecutionTimeUnder(fn() => app(\App\Services\UserService::class), 0.1);
```

## 📋 Best Practices

### 1. Clear Test Names
```php
// Good: descriptive test names
it('calculates correct torrent ratio when user has downloads', function () {
    // Test implementation
});

// Bad: vague test names
it('tests ratio', function () {
    // Test implementation
});
```

### 2. Arrange-Act-Assert Pattern
```php
it('creates user with valid data', function () {
    // Arrange
    $userData = ['username' => 'john', 'email' => 'john@example.com'];
    $userService = app(\App\Services\UserService::class);
    
    // Act
    $user = $userService->create($userData);
    
    // Assert
    expect($user)->toBeInstanceOf(\App\Models\User::class);
    expect($user->username)->toBe('john');
});
```

### 3. Use Container for Dependencies
```php
// Good: use container
it('service uses correct dependencies', function () {
    $service = app(\App\Services\UserService::class);
    // Test service behavior
});

// Avoid: manual instantiation
it('service works', function () {
    $service = new \App\Services\UserService(); // Missing dependencies
    // Test may fail due to missing dependencies
});
```

## 📊 Current Implementation Status

### ✅ Clean Testing Foundation

- **Illuminate Container**: Full container-based testing
- **Laravel-style Patterns**: Familiar testing approaches
- **Pest PHP**: Modern, expressive test syntax
- **MVC Testing**: Separate testing for models, services, controllers
- **Feature Tests**: HTTP endpoint and integration testing

### 🔄 Testing Coverage Goals

- **Models**: Test business logic and data validation
- **Services**: Test service layer and business workflows
- **Controllers**: Test HTTP responses and request handling
- **Container**: Test dependency injection and service resolution
- **Integration**: Test database operations and external services

---

**TorrentPier 3.0 Testing Philosophy**: Tests serve as both validation and documentation of the Laravel-style MVC architecture. Clear, simple tests ensure maintainable code and confident development.

For questions about testing patterns or contributions, refer to the [TorrentPier GitHub repository](https://github.com/torrentpier/torrentpier).