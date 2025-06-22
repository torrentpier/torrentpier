<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Tests\TestCase;

class ConfigSystemTest extends TestCase
{
    /**
     * Test that config helper function works
     */
    public function testConfigHelper(): void
    {
        // Test getting the config repository
        $config = config();
        $this->assertInstanceOf(\Illuminate\Config\Repository::class, $config);
    }
    
    /**
     * Test getting config values
     */
    public function testGettingConfigValues(): void
    {
        // Assuming app.php config exists
        $appConfig = config('app');
        $this->assertIsArray($appConfig);
        
        // Test with default value
        $nonExistent = config('non.existent.key', 'default');
        $this->assertEquals('default', $nonExistent);
    }
    
    /**
     * Test setting config values
     */
    public function testSettingConfigValues(): void
    {
        // Set a single value
        config(['test.key' => 'test value']);
        $this->assertEquals('test value', config('test.key'));
        
        // Set multiple values
        config([
            'test.foo' => 'bar',
            'test.baz' => 'qux'
        ]);
        
        $this->assertEquals('bar', config('test.foo'));
        $this->assertEquals('qux', config('test.baz'));
    }
    
    /**
     * Test dot notation access
     */
    public function testDotNotationAccess(): void
    {
        config(['deeply.nested.config.value' => 'found it']);
        
        $this->assertEquals('found it', config('deeply.nested.config.value'));
        $this->assertIsArray(config('deeply.nested'));
        $this->assertArrayHasKey('config', config('deeply.nested'));
    }
}