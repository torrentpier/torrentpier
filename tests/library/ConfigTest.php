<?php

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $config;
    private $testConfigFile;
    private $testConfigData;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->testConfigFile = sys_get_temp_dir() . '/test_config.ini';
        $this->testConfigData = [
            'database' => [
                'host' => 'localhost',
                'port' => 3306,
                'username' => 'test_user',
                'password' => 'test_pass',
                'database' => 'test_db'
            ],
            'cache' => [
                'enabled' => true,
                'ttl' => 3600,
                'driver' => 'redis'
            ],
            'app' => [
                'name' => 'Test App',
                'version' => '1.0.0',
                'debug' => false
            ]
        ];
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testConfigFile)) {
            unlink($this->testConfigFile);
        }
    }

    // Happy path tests
    public function testConfigCanBeInstantiated()
    {
        $this->assertInstanceOf(Config::class, $this->config);
    }

    public function testSetAndGetConfigValue()
    {
        $this->config->set('test.key', 'test_value');
        $this->assertEquals('test_value', $this->config->get('test.key'));
    }

    public function testSetAndGetNestedConfigValue()
    {
        $this->config->set('database.host', 'localhost');
        $this->config->set('database.port', 3306);
        
        $this->assertEquals('localhost', $this->config->get('database.host'));
        $this->assertEquals(3306, $this->config->get('database.port'));
    }

    public function testSetMultipleValuesAtOnce()
    {
        $this->config->setMultiple($this->testConfigData);
        
        $this->assertEquals('localhost', $this->config->get('database.host'));
        $this->assertEquals(3306, $this->config->get('database.port'));
        $this->assertEquals('test_user', $this->config->get('database.username'));
        $this->assertTrue($this->config->get('cache.enabled'));
        $this->assertEquals('Test App', $this->config->get('app.name'));
    }

    public function testHasConfigValue()
    {
        $this->config->set('test.key', 'value');
        
        $this->assertTrue($this->config->has('test.key'));
        $this->assertFalse($this->config->has('nonexistent.key'));
    }

    public function testRemoveConfigValue()
    {
        $this->config->set('test.key', 'value');
        $this->assertTrue($this->config->has('test.key'));
        
        $this->config->remove('test.key');
        $this->assertFalse($this->config->has('test.key'));
    }

    public function testGetAllConfig()
    {
        $this->config->setMultiple($this->testConfigData);
        $allConfig = $this->config->getAll();
        
        $this->assertIsArray($allConfig);
        $this->assertArrayHasKey('database', $allConfig);
        $this->assertArrayHasKey('cache', $allConfig);
        $this->assertArrayHasKey('app', $allConfig);
    }

    public function testGetConfigSection()
    {
        $this->config->setMultiple($this->testConfigData);
        $databaseConfig = $this->config->getSection('database');
        
        $this->assertIsArray($databaseConfig);
        $this->assertEquals('localhost', $databaseConfig['host']);
        $this->assertEquals(3306, $databaseConfig['port']);
    }

    // Edge cases and error conditions
    public function testGetNonExistentConfigReturnsNull()
    {
        $this->assertNull($this->config->get('nonexistent.key'));
    }

    public function testGetNonExistentConfigReturnsDefaultValue()
    {
        $defaultValue = 'default_value';
        $this->assertEquals($defaultValue, $this->config->get('nonexistent.key', $defaultValue));
    }

    public function testGetWithEmptyKey()
    {
        $this->assertNull($this->config->get(''));
        $this->assertNull($this->config->get(null));
    }

    public function testSetWithEmptyKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->config->set('', 'value');
    }

    public function testSetWithNullKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->config->set(null, 'value');
    }

    public function testOverwriteExistingConfigValue()
    {
        $this->config->set('test.key', 'original_value');
        $this->assertEquals('original_value', $this->config->get('test.key'));
        
        $this->config->set('test.key', 'new_value');
        $this->assertEquals('new_value', $this->config->get('test.key'));
    }

    public function testSetComplexDataTypes()
    {
        $arrayValue = ['item1', 'item2', 'item3'];
        $objectValue = new stdClass();
        $objectValue->property = 'value';
        
        $this->config->set('test.array', $arrayValue);
        $this->config->set('test.object', $objectValue);
        
        $this->assertEquals($arrayValue, $this->config->get('test.array'));
        $this->assertEquals($objectValue, $this->config->get('test.object'));
    }

    public function testRemoveNonExistentKey()
    {
        // Should not throw an exception
        $this->config->remove('nonexistent.key');
        $this->assertFalse($this->config->has('nonexistent.key'));
    }

    public function testGetSectionNonExistent()
    {
        $result = $this->config->getSection('nonexistent');
        $this->assertNull($result);
    }

    public function testClearAllConfig()
    {
        $this->config->setMultiple($this->testConfigData);
        $this->assertTrue($this->config->has('database.host'));
        
        $this->config->clear();
        $this->assertFalse($this->config->has('database.host'));
        $this->assertEmpty($this->config->getAll());
    }

    // File operations tests
    public function testLoadConfigFromFile()
    {
        $configContent = "[database]\nhost = localhost\nport = 3306\n\n[cache]\nenabled = true\nttl = 3600";
        file_put_contents($this->testConfigFile, $configContent);
        
        $this->config->loadFromFile($this->testConfigFile);
        
        $this->assertEquals('localhost', $this->config->get('database.host'));
        $this->assertEquals('3306', $this->config->get('database.port'));
        $this->assertEquals('true', $this->config->get('cache.enabled'));
        $this->assertEquals('3600', $this->config->get('cache.ttl'));
    }

    public function testLoadConfigFromNonExistentFile()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->config->loadFromFile('/nonexistent/path/config.ini');
    }

    public function testSaveConfigToFile()
    {
        $this->config->setMultiple($this->testConfigData);
        $this->config->saveToFile($this->testConfigFile);
        
        $this->assertFileExists($this->testConfigFile);
        $content = file_get_contents($this->testConfigFile);
        $this->assertStringContains('localhost', $content);
        $this->assertStringContains('3306', $content);
    }

    public function testSaveConfigToInvalidPath()
    {
        $this->expectException(RuntimeException::class);
        $this->config->saveToFile('/invalid/path/config.ini');
    }

    // Type casting and validation tests
    public function testGetBooleanValue()
    {
        $this->config->set('test.bool_true', true);
        $this->config->set('test.bool_false', false);
        $this->config->set('test.bool_string_true', 'true');
        $this->config->set('test.bool_string_false', 'false');
        
        $this->assertTrue($this->config->getBool('test.bool_true'));
        $this->assertFalse($this->config->getBool('test.bool_false'));
        $this->assertTrue($this->config->getBool('test.bool_string_true'));
        $this->assertFalse($this->config->getBool('test.bool_string_false'));
    }

    public function testGetIntegerValue()
    {
        $this->config->set('test.int', 42);
        $this->config->set('test.int_string', '123');
        
        $this->assertEquals(42, $this->config->getInt('test.int'));
        $this->assertEquals(123, $this->config->getInt('test.int_string'));
    }

    public function testGetFloatValue()
    {
        $this->config->set('test.float', 3.14);
        $this->config->set('test.float_string', '2.718');
        
        $this->assertEquals(3.14, $this->config->getFloat('test.float'));
        $this->assertEquals(2.718, $this->config->getFloat('test.float_string'));
    }

    public function testGetArrayValue()
    {
        $arrayValue = ['a', 'b', 'c'];
        $this->config->set('test.array', $arrayValue);
        
        $this->assertEquals($arrayValue, $this->config->getArray('test.array'));
    }

    // Environment variable integration tests
    public function testGetFromEnvironmentVariable()
    {
        $_ENV['TEST_CONFIG_VALUE'] = 'env_value';
        $this->config->set('test.env', '${TEST_CONFIG_VALUE}');
        
        $result = $this->config->get('test.env', null, true); // true for env expansion
        $this->assertEquals('env_value', $result);
        
        unset($_ENV['TEST_CONFIG_VALUE']);
    }

    public function testGetFromEnvironmentVariableWithDefault()
    {
        $this->config->set('test.env', '${NONEXISTENT_VAR:default_value}');
        
        $result = $this->config->get('test.env', null, true);
        $this->assertEquals('default_value', $result);
    }

    // Merge and extend functionality tests
    public function testMergeConfigs()
    {
        $this->config->setMultiple([
            'database' => ['host' => 'localhost', 'port' => 3306],
            'cache' => ['enabled' => true]
        ]);
        
        $additionalConfig = [
            'database' => ['port' => 5432, 'ssl' => true],
            'logging' => ['level' => 'debug']
        ];
        
        $this->config->merge($additionalConfig);
        
        $this->assertEquals('localhost', $this->config->get('database.host'));
        $this->assertEquals(5432, $this->config->get('database.port')); // Should be overwritten
        $this->assertTrue($this->config->get('database.ssl')); // Should be added
        $this->assertTrue($this->config->get('cache.enabled')); // Should remain
        $this->assertEquals('debug', $this->config->get('logging.level')); // Should be added
    }

    // Performance and memory tests
    public function testHandleLargeConfigData()
    {
        $largeData = [];
        for ($i = 0; $i < 1000; $i++) {
            $largeData["key_$i"] = "value_$i";
        }
        
        $this->config->setMultiple(['large_section' => $largeData]);
        
        $this->assertEquals('value_500', $this->config->get('large_section.key_500'));
        $this->assertEquals(1000, count($this->config->getSection('large_section')));
    }

    public function testConfigImmutability()
    {
        $originalData = ['immutable' => ['key' => 'value']];
        $this->config->setMultiple($originalData);
        
        $retrievedData = $this->config->getSection('immutable');
        $retrievedData['key'] = 'modified_value';
        
        // Original config should remain unchanged
        $this->assertEquals('value', $this->config->get('immutable.key'));
    }

    // Validation and sanitization tests
    public function testConfigKeyValidation()
    {
        $invalidKeys = ['', null, 123, [], new stdClass()];
        
        foreach ($invalidKeys as $key) {
            try {
                $this->config->set($key, 'value');
                $this->fail('Expected InvalidArgumentException for invalid key: ' . var_export($key, true));
            } catch (InvalidArgumentException $e) {
                $this->assertTrue(true); // Expected exception
            }
        }
    }

    public function testNestedKeyDepthLimit()
    {
        // Test very deep nesting
        $deepKey = implode('.', array_fill(0, 20, 'level'));
        $this->config->set($deepKey, 'deep_value');
        
        $this->assertEquals('deep_value', $this->config->get($deepKey));
    }

    // Thread safety and concurrent access tests
    public function testConcurrentAccess()
    {
        $this->config->set('concurrent.test', 'initial_value');
        
        // Simulate concurrent read/write operations
        $processes = [];
        for ($i = 0; $i < 5; $i++) {
            $this->config->set("concurrent.key_$i", "value_$i");
            $this->assertEquals("value_$i", $this->config->get("concurrent.key_$i"));
        }
        
        $this->assertEquals('initial_value', $this->config->get('concurrent.test'));
    }

    // Serialization tests
    public function testConfigSerialization()
    {
        $this->config->setMultiple($this->testConfigData);
        
        $serialized = serialize($this->config);
        $unserialized = unserialize($serialized);
        
        $this->assertEquals($this->config->get('database.host'), $unserialized->get('database.host'));
        $this->assertEquals($this->config->getAll(), $unserialized->getAll());
    }

    // Configuration validation tests
    public function testRequiredConfigValidation()
    {
        $requiredKeys = ['database.host', 'database.port', 'app.name'];
        
        $this->config->setMultiple($this->testConfigData);
        
        foreach ($requiredKeys as $key) {
            $this->assertTrue($this->config->has($key), "Required key '$key' is missing");
        }
    }

    public function testConfigValueConstraints()
    {
        $this->config->set('database.port', 3306);
        $port = $this->config->getInt('database.port');
        
        $this->assertGreaterThan(0, $port);
        $this->assertLessThanOrEqual(65535, $port);
    }
}