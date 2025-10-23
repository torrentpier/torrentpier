<?php

declare(strict_types=1);

namespace Tests\Hooks;

use PHPUnit\Framework\TestCase;
use TorrentPier\Hooks\HookManager;

/**
 * Comprehensive test suite for Hook system
 *
 * @covers \TorrentPier\Hooks\HookManager
 */
class HookTest extends TestCase
{
    private HookManager $hooks;

    protected function setUp(): void
    {
        // Get singleton instance and clear all hooks before each test
        $this->hooks = HookManager::getInstance();
        $this->hooks->clear_hooks();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->hooks->clear_hooks();
    }

    // ========================================================================
    // ACTION TESTS
    // ========================================================================

    public function test_add_action_registers_callback(): void
    {
        $executed = false;

        $this->hooks->add_action('test.action', function () use (&$executed) {
            $executed = true;
        });

        $this->assertTrue($this->hooks->has_hook('test.action', 'action'));

        $this->hooks->do_action('test.action');

        $this->assertTrue($executed, 'Action callback should be executed');
    }

    public function test_action_receives_arguments(): void
    {
        $receivedArgs = [];

        $this->hooks->add_action('test.action', function ($arg1, $arg2, $arg3) use (&$receivedArgs) {
            $receivedArgs = [$arg1, $arg2, $arg3];
        }, 10, 3);

        $this->hooks->do_action('test.action', 'foo', 'bar', 'baz');

        $this->assertEquals(['foo', 'bar', 'baz'], $receivedArgs);
    }

    public function test_action_limits_arguments_based_on_accepted_args(): void
    {
        $receivedArgs = [];

        // Accept only 2 arguments
        $this->hooks->add_action('test.action', function (...$args) use (&$receivedArgs) {
            $receivedArgs = $args;
        }, 10, 2);

        // Pass 5 arguments
        $this->hooks->do_action('test.action', 'a', 'b', 'c', 'd', 'e');

        $this->assertCount(2, $receivedArgs, 'Should only receive 2 arguments');
        $this->assertEquals(['a', 'b'], $receivedArgs);
    }

    public function test_multiple_actions_execute_in_priority_order(): void
    {
        $order = [];

        $this->hooks->add_action('test.action', function () use (&$order) {
            $order[] = 'third';
        }, 30);

        $this->hooks->add_action('test.action', function () use (&$order) {
            $order[] = 'first';
        }, 10);

        $this->hooks->add_action('test.action', function () use (&$order) {
            $order[] = 'second';
        }, 20);

        $this->hooks->do_action('test.action');

        $this->assertEquals(['first', 'second', 'third'], $order);
    }

    public function test_do_action_with_no_callbacks_does_nothing(): void
    {
        // Should not throw exception
        $this->hooks->do_action('nonexistent.action', 'arg1', 'arg2');

        $this->assertFalse($this->hooks->has_hook('nonexistent.action'));
    }

    public function test_action_error_does_not_stop_other_callbacks(): void
    {
        $executed = false;

        $this->hooks->add_action('test.action', function () {
            throw new \Exception('Test error');
        }, 10);

        $this->hooks->add_action('test.action', function () use (&$executed) {
            $executed = true;
        }, 20);

        $this->hooks->do_action('test.action');

        $this->assertTrue($executed, 'Second callback should execute despite first throwing error');
    }

    // ========================================================================
    // FILTER TESTS
    // ========================================================================

    public function test_add_filter_modifies_value(): void
    {
        $this->hooks->add_filter('test.filter', function ($value) {
            return $value . ' modified';
        });

        $result = $this->hooks->apply_filter('test.filter', 'original');

        $this->assertEquals('original modified', $result);
    }

    public function test_filter_receives_additional_arguments(): void
    {
        $this->hooks->add_filter('test.filter', function ($value, $multiplier, $suffix) {
            return ($value * $multiplier) . $suffix;
        }, 10, 3);

        $result = $this->hooks->apply_filter('test.filter', 5, 3, '_units');

        $this->assertEquals('15_units', $result);
    }

