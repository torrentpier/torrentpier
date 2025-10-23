<?php

declare(strict_types=1);

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use TorrentPier\Config;

/**
 * Tests for Config mod namespace isolation features
 */
#[CoversClass(Config::class)]
class ConfigModNamespaceTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Create fresh config instance
        $this->config = Config::getInstance([
            'mods' => [
                'existing-mod' => [
                    'enabled' => true,
                    'settings' => [
                        'max_items' => 10
                    ]
                ]
            ]
        ]);
    }

    protected function tearDown(): void
    {
        // Reset singleton for next test
        $reflection = new \ReflectionClass(Config::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        parent::tearDown();
    }

    public function test_getModConfig_returns_value(): void
    {
        $value = $this->config->getModConfig('existing-mod', 'enabled');

        expect($value)->toBeTrue();
    }

    public function test_getModConfig_returns_default_when_not_found(): void
    {
        $value = $this->config->getModConfig('nonexistent-mod', 'key', 'default');

        expect($value)->toBe('default');
    }

    public function test_getModConfig_supports_nested_keys(): void
    {
        $value = $this->config->getModConfig('existing-mod', 'settings.max_items');

        expect($value)->toBe(10);
    }

    public function test_setModConfig_creates_new_mod_config(): void
    {
        $this->config->setModConfig('new-mod', 'api_key', 'secret123');

        $value = $this->config->get('mods.new-mod.api_key');

        expect($value)->toBe('secret123');
    }

    public function test_setModConfig_updates_existing_value(): void
    {
        $this->config->setModConfig('existing-mod', 'enabled', false);

        $value = $this->config->getModConfig('existing-mod', 'enabled');

        expect($value)->toBeFalse();
    }

    public function test_setModConfig_supports_nested_keys(): void
    {
        $this->config->setModConfig('new-mod', 'nested.level1.level2', 'deep value');

        $value = $this->config->getModConfig('new-mod', 'nested.level1.level2');

        expect($value)->toBe('deep value');
    }

    public function test_setModConfig_maintains_isolation_between_mods(): void
    {
        $this->config->setModConfig('mod-a', 'setting', 'value-a');
        $this->config->setModConfig('mod-b', 'setting', 'value-b');

        $valueA = $this->config->getModConfig('mod-a', 'setting');
        $valueB = $this->config->getModConfig('mod-b', 'setting');

        expect($valueA)->toBe('value-a');
        expect($valueB)->toBe('value-b');
    }

    public function test_getAllModConfig_returns_all_config_for_mod(): void
    {
        $config = $this->config->getAllModConfig('existing-mod');

        expect($config)->toBe([
            'enabled' => true,
            'settings' => [
                'max_items' => 10
            ]
        ]);
    }

    public function test_getAllModConfig_returns_empty_array_for_nonexistent_mod(): void
    {
        $config = $this->config->getAllModConfig('nonexistent-mod');

        expect($config)->toBe([]);
    }

    public function test_hasModConfig_returns_true_when_key_exists(): void
    {
        expect($this->config->hasModConfig('existing-mod', 'enabled'))->toBeTrue();
    }

    public function test_hasModConfig_returns_false_when_key_missing(): void
    {
        expect($this->config->hasModConfig('existing-mod', 'nonexistent'))->toBeFalse();
    }

    public function test_hasModConfig_returns_false_when_mod_missing(): void
    {
        expect($this->config->hasModConfig('nonexistent-mod', 'key'))->toBeFalse();
    }

    public function test_hasModConfig_supports_nested_keys(): void
    {
        expect($this->config->hasModConfig('existing-mod', 'settings.max_items'))->toBeTrue();
    }

    public function test_deleteModConfig_removes_key(): void
    {
        $this->config->setModConfig('test-mod', 'key1', 'value1');
        $this->config->setModConfig('test-mod', 'key2', 'value2');

        $this->config->deleteModConfig('test-mod', 'key1');

        expect($this->config->hasModConfig('test-mod', 'key1'))->toBeFalse();
        expect($this->config->hasModConfig('test-mod', 'key2'))->toBeTrue();
    }

    public function test_deleteModConfig_handles_nested_keys(): void
    {
        $this->config->setModConfig('test-mod', 'level1.level2.key', 'value');

        $this->config->deleteModConfig('test-mod', 'level1.level2.key');

        expect($this->config->hasModConfig('test-mod', 'level1.level2.key'))->toBeFalse();
    }

    public function test_deleteModConfig_handles_nonexistent_key(): void
    {
        // Should not throw
        $this->config->deleteModConfig('test-mod', 'nonexistent');

        expect(true)->toBeTrue();
    }

    public function test_deleteModConfig_handles_nonexistent_mod(): void
    {
        // Should not throw
        $this->config->deleteModConfig('nonexistent-mod', 'key');

        expect(true)->toBeTrue();
    }

    public function test_clearModConfig_removes_all_mod_config(): void
    {
        $this->config->setModConfig('test-mod', 'key1', 'value1');
        $this->config->setModConfig('test-mod', 'key2', 'value2');
        $this->config->setModConfig('test-mod', 'key3', 'value3');

        $this->config->clearModConfig('test-mod');

        $config = $this->config->getAllModConfig('test-mod');

        expect($config)->toBe([]);
    }

    public function test_clearModConfig_does_not_affect_other_mods(): void
    {
        $this->config->setModConfig('mod-a', 'key', 'value-a');
        $this->config->setModConfig('mod-b', 'key', 'value-b');

        $this->config->clearModConfig('mod-a');

        expect($this->config->getAllModConfig('mod-a'))->toBe([]);
        expect($this->config->getModConfig('mod-b', 'key'))->toBe('value-b');
    }

    public function test_clearModConfig_handles_nonexistent_mod(): void
    {
        // Should not throw
        $this->config->clearModConfig('nonexistent-mod');

        expect(true)->toBeTrue();
    }

    public function test_mod_config_does_not_affect_global_config(): void
    {
        $this->config->set('global_key', 'global_value');
        $this->config->setModConfig('test-mod', 'key', 'mod_value');

        expect($this->config->get('global_key'))->toBe('global_value');
        expect($this->config->getModConfig('test-mod', 'key'))->toBe('mod_value');
        expect($this->config->get('key'))->toBeNull();
    }

    public function test_complex_mod_config_scenario(): void
    {
        // Mod A: User reputation system
        $this->config->setModConfig('reputation', 'initial_points', 100);
        $this->config->setModConfig('reputation', 'max_points', 10000);
        $this->config->setModConfig('reputation', 'actions.post_create', 5);
        $this->config->setModConfig('reputation', 'actions.post_delete', -10);

        // Mod B: Karma system (different from reputation)
        $this->config->setModConfig('karma', 'initial_points', 50);
        $this->config->setModConfig('karma', 'actions.upvote', 1);
        $this->config->setModConfig('karma', 'actions.downvote', -1);

        // Verify isolation
        expect($this->config->getModConfig('reputation', 'initial_points'))->toBe(100);
        expect($this->config->getModConfig('karma', 'initial_points'))->toBe(50);

        // Verify nested access
        expect($this->config->getModConfig('reputation', 'actions.post_create'))->toBe(5);
        expect($this->config->getModConfig('karma', 'actions.upvote'))->toBe(1);

        // Get all config
        $reputationConfig = $this->config->getAllModConfig('reputation');
        expect($reputationConfig)->toHaveKey('initial_points');
        expect($reputationConfig)->toHaveKey('max_points');
        expect($reputationConfig)->toHaveKey('actions');

        // Clear one mod
        $this->config->clearModConfig('reputation');
        expect($this->config->getAllModConfig('reputation'))->toBe([]);
        expect($this->config->getModConfig('karma', 'initial_points'))->toBe(50);
    }
}
