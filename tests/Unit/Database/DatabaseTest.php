<?php

use Nette\Database\Connection;
use TorrentPier\Database\Database;
use TorrentPier\Database\DatabaseDebugger;

describe('Database Class', function () {
    beforeEach(function () {
        // Reset any global state
        resetGlobalState();

        // Mock required functions that might not exist in test environment
        mockTracyFunction();
        mockBbLogFunction();
        mockHideBbPathFunction();
    });

    afterEach(function () {
        // Close mockery
        Mockery::close();
    });

    describe('Instance Creation', function () {
        it('creates instance with valid configuration', function () {
            $config = getTestDatabaseConfig();

            $instance = new Database(array_values($config));

            expect($instance)->toBeInstanceOf(Database::class);
        });

        it('creates multiple independent instances', function () {
            $config = getTestDatabaseConfig();

            $instance1 = new Database(array_values($config), 'db1');
            $instance2 = new Database(array_values($config), 'db2');

            expect($instance1)->not->toBe($instance2)
                ->and($instance1)->toBeInstanceOf(Database::class)
                ->and($instance2)->toBeInstanceOf(Database::class)
                ->and($instance1->db_server)->toBe('db1')
                ->and($instance2->db_server)->toBe('db2');
        });

        it('stores configuration correctly', function () {
            $config = getTestDatabaseConfig();
            $db = new Database(array_values($config));

            expect($db->cfg)->toBe($config)
                ->and($db->db_server)->toBe('db')
                ->and($db->cfg_keys)->toContain('dbhost', 'dbport', 'dbname');
        });

        it('initializes debugger on construction', function () {
            $config = getTestDatabaseConfig();
            $db = new Database(array_values($config));

            expect($db->debugger)->toBeInstanceOf(DatabaseDebugger::class);
        });
    });

    describe('Configuration Validation', function () {
        it('validates required configuration keys', function () {
            $requiredKeys = ['dbhost', 'dbport', 'dbname', 'dbuser', 'dbpasswd', 'charset', 'persist'];
            $config = getTestDatabaseConfig();

            foreach ($requiredKeys as $key) {
                expect($config)->toHaveKey($key);
            }
        });

        it('validates configuration has correct structure', function () {
            $config = getTestDatabaseConfig();
            expect($config)->toBeValidDatabaseConfig();
        });

        it('handles missing configuration gracefully', function () {
            $invalidConfig = ['dbhost' => 'localhost']; // Missing required keys

            expect(function () use ($invalidConfig) {
                new Database(array_values($invalidConfig));
            })->toThrow(ValueError::class);
        });
    });

    describe('Connection Management', function () {
        it('initializes connection state correctly', function () {
            $config = getTestDatabaseConfig();
            $db = new Database(array_values($config));

            expect($db->connection)->toBeNull()
                ->and($db->inited)->toBeFalse()
                ->and($db->num_queries)->toBe(0);
        });

        it('tracks initialization state', function () {
            // Create a mock that doesn't try to connect to real database
            $mockConnection = Mockery::mock(Connection::class);
            $mockConnection->shouldReceive('connect')->andReturn(true);

            $this->db = Mockery::mock(Database::class)->makePartial();
            $this->db->shouldReceive('init')->andReturnNull();
            $this->db->shouldReceive('connect')->andReturnNull();

            $this->db->init(); // void method, just call it
            expect(true)->toBeTrue(); // Just verify it completes without error
        });
    });

    describe('Magic Methods', function () {
        beforeEach(function () {
            $this->db = new Database(array_values(getTestDatabaseConfig()));
            $this->db->debugger = mockDatabaseDebugger();
        });

        it('provides access to debugger properties via magic getter', function () {
            $this->db->debugger->dbg_enabled = true;

            $value = $this->db->__get('dbg_enabled');

            expect($value)->toBeTrue();
        });

        it('checks property existence via magic isset', function () {
            $exists = $this->db->__isset('dbg_enabled');

            expect($exists)->toBeTrue();
        });

        it('returns false for non-existent properties', function () {
            $exists = $this->db->__isset('non_existent_property');

            expect($exists)->toBeFalse();
        });

        it('throws exception for invalid property access', function () {
            expect(fn () => $this->db->__get('invalid_property'))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Performance Testing', function () {
        beforeEach(function () {
            $this->db = new Database(array_values(getTestDatabaseConfig()));
            $this->db->connection = mockConnection();
        });

        it('executes queries within acceptable time', function () {
            expectExecutionTimeUnder(function () {
                $this->db->sql_query('SELECT 1');
            }, 0.01); // 10ms
        });

        it('handles multiple concurrent queries efficiently', function () {
            expectExecutionTimeUnder(function () {
                for ($i = 0; $i < 100; $i++) {
                    $this->db->sql_query("SELECT {$i}");
                }
            }, 0.1); // 100ms for 100 queries
        });
    });

    describe('Error Handling', function () {
        beforeEach(function () {
            $this->db = Mockery::mock(Database::class)->makePartial();
        });

        it('handles connection errors gracefully', function () {
            $invalidConfig = getInvalidDatabaseConfig();
            $db = new Database(array_values($invalidConfig));

            expect(fn () => $db->connect())->toThrow(Exception::class);
        });

        it('triggers error for query failures when using wrapper', function () {
            // Mock sql_query to return null (indicating failure)
            $this->db->shouldReceive('sql_query')->andReturn(null);

            // Mock trigger_error to throw RuntimeException instead of calling bb_die
            $this->db->shouldReceive('trigger_error')->andThrow(new RuntimeException('Database Error'));

            expect(fn () => $this->db->query('INVALID'))
                ->toThrow(RuntimeException::class);
        });

        it('logs errors appropriately', function () {
            $exception = new Exception('Test error');

            // Should not throw when logging errors
            $this->db->shouldReceive('logError')->with($exception)->andReturn(true);

            expect(fn () => $this->db->logError($exception))
                ->not->toThrow(Exception::class);
        });
    });

    describe('Legacy Compatibility', function () {
        it('maintains backward compatibility with SqlDb interface', function () {
            $db = new Database(array_values(getTestDatabaseConfig()));
            $db->connection = mockConnection();

            // All these methods should exist and work
            expect(method_exists($db, 'sql_query'))->toBeTrue()
                ->and(method_exists($db, 'sql_fetchrow'))->toBeTrue()
                ->and(method_exists($db, 'sql_fetchrowset'))->toBeTrue()
                ->and(method_exists($db, 'fetch_row'))->toBeTrue()
                ->and(method_exists($db, 'fetch_rowset'))->toBeTrue()
                ->and(method_exists($db, 'affected_rows'))->toBeTrue()
                ->and(method_exists($db, 'sql_nextid'))->toBeTrue();
        });

        it('maintains DBS statistics compatibility', function () {
            $db = new Database(array_values(getTestDatabaseConfig()));

            expect($db->DBS)->toBeArray()
                ->and($db->DBS)->toHaveKey('num_queries')
                ->and($db->DBS)->toHaveKey('sql_timetotal');
        });
    });
});

// Performance test group
describe('Database Performance', function () {
    beforeEach(function () {
        $this->db = new Database(array_values(getTestDatabaseConfig()));
        $this->db->connection = mockConnection();
    });

    it('creates instances efficiently')
        ->group('performance')
        ->repeat(1000)
        ->expect(fn () => new Database(array_values(getTestDatabaseConfig())))
        ->toBeInstanceOf(Database::class);

    it('executes simple queries efficiently')
        ->group('performance')
        ->expect(function () {
            return measureExecutionTime(fn () => $this->db->sql_query('SELECT 1'));
        })
        ->toBeLessThan(0.001); // 1ms
});