    public function test_multiple_filters_chain_values(): void
    {
        $this->hooks->add_filter('test.filter', function ($value) {
            return $value . 'A';
        }, 20);

        $this->hooks->add_filter('test.filter', function ($value) {
            return $value . 'B';
        }, 10);

        $this->hooks->add_filter('test.filter', function ($value) {
            return $value . 'C';
        }, 30);

        $result = $this->hooks->apply_filter('test.filter', '');

        $this->assertEquals('BAC', $result, 'Filters should execute in priority order');
    }

    public function test_apply_filter_with_no_callbacks_returns_original(): void
    {
        $original = 'unchanged';

        $result = $this->hooks->apply_filter('nonexistent.filter', $original);

        $this->assertSame($original, $result);
    }

    public function test_filter_error_preserves_current_value(): void
    {
        $this->hooks->add_filter('test.filter', function ($value) {
            return $value . 'A';
        }, 10);

        $this->hooks->add_filter('test.filter', function ($value) {
            throw new \Exception('Test error');
        }, 20);

        $this->hooks->add_filter('test.filter', function ($value) {
            return $value . 'C';
        }, 30);

        $result = $this->hooks->apply_filter('test.filter', '');

        $this->assertEquals('AC', $result, 'Should skip broken filter and continue with value');
    }

    // ========================================================================
    // PRIORITY TESTS
    // ========================================================================

    public function test_same_priority_executes_in_registration_order(): void
    {
        $order = [];

        $this->hooks->add_action('test.action', function () use (&$order) {
            $order[] = 'first';
        }, 10);

        $this->hooks->add_action('test.action', function () use (&$order) {
            $order[] = 'second';
        }, 10);

        $this->hooks->add_action('test.action', function () use (&$order) {
            $order[] = 'third';
        }, 10);

        $this->hooks->do_action('test.action');

        $this->assertEquals(['first', 'second', 'third'], $order);
    }

    public function test_negative_priority_executes_first(): void
    {
        $order = [];

        $this->hooks->add_action('test.action', function () use (&$order) {
            $order[] = 'normal';
        }, 10);

        $this->hooks->add_action('test.action', function () use (&$order) {
            $order[] = 'early';
        }, -10);

        $this->hooks->do_action('test.action');

        $this->assertEquals(['early', 'normal'], $order);
    }

    // ========================================================================
    // REMOVE HOOK TESTS
    // ========================================================================

    public function test_remove_action_callback(): void
    {
        $executed = false;

        $callback = function () use (&$executed) {
            $executed = true;
        };

        $this->hooks->add_action('test.action', $callback);

        $this->assertTrue($this->hooks->has_hook('test.action'));

        $removed = $this->hooks->remove_hook('test.action', $callback, 10, 'action');

        $this->assertTrue($removed);

        $this->hooks->do_action('test.action');

        $this->assertFalse($executed, 'Removed callback should not execute');
    }

    public function test_remove_filter_callback(): void
    {
        $callback = function ($value) {
            return $value . ' modified';
        };

        $this->hooks->add_filter('test.filter', $callback);

        $removed = $this->hooks->remove_hook('test.filter', $callback, 10, 'filter');

        $this->assertTrue($removed);

        $result = $this->hooks->apply_filter('test.filter', 'original');

        $this->assertEquals('original', $result, 'Filter should not be applied after removal');
    }

    public function test_remove_nonexistent_callback_returns_false(): void
    {
        $callback = function () {
        };

        $removed = $this->hooks->remove_hook('test.action', $callback, 10, 'action');

        $this->assertFalse($removed);
    }

    // ========================================================================
    // HAS HOOK TESTS
    // ========================================================================

