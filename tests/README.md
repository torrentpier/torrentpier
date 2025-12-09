# 🧪 TorrentPier Testing Infrastructure

This document outlines the comprehensive testing infrastructure for TorrentPier, built using **Pest PHP**, a modern testing framework for PHP that provides an elegant and developer-friendly testing experience.

## 📖 Table of Contents

- [Overview](#overview)
- [Testing Architecture](#testing-architecture)
- [Test Organization](#test-organization)
- [Testing Patterns](#testing-patterns)
- [Database Testing](#database-testing)
- [Cache Testing](#cache-testing)
- [Mocking and Fixtures](#mocking-and-fixtures)
- [Test Execution](#test-execution)
- [Best Practices](#best-practices)
- [CI/CD Integration](#cicd-integration)

## 🎯 Overview

TorrentPier's testing suite is designed to provide comprehensive coverage of all components with a focus on:

- **Unit Testing**: Testing individual classes and methods in isolation
- **Integration Testing**: Testing component interactions and system behavior
- **Feature Testing**: Testing complete workflows and user scenarios
- **Architecture Testing**: Ensuring code follows architectural principles
- **Performance Testing**: Validating performance requirements

### Core Testing Principles

1. **Test-First Development**: Write tests before or alongside code development
2. **Comprehensive Coverage**: Aim for high test coverage across all components
3. **Fast Execution**: Tests should run quickly to encourage frequent execution
4. **Reliable Results**: Tests should be deterministic and consistent
5. **Clear Documentation**: Tests serve as living documentation of system behavior

## 🏗️ Testing Architecture

### Framework: Pest PHP

We use **Pest PHP** for its elegant syntax and powerful features:

```php
// Traditional PHPUnit style
it('validates user input', function () {
    $result = validateEmail('test@example.com');
    expect($result)->toBeTrue();
});

// Higher Order Testing
it('creates user successfully')
    ->expect(fn() => User::create(['email' => 'test@example.com']))
    ->toBeInstanceOf(User::class);
```

### Key Features Used

- **Expectation API**: Fluent assertions with `expect()`
- **Higher Order Testing**: Simplified test syntax
- **Datasets**: Parameterized testing with data providers
- **Architecture Testing**: Code structure validation
- **Mocking**: Test doubles with Mockery integration
- **Parallel Execution**: Faster test runs with concurrent testing

### Base Test Case

```php
// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    // Minimal base test case - most setup is handled in Pest.php global helpers
}
```

### Global Test Helpers (Pest.php)

The `tests/Pest.php` file contains extensive helper functions and mocks for testing TorrentPier components:

#### Environment Setup
- `setupTestEnvironment()` - Defines required constants for testing
- `getTestDatabaseConfig()` / `getInvalidDatabaseConfig()` - Database configuration fixtures
- `createTestCacheConfig()` - Cache configuration for testing

#### Mock Factories
- `mockDatabase()` - Creates Database class mocks with standard expectations
- `mockDatabaseDebugger()` - Creates DatabaseDebugger mocks
- `mockCacheManager()` / `mockDatastoreManager()` - Cache component mocks
- `mockConnection()` / `mockPdo()` / `mockPdoStatement()` - Low-level database mocks

#### Test Data Generators
- `createTestUser()` / `createTestTorrent()` - Generate test entity data
- `createSelectQuery()` / `createInsertQuery()` / `createUpdateQuery()` - SQL query builders
- `createTestCacheKey()` / `createTestCacheValue()` - Cache testing utilities
- `createDebugEntry()` - Debug information test data

#### Testing Utilities
- `expectException()` - Enhanced exception testing
- `measureExecutionTime()` / `expectExecutionTimeUnder()` - Performance assertions
- `cleanupSingletons()` / `resetGlobalState()` - Test isolation helpers
- `mockGlobalFunction()` - Mock PHP global functions for testing

#### Custom Pest Expectations
- `toBeValidDatabaseConfig()` - Validates database configuration structure
- `toHaveDebugInfo()` - Validates debug entry structure
- `toBeOne()` - Simple value assertion

## 📁 Test Organization

### Directory Structure

```
tests/
├── README.md                    # This documentation
├── Pest.php                     # Pest configuration and global helpers
├── TestCase.php                 # Base test case for all tests
├── Unit/                        # Unit tests for individual classes
│   ├── Cache/                   # Cache component tests
│   │   ├── CacheManagerTest.php      # Cache manager functionality tests
│   │   └── DatastoreManagerTest.php  # Datastore management tests
│   └── Database/                # Database component tests
│       ├── DatabaseTest.php          # Main database class tests
│       └── DatabaseDebuggerTest.php  # Database debugging functionality tests
└── Feature/                     # Integration and feature tests
    └── ExampleTest.php              # Basic example test
```

### Naming Conventions

- **Unit Tests**: `{ClassName}Test.php`
- **Feature Tests**: `{FeatureName}Test.php` or `{FeatureName}IntegrationTest.php`
- **Test Methods**: Descriptive `it('does something')` or `test('it does something')`

## 🎨 Testing Patterns

### 1. Singleton Testing Pattern

For testing singleton classes like Database, Cache, etc.:

```php
beforeEach(function () {
    // Reset singleton instances between tests
    Database::destroyInstances();
    UnifiedCacheSystem::destroyInstance();
});

it('creates singleton instance', function () {
    $instance1 = Database::getInstance($config);
    $instance2 = Database::getInstance();

    expect($instance1)->toBe($instance2);
});
```

### 2. Exception Testing Pattern

Testing error conditions and exception handling:

```php
it('throws exception for invalid configuration', function () {
    expect(fn() => Database::getInstance([]))
        ->toThrow(InvalidArgumentException::class, 'Database configuration is required');
});

it('handles database connection errors gracefully', function () {
    $config = ['dbhost' => 'invalid', 'dbport' => 9999, /* ... */];

    expect(fn() => Database::getInstance($config)->connect())
        ->toThrow(PDOException::class);
});
```

### 3. Mock-Based Testing Pattern

Using mocks for external dependencies:

```php
it('logs errors correctly', function () {
    $mockLogger = Mockery::mock('alias:' . logger::class);
    $mockLogger->shouldReceive('error')
        ->once()
        ->with(Mockery::type('string'));

    $database = Database::getInstance($config);
    $database->logError(new Exception('Test error'));
});
```

### 4. Data-Driven Testing Pattern

Using datasets for comprehensive testing:

```php
it('validates configuration keys', function ($key, $isValid) {
    $config = [$key => 'test_value'];

    if ($isValid) {
        expect(fn() => Database::getInstance($config))->not->toThrow();
    } else {
        expect(fn() => Database::getInstance($config))->toThrow();
    }
})->with([
    ['dbhost', true],
    ['dbport', true],
    ['dbname', true],
    ['invalid_key', false],
]);
```

## 🗄️ Database Testing

### Singleton Pattern Testing

```php
// Test singleton pattern implementation
it('creates singleton instance with valid configuration', function () {
    $config = getTestDatabaseConfig();

    $instance1 = Database::getInstance($config);
    $instance2 = Database::getInstance();

    expect($instance1)->toBe($instance2);
    expect($instance1)->toBeInstanceOf(Database::class);
});

// Test multiple server instances
it('creates different instances for different servers', function () {
    $config = getTestDatabaseConfig();

    $dbInstance = Database::getServerInstance($config, 'db');
    $trackerInstance = Database::getServerInstance($config, 'tracker');

    expect($dbInstance)->not->toBe($trackerInstance);
});
```

### Configuration Testing

```php
// Test configuration validation
it('validates required configuration keys', function () {
    $config = getTestDatabaseConfig();
    expect($config)->toBeValidDatabaseConfig();
});

// Test error handling for invalid configuration
it('handles missing configuration gracefully', function () {
    $invalidConfig = ['dbhost' => 'localhost']; // Missing required keys

    expect(function () use ($invalidConfig) {
        Database::getInstance(array_values($invalidConfig));
    })->toThrow(ValueError::class);
});
```

### Query Execution Testing

```php
// Test SQL query execution with mocks
it('executes SQL queries successfully', function () {
    $query = 'SELECT * FROM users';
    $mockResult = Mockery::mock(ResultSet::class);

    $this->db->shouldReceive('sql_query')->with($query)->andReturn($mockResult);
    $result = $this->db->sql_query($query);

    expect($result)->toBeInstanceOf(ResultSet::class);
});

// Test query counter
it('increments query counter correctly', function () {
    $initialCount = $this->db->num_queries;
    $this->db->shouldReceive('getQueryCount')->andReturn($initialCount + 1);

    $this->db->sql_query('SELECT 1');
    expect($this->db->getQueryCount())->toBe($initialCount + 1);
});
```

### Debug Testing

```php
// Test debug functionality
it('captures debug information when enabled', function () {
    $mockDebugger = Mockery::mock(DatabaseDebugger::class);
    $mockDebugger->shouldReceive('debug_find_source')->andReturn('test.php:123');

    expect($mockDebugger->debug_find_source())->toContain('test.php');
});
```

## 💾 Cache Testing

### CacheManager Singleton Pattern

```php
// Test singleton pattern for cache managers
it('creates singleton instance correctly', function () {
    $storage = new MemoryStorage();
    $config = createTestCacheConfig();

    $manager1 = CacheManager::getInstance('test', $storage, $config);
    $manager2 = CacheManager::getInstance('test', $storage, $config);

    expect($manager1)->toBe($manager2);
});

// Test namespace isolation
it('creates different instances for different namespaces', function () {
    $storage = new MemoryStorage();
    $config = createTestCacheConfig();

    $manager1 = CacheManager::getInstance('namespace1', $storage, $config);
    $manager2 = CacheManager::getInstance('namespace2', $storage, $config);

    expect($manager1)->not->toBe($manager2);
});
```

### Basic Cache Operations

```php
// Test storing and retrieving values
it('stores and retrieves values correctly', function () {
    $key = 'test_key';
    $value = 'test_value';

    $result = $this->cacheManager->set($key, $value);

    expect($result)->toBeTrue();
    expect($this->cacheManager->get($key))->toBe($value);
});

// Test different data types
it('handles different data types', function () {
    $testCases = [
        ['string_key', 'string_value'],
        ['int_key', 42],
        ['array_key', ['nested' => ['data' => 'value']]],
        ['object_key', (object)['property' => 'value']]
    ];

    foreach ($testCases as [$key, $value]) {
        $this->cacheManager->set($key, $value);
        expect($this->cacheManager->get($key))->toBe($value);
    }
});
```

### Advanced Nette Cache Features

```php
// Test loading with callback functions
it('loads with callback function', function () {
    $key = 'callback_test';
    $callbackExecuted = false;

    $result = $this->cacheManager->load($key, function () use (&$callbackExecuted) {
        $callbackExecuted = true;
        return 'callback_result';
    });

    expect($result)->toBe('callback_result');
    expect($callbackExecuted)->toBeTrue();
});

// Test bulk operations
it('performs bulk loading', function () {
    // Pre-populate test data
    $this->cacheManager->set('bulk1', 'value1');
    $this->cacheManager->set('bulk2', 'value2');

    $keys = ['bulk1', 'bulk2', 'bulk3'];
    $results = $this->cacheManager->bulkLoad($keys);

    expect($results)->toBeArray();
    expect($results)->toHaveCount(3);
});
```

## 🎭 Mocking and Fixtures

### Mock Factories

```php
// Helper functions for creating mocks
function mockDatabase(): Database
{
    return Mockery::mock(Database::class)
        ->shouldReceive('sql_query')->andReturn(mockResultSet())
        ->shouldReceive('connect')->andReturn(true)
        ->getMock();
}

function mockResultSet(): ResultSet
{
    return Mockery::mock(ResultSet::class)
        ->shouldReceive('fetch')->andReturn(['id' => 1, 'name' => 'test'])
        ->shouldReceive('getRowCount')->andReturn(1)
        ->getMock();
}
```

### Test Fixtures

```php
// Configuration fixtures
function getTestDatabaseConfig(): array
{
    return [
        'dbhost' => env('TEST_DB_HOST', 'localhost'),
        'dbport' => env('TEST_DB_PORT', 3306),
        'dbname' => env('TEST_DB_NAME', 'torrentpier_test'),
        'dbuser' => env('TEST_DB_USER', 'root'),
        'dbpasswd' => env('TEST_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'persist' => false
    ];
}
```

## 🚀 Test Execution

### Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest tests/Unit/Database/
./vendor/bin/pest tests/Unit/Cache/

# Run with coverage
./vendor/bin/pest --coverage

# Run in parallel
./vendor/bin/pest --parallel

# Run with specific filter
./vendor/bin/pest --filter="singleton"
./vendor/bin/pest --filter="cache operations"

# Run specific test files
./vendor/bin/pest tests/Unit/Database/DatabaseTest.php
./vendor/bin/pest tests/Unit/Cache/CacheManagerTest.php
```

### Performance Testing

```bash
# Run performance-sensitive tests
./vendor/bin/pest --group=performance

# Stress testing with repetition
./vendor/bin/pest --repeat=100 tests/Unit/Database/DatabaseTest.php
```

### Debugging Tests

```bash
# Run with debug output
./vendor/bin/pest --debug

# Stop on first failure
./vendor/bin/pest --stop-on-failure

# Verbose output
./vendor/bin/pest -v
```

## 📋 Best Practices

### 1. Test Isolation

```php
beforeEach(function () {
    // Reset singleton instances between tests
    Database::destroyInstances();

    // Reset global state
    resetGlobalState();

    // Mock required functions for testing
    mockTracyFunction();
    mockBbLogFunction();
    mockHideBbPathFunction();
    mockUtimeFunction();

    // Initialize test data
    $this->storage = new MemoryStorage();
    $this->config = createTestCacheConfig();
});

afterEach(function () {
    // Clean up after each test
    cleanupSingletons();
});
```

### 2. Descriptive Test Names

```php
// ✅ Good: Descriptive and specific (from actual tests)
it('creates singleton instance with valid configuration');
it('creates different instances for different servers');
it('handles different data types');
it('loads with callback function');
it('increments query counter correctly');

// ❌ Bad: Vague and unclear
it('tests database');
it('cache works');
it('error handling');
```

### 3. Arrange-Act-Assert Pattern

```php
it('stores cache value with TTL', function () {
    // Arrange
    $cache = createTestCache();
    $key = 'test_key';
    $value = 'test_value';
    $ttl = 3600;

    // Act
    $result = $cache->set($key, $value, $ttl);

    // Assert
    expect($result)->toBeTrue();
    expect($cache->get($key))->toBe($value);
});
```

### 4. Test Data Management

```php
// Use factories for test data
function createTestUser(array $overrides = []): array
{
    return array_merge([
        'id' => 1,
        'username' => 'testuser',
        'email' => 'test@example.com',
        'active' => 1
    ], $overrides);
}

// Use datasets for comprehensive testing
dataset('cache_engines', [
    'file' => ['FileStorage'],
    'memory' => ['MemoryStorage'],
    'sqlite' => ['SQLiteStorage']
]);
```

### 5. Error Testing

```php
// Test all error conditions
it('handles various database errors')->with([
    [new PDOException('Connection failed'), PDOException::class],
    [new Exception('General error'), Exception::class],
    [null, 'Database connection not established']
]);
```

## 🔄 CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: torrentpier_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4
          extensions: pdo, pdo_mysql, mbstring
          coverage: xdebug

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Run tests
        run: ./vendor/bin/pest --coverage --min=80
        env:
          TEST_DB_HOST: 127.0.0.1
          TEST_DB_DATABASE: torrentpier_test
          TEST_DB_USERNAME: root
          TEST_DB_PASSWORD: password
```

### Coverage Requirements

- **Minimum Coverage**: 80% overall
- **Critical Components**: 95% (Database, Cache, Security)
- **New Code**: 100% (all new code must be fully tested)

## 📊 Test Metrics and Reporting

### Coverage Analysis

```bash
# Generate detailed coverage report
./vendor/bin/pest --coverage-html=coverage/

# Coverage by component
./vendor/bin/pest --coverage --coverage-min=80

# Check coverage for specific files
./vendor/bin/pest --coverage --path=src/Database/
```

### Performance Metrics

```php
// Performance testing with timing assertions
it('database query executes within acceptable time', function () {
    $start = microtime(true);

    $db = createTestDatabase();
    $db->sql_query('SELECT * FROM users LIMIT 1000');

    $duration = microtime(true) - $start;
    expect($duration)->toBeLessThan(0.1); // 100ms limit
});
```

## 📈 Current Implementation Status

### ✅ Completed Components

- **Database Testing**: Comprehensive unit tests for Database and DatabaseDebugger classes
- **Cache Testing**: Full test coverage for CacheManager and DatastoreManager
- **Test Infrastructure**: Complete Pest.php helper functions and mock factories
- **Singleton Pattern Testing**: Validated across all major components

### 🚧 Current Test Coverage

- **Unit Tests**: 4 test files covering core database and cache functionality
- **Mock System**: Extensive mocking infrastructure for all dependencies
- **Helper Functions**: 25+ utility functions for test data generation and assertions
- **Custom Expectations**: Specialized Pest expectations for TorrentPier patterns

## 🔮 Future Enhancements

### Planned Testing Improvements

1. **Integration Testing**: Add Feature tests for component interactions
2. **Architecture Testing**: Validate code structure and design patterns
3. **Performance Testing**: Load testing and benchmark validation
4. **Security Testing**: Automated vulnerability scanning
5. **API Testing**: REST endpoint validation (when applicable)

### Testing Guidelines for New Components

When adding new components to TorrentPier:

1. **Create test file** in appropriate Unit directory (`tests/Unit/ComponentName/`)
2. **Write unit tests** for all public methods and singleton patterns
3. **Use existing helpers** from Pest.php (mock factories, test data generators)
4. **Follow naming patterns** used in existing tests
5. **Add integration tests** to Feature directory for complex workflows
6. **Update this documentation** with component-specific patterns

---

**Remember**: Tests are not just validation tools—they're living documentation of your system's behavior. Write tests that clearly express the intended functionality and help future developers understand the codebase.

For questions or suggestions about the testing infrastructure, please refer to the [TorrentPier GitHub repository](https://github.com/torrentpier/torrentpier) or contribute to the discussion in our community forums.
