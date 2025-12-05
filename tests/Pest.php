<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeValidDatabaseConfig', function () {
    $requiredKeys = ['dbhost', 'dbport', 'dbname', 'dbuser', 'dbpasswd', 'charset', 'persist'];

    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $this->value)) {
            return $this->toBeNull("Missing required config key: $key");
        }
    }

    return $this->toBeArray();
});

expect()->extend('toHaveDebugInfo', function () {
    return $this->toHaveKeys(['sql', 'src', 'file', 'line', 'time']);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

use Nette\Caching\Storages\MemoryStorage;
use Nette\Database\Connection;
use Nette\Database\ResultSet;
use TorrentPier\Cache\CacheManager;
use TorrentPier\Cache\DatastoreManager;
use TorrentPier\Cache\UnifiedCacheSystem;
use TorrentPier\Database\Database;
use TorrentPier\Database\DatabaseDebugger;

/**
 * Test Environment Setup
 */
function setupTestEnvironment(): void
{
    // Define test constants if not already defined
    if (!defined('BB_ROOT')) {
        define('BB_ROOT', __DIR__ . '/../');
    }

    if (!defined('INC_DIR')) {
        define('INC_DIR', BB_ROOT . 'library/includes');
    }

    if (!defined('SQL_PREPEND_SRC')) {
        define('SQL_PREPEND_SRC', true);
    }

    if (!defined('SQL_CALC_QUERY_TIME')) {
        define('SQL_CALC_QUERY_TIME', true);
    }

    if (!defined('SQL_LOG_SLOW_QUERIES')) {
        define('SQL_LOG_SLOW_QUERIES', false);
    }

    if (!defined('LOG_SEPR')) {
        define('LOG_SEPR', ' | ');
    }

    if (!defined('LOG_LF')) {
        define('LOG_LF', "\n");
    }
}

/**
 * Database Test Configuration
 */
function getTestDatabaseConfig(): array
{
    return [
        'dbhost' => 'localhost',
        'dbport' => 3306,
        'dbname' => 'test_torrentpier',
        'dbuser' => 'test_user',
        'dbpasswd' => 'test_password',
        'charset' => 'utf8mb4',
        'persist' => false
    ];
}

function getInvalidDatabaseConfig(): array
{
    return [
        'dbhost' => 'nonexistent.host',
        'dbport' => 9999,
        'dbname' => 'invalid_db',
        'dbuser' => 'invalid_user',
        'dbpasswd' => 'invalid_password',
        'charset' => 'utf8mb4',
        'persist' => false
    ];
}

/**
 * Mock Database Components
 */
function mockDatabase(): Database
{
    $mock = Mockery::mock(Database::class);
    $mock->shouldReceive('init')->andReturn(true);
    $mock->shouldReceive('connect')->andReturn(true);
    $mock->shouldReceive('sql_query')->andReturn(mockResultSet());
    $mock->shouldReceive('num_rows')->andReturn(1);
    $mock->shouldReceive('affected_rows')->andReturn(1);
    $mock->shouldReceive('sql_nextid')->andReturn(123);
    $mock->shouldReceive('close')->andReturn(true);

    return $mock;
}

function mockResultSet(): ResultSet
{
    $mock = Mockery::mock(ResultSet::class);

    // For testing purposes, just return null to indicate empty result set
    // This avoids complex Row object mocking and type issues
    $mock->shouldReceive('fetch')->andReturn(null);
    $mock->shouldReceive('getRowCount')->andReturn(0);

    return $mock;
}

function mockConnection(): Connection
{
    $mock = Mockery::mock(Connection::class);
    $mock->shouldReceive('query')->andReturn(mockResultSet());
    $mock->shouldReceive('getInsertId')->andReturn(123);
    $mock->shouldReceive('getPdo')->andReturn(mockPdo());

    return $mock;
}

function mockPdo(): PDO
{
    $mock = Mockery::mock(PDO::class);
    $mock->shouldReceive('prepare')->andReturn(mockPdoStatement());
    $mock->shouldReceive('errorInfo')->andReturn(['00000', null, null]);

    return $mock;
}

function mockPdoStatement(): PDOStatement
{
    $mock = Mockery::mock(PDOStatement::class);
    $mock->shouldReceive('execute')->andReturn(true);
    $mock->shouldReceive('fetch')->andReturn(['id' => 1, 'name' => 'test']);
    $mock->shouldReceive('fetchAll')->andReturn([['id' => 1, 'name' => 'test']]);

    return $mock;
}

function mockDatabaseDebugger(): DatabaseDebugger
{
    $mockDb = mockDatabase();
    $mock = Mockery::mock(DatabaseDebugger::class, [$mockDb]);
    $mock->shouldReceive('debug')->andReturn(true);
    $mock->shouldReceive('debug_find_source')->andReturn('test.php(123)');
    $mock->shouldReceive('log_query')->andReturn(true);
    $mock->shouldReceive('log_error')->andReturn(true);

    return $mock;
}

/**
 * Mock Cache Components
 */
function mockCacheManager(): CacheManager
{
    $mock = Mockery::mock(CacheManager::class);
    $mock->shouldReceive('get')->andReturn('test_value');
    $mock->shouldReceive('set')->andReturn(true);
    $mock->shouldReceive('rm')->andReturn(true);
    $mock->shouldReceive('load')->andReturn('test_value');
    $mock->shouldReceive('save')->andReturn(true);
    $mock->shouldReceive('clean')->andReturn(true);

    return $mock;
}

function mockDatastoreManager(): DatastoreManager
{
    $mock = Mockery::mock(DatastoreManager::class);
    $mock->shouldReceive('get')->andReturn(['test' => 'data']);
    $mock->shouldReceive('store')->andReturn(true);
    $mock->shouldReceive('update')->andReturn(true);
    $mock->shouldReceive('rm')->andReturn(true);
    $mock->shouldReceive('clean')->andReturn(true);

    return $mock;
}

function mockMemoryStorage(): MemoryStorage
{
    return new MemoryStorage();
}

/**
 * Test Data Factories
 */
function createTestUser(array $overrides = []): array
{
    return array_merge([
        'id' => 1,
        'username' => 'testuser',
        'email' => 'test@example.com',
        'active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ], $overrides);
}

function createTestTorrent(array $overrides = []): array
{
    return array_merge([
        'id' => 1,
        'info_hash' => 'test_hash_' . uniqid(),
        'name' => 'Test Torrent',
        'size' => 1048576,
        'seeders' => 5,
        'leechers' => 2,
        'completed' => 10
    ], $overrides);
}

function createTestCacheConfig(): array
{
    return [
        'prefix' => 'test_',
        'engine' => 'Memory',
        'enabled' => true,
        'ttl' => 3600
    ];
}

/**
 * Exception Testing Helpers
 */
function expectException(callable $callback, string $exceptionClass, ?string $message = null): void
{
    try {
        $callback();
        fail("Expected exception $exceptionClass was not thrown");
    } catch (Exception $e) {
        expect($e)->toBeInstanceOf($exceptionClass);
        if ($message) {
            expect($e->getMessage())->toContain($message);
        }
    }
}

/**
 * Performance Testing Helpers
 */
function measureExecutionTime(callable $callback): float
{
    $start = microtime(true);
    $callback();
    return microtime(true) - $start;
}

function expectExecutionTimeUnder(callable $callback, float $maxSeconds): void
{
    $time = measureExecutionTime($callback);
    expect($time)->toBeLessThan($maxSeconds, "Execution took {$time}s, expected under {$maxSeconds}s");
}

/**
 * Database Query Testing Helpers
 */
function createSelectQuery(array $options = []): array
{
    return array_merge([
        'SELECT' => '*',
        'FROM' => 'test_table',
        'WHERE' => '1=1',
        'ORDER BY' => 'id ASC',
        'LIMIT' => '10'
    ], $options);
}

function createInsertQuery(array $data = []): array
{
    $defaultData = ['name' => 'test', 'value' => 'test_value'];
    return [
        'INSERT' => 'test_table',
        'VALUES' => array_merge($defaultData, $data)
    ];
}

function createUpdateQuery(array $data = [], string $where = 'id = 1'): array
{
    $defaultData = ['updated_at' => date('Y-m-d H:i:s')];
    return [
        'UPDATE' => 'test_table',
        'SET' => array_merge($defaultData, $data),
        'WHERE' => $where
    ];
}

function createDeleteQuery(string $where = 'id = 1'): array
{
    return [
        'DELETE' => 'test_table',
        'WHERE' => $where
    ];
}

/**
 * Cache Testing Helpers
 */
function createTestCacheKey(string $suffix = ''): string
{
    return 'test_key_' . uniqid() . ($suffix ? '_' . $suffix : '');
}

function createTestCacheValue(array $data = []): array
{
    return array_merge([
        'data' => 'test_value',
        'timestamp' => time(),
        'version' => '1.0'
    ], $data);
}

/**
 * Debug Testing Helpers
 */
function createDebugEntry(array $overrides = []): array
{
    return array_merge([
        'sql' => 'SELECT * FROM test_table',
        'src' => 'test.php(123)',
        'file' => 'test.php',
        'line' => '123',
        'time' => 0.001,
        'info' => 'Test query',
        'mem_before' => 1024,
        'mem_after' => 1024
    ], $overrides);
}

function assertDebugEntryValid(array $entry): void
{
    expect($entry)->toHaveDebugInfo();
    expect($entry['sql'])->toBeString();
    expect($entry['time'])->toBeFloat();
    expect($entry['src'])->toBeString();
}

/**
 * Cleanup Helpers
 */
function cleanupSingletons(): void
{
    // Reset database instances
    if (class_exists(Database::class) && method_exists(Database::class, 'destroyInstances')) {
        Database::destroyInstances();
    }

    // Reset cache instances
    if (class_exists(UnifiedCacheSystem::class) && method_exists(UnifiedCacheSystem::class, 'destroyInstance')) {
        UnifiedCacheSystem::destroyInstance();
    }

    // Close mockery
    Mockery::close();
}

function resetGlobalState(): void
{
    // Reset any global variables that might affect tests
    $_COOKIE = [];
    $_SESSION = [];
}

/**
 * File System Helpers
 */
function createTempDirectory(): string
{
    $tempDir = sys_get_temp_dir() . '/torrentpier_test_' . uniqid();
    mkdir($tempDir, 0755, true);
    return $tempDir;
}

function removeTempDirectory(string $dir): void
{
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? removeTempDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

/**
 * Function Mocking Helpers
 */
function mockGlobalFunction(string $functionName, $returnValue): void
{
    if (!function_exists($functionName)) {
        eval("function $functionName() { return " . var_export($returnValue, true) . "; }");
    }
}

function mockDevFunction(): void
{
    if (!function_exists('dev')) {
        eval('
            function dev() {
                return new class {
                    public function checkSqlDebugAllowed() { return true; }
                    public function formatShortQuery($query, $escape = false) { return $query; }
                };
            }
        ');
    }
}

function mockBbLogFunction(): void
{
    if (!function_exists('bb_log')) {
        eval('function bb_log($message, $file = "test", $append = true) { return true; }');
    }
}

function mockHideBbPathFunction(): void
{
    if (!function_exists('hide_bb_path')) {
        eval('function hide_bb_path($path) { return basename($path); }');
    }
}

function mockUtimeFunction(): void
{
    if (!function_exists('utime')) {
        eval('function utime() { return microtime(true); }');
    }
}

// Initialize test environment when Pest loads
setupTestEnvironment();
mockDevFunction();
mockBbLogFunction();
mockHideBbPathFunction();
mockUtimeFunction();