    public function test_has_hook_detects_action(): void
    {
        $this->assertFalse($this->hooks->has_hook('test.action'));

        $this->hooks->add_action('test.action', function () {
        });

        $this->assertTrue($this->hooks->has_hook('test.action'));
        $this->assertTrue($this->hooks->has_hook('test.action', 'action'));
        $this->assertFalse($this->hooks->has_hook('test.action', 'filter'));
    }

    public function test_has_hook_detects_filter(): void
    {
        $this->assertFalse($this->hooks->has_hook('test.filter'));

        $this->hooks->add_filter('test.filter', function ($v) {
            return $v;
        });

        $this->assertTrue($this->hooks->has_hook('test.filter'));
        $this->assertTrue($this->hooks->has_hook('test.filter', 'filter'));
        $this->assertFalse($this->hooks->has_hook('test.filter', 'action'));
    }

    public function test_has_hook_any_type(): void
    {
        $this->hooks->add_action('test.hook', function () {
        });

        $this->assertTrue($this->hooks->has_hook('test.hook', 'any'));

        $this->hooks->add_filter('test.hook2', function ($v) {
            return $v;
        });

        $this->assertTrue($this->hooks->has_hook('test.hook2', 'any'));
    }

    // ========================================================================
    // GET HOOKS TESTS
    // ========================================================================

    public function test_get_hooks_returns_all_actions(): void
    {
        $this->hooks->add_action('test.action', function () {
        });

        $hooks = $this->hooks->get_hooks(null, 'action');

        $this->assertArrayHasKey('test.action', $hooks);
        $this->assertIsArray($hooks['test.action']);
    }

    public function test_get_hooks_returns_specific_hook(): void
    {
        $this->hooks->add_action('test.action', function () {
        }, 10);

        $this->hooks->add_action('test.action', function () {
        }, 20);

        $hooks = $this->hooks->get_hooks('test.action', 'action');

        $this->assertArrayHasKey('actions', $hooks);
        $this->assertCount(2, $hooks['actions'], 'Should have 2 priority levels');
    }

    // ========================================================================
    // CLEAR HOOKS TESTS
    // ========================================================================

    public function test_clear_specific_action(): void
    {
        $this->hooks->add_action('test.action1', function () {
        });

        $this->hooks->add_action('test.action2', function () {
        });

        $this->hooks->clear_hooks('test.action1', 'action');

        $this->assertFalse($this->hooks->has_hook('test.action1'));
        $this->assertTrue($this->hooks->has_hook('test.action2'));
    }

    public function test_clear_all_hooks(): void
    {
        $this->hooks->add_action('test.action', function () {
        });

        $this->hooks->add_filter('test.filter', function ($v) {
            return $v;
        });

        $this->hooks->clear_hooks();

        $this->assertFalse($this->hooks->has_hook('test.action'));
        $this->assertFalse($this->hooks->has_hook('test.filter'));
    }

    // ========================================================================
    // STATISTICS TESTS
    // ========================================================================

    public function test_statistics_tracking(): void
    {
        $this->hooks->add_action('test.action', function () {
        });

        $this->hooks->add_filter('test.filter', function ($v) {
            return $v;
        });

        $stats = $this->hooks->get_stats();

        $this->assertEquals(1, $stats['actions']);
        $this->assertEquals(1, $stats['filters']);
        $this->assertEquals(0, $stats['executions']);

        $this->hooks->do_action('test.action');
        $this->hooks->apply_filter('test.filter', 'test');

        $stats = $this->hooks->get_stats();

        $this->assertEquals(2, $stats['executions']);
    }

    // ========================================================================
    // PERFORMANCE TESTS
    // ========================================================================

    public function test_hook_execution_performance(): void
    {
        // Register 100 hooks
        for ($i = 0; $i < 100; $i++) {
            $this->hooks->add_action('test.action', function () {
            }, $i);
        }

        $start = microtime(true);

        // Execute 100 times
        for ($i = 0; $i < 100; $i++) {
            $this->hooks->do_action('test.action');
        }

        $elapsed = microtime(true) - $start;

        // 10,000 hook executions should take less than 100ms
        $this->assertLessThan(0.1, $elapsed, 'Performance: 10,000 hook executions should be <100ms');
    }

    public function test_caching_improves_performance(): void
    {
        // Add many hooks with different priorities
        for ($i = 0; $i < 50; $i++) {
            $this->hooks->add_action('test.action', function () {
            }, $i);
        }

        // First execution (no cache)
        $start1 = microtime(true);
        $this->hooks->do_action('test.action');
        $elapsed1 = microtime(true) - $start1;

        // Second execution (with cache)
        $start2 = microtime(true);
        $this->hooks->do_action('test.action');
        $elapsed2 = microtime(true) - $start2;

        // Cached execution should be faster (or at least not significantly slower)
        $this->assertLessThanOrEqual($elapsed1 * 1.5, $elapsed2, 'Cached execution should not be significantly slower');
    }

    // ========================================================================
    // COMPLEX SCENARIOS
    // ========================================================================

    public function test_action_can_modify_external_state(): void
    {
        $counter = 0;

        $this->hooks->add_action('increment', function () use (&$counter) {
            $counter++;
        });

        $this->hooks->add_action('increment', function () use (&$counter) {
            $counter += 2;
        });

        $this->hooks->do_action('increment');

        $this->assertEquals(3, $counter);
    }

    public function test_filter_can_change_type(): void
    {
        $this->hooks->add_filter('test.filter', function ($value) {
            return (int) $value;
        }, 10);

        $this->hooks->add_filter('test.filter', function ($value) {
            return $value * 2;
        }, 20);

        $this->hooks->add_filter('test.filter', function ($value) {
            return (string) $value;
        }, 30);

        $result = $this->hooks->apply_filter('test.filter', '10');

        $this->assertIsString($result);
        $this->assertEquals('20', $result);
    }

    public function test_closure_with_use_statement(): void
    {
        $prefix = 'PREFIX:';

        $this->hooks->add_filter('test.filter', function ($value) use ($prefix) {
            return $prefix . $value;
        });

        $result = $this->hooks->apply_filter('test.filter', 'test');

        $this->assertEquals('PREFIX:test', $result);
    }

    public function test_callable_array_format(): void
    {
        $testObject = new class {
            public function modifyValue($value)
            {
                return $value . '_modified';
            }
        };

        $this->hooks->add_filter('test.filter', [$testObject, 'modifyValue']);

        $result = $this->hooks->apply_filter('test.filter', 'original');

        $this->assertEquals('original_modified', $result);
    }

    public function test_real_world_example_post_can_edit_filter(): void
    {
        // Simulate post edit permission checking with filters

        $post = ['author_id' => 100, 'locked' => false];
        $user = ['id' => 100, 'is_admin' => false, 'readonly' => false];

        // Core permission check
        $can_edit = $post['author_id'] === $user['id'];

        // Mod 1: Prevent editing locked posts
        $this->hooks->add_filter('post.can_edit', function ($can, $post) {
            if ($post['locked']) {
                return false;
            }
            return $can;
        }, 10, 2);

        // Mod 2: Admins can always edit
        $this->hooks->add_filter('post.can_edit', function ($can, $post, $user) {
            if ($user['is_admin']) {
                return true;
            }
            return $can;
        }, 20, 3);

        // Mod 3: Readonly users cannot edit
        $this->hooks->add_filter('post.can_edit', function ($can, $post, $user) {
            if ($user['readonly']) {
                return false;
            }
            return $can;
        }, 30, 3);

        $result = $this->hooks->apply_filter('post.can_edit', $can_edit, $post, $user);

        $this->assertTrue($result, 'User should be able to edit their own unlocked post');

        // Test with readonly user
        $user['readonly'] = true;
        $result = $this->hooks->apply_filter('post.can_edit', $can_edit, $post, $user);

        $this->assertFalse($result, 'Readonly user should not be able to edit');
    }
}
