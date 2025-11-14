<?php

declare(strict_types=1);

namespace TorrentPier\Hooks;

use Throwable;

/**
 * Hook System for TorrentPier v3.0 Mod System
 *
 * Provides WordPress-style action and filter hooks for mod extensibility.
 * Allows mods to inject functionality without modifying core files.
 *
 * Actions: Execute callbacks without returning values
 * Filters: Execute callbacks that modify and return values
 *
 * @package TorrentPier\Hooks
 * @since 3.0.0
 */
class HookManager
{
    private static ?self $instance = null;

    /**
     * Registry of action hooks
     * Format: ['hook_name' => [priority => [['callback' => callable, 'accepted_args' => int]]]]
     *
     * @var array<string, array<int, array<array{callback: callable, accepted_args: int}>>>
     */
    private array $actions = [];

    /**
     * Registry of filter hooks
     * Format: ['hook_name' => [priority => [['callback' => callable, 'accepted_args' => int]]]]
     *
     * @var array<string, array<int, array<array{callback: callable, accepted_args: int}>>>
     */
    private array $filters = [];

    /**
     * Cache for sorted hooks (performance optimization)
     *
     * @var array<string, array<int, array<array{callback: callable, accepted_args: int}>>>
     */
    private array $sortedActionsCache = [];
    private array $sortedFiltersCache = [];

    /**
     * Statistics for debugging and performance monitoring
     *
     * @var array{actions: int, filters: int, executions: int}
     */
    private array $stats = [
        'actions' => 0,
        'filters' => 0,
        'executions' => 0,
    ];

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
    }

    /**
     * Get a singleton instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register an action hook callback
     *
     * Actions allow mods to execute code at specific points without modifying the return value.
     *
     * Example:
     * ```php
     * hooks()->add_action('post.created', function($postId, $userId) {
     *     // Log post creation
     *     Log::info("Post {$postId} created by user {$userId}");
     * }, 10, 2);
     * ```
     *
     * @param string $hook Hook name (e.g., 'post.created', 'user.login')
     * @param callable $callback Function to execute when a hook fires
     * @param int $priority Priority (lower = earlier, default: 10)
     * @param int $accepted_args Number of arguments callback accepts (default: 1)
     * @return void
     */
    public function add_action(
        string   $hook,
        callable $callback,
        int      $priority = 10,
        int      $accepted_args = 1
    ): void
    {
        if (!isset($this->actions[$hook])) {
            $this->actions[$hook] = [];
        }

        if (!isset($this->actions[$hook][$priority])) {
            $this->actions[$hook][$priority] = [];
        }

        $this->actions[$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args,
        ];

        // Invalidate sorted cache
        unset($this->sortedActionsCache[$hook]);

        $this->stats['actions']++;
    }

    /**
     * Execute all callbacks registered to an action hook
     *
     * Callbacks are executed in priority order (lowest to highest).
     * Return values are ignored.
     *
     * Example:
     * ```php
     * // In core code
     * hooks()->do_action('post.created', $postId, $userId);
     * ```
     *
     * @param string $hook Hook name
     * @param mixed ...$args Arguments to pass to callbacks
     * @return void
     */
    public function do_action(string $hook, mixed ...$args): void
    {
        if (!isset($this->actions[$hook])) {
            if (function_exists('dev') && ($dev = dev()) && method_exists($dev, 'log')) {
                $dev->log('hooks', "HookManager::do_action - hook not registered: $hook");
            }
            return;
        }

        $callbacks = $this->getSortedHooks($hook, 'action');

        if (function_exists('dev') && ($dev = dev()) && method_exists($dev, 'log')) {
            $dev->log('hooks', "HookManager::do_action - calling $hook", ['callbacks_count' => count($callbacks)]);
        }

        foreach ($callbacks as $callback_data) {
            $callback = $callback_data['callback'];
            $accepted_args = $callback_data['accepted_args'];

            // Limit arguments to what callback expects
            $callback_args = array_slice($args, 0, $accepted_args);

            try {
                $callback(...$callback_args);
                $this->stats['executions']++;
            } catch (Throwable $e) {
                // Log error but don't break execution
                $this->logHookError($hook, $callback, $e);
            }
        }
    }

    /**
     * Register a filter hook callback
     *
     * Filters allow mods to modify values passing through the system.
     * Each callback receives the value and should return a modified version.
     *
     * Example:
     * ```php
     * hooks()->add_filter('post.can_edit', function($can, $post, $user) {
     *     // Prevent banned users from editing
     *     if ($user['readonly'] != 0) {
     *         return false;
     *     }
     *     return $can;
     * }, 10, 3);
     * ```
     *
     * @param string $hook Hook name (e.g., 'post.content', 'user.permissions')
     * @param callable $callback Function to filter value
     * @param int $priority Priority (lower = earlier, default: 10)
     * @param int $accepted_args Number of arguments callback accepts (default: 1)
     * @return void
     */
    public function add_filter(
        string   $hook,
        callable $callback,
        int      $priority = 10,
        int      $accepted_args = 1
    ): void
    {
        if (!isset($this->filters[$hook])) {
            $this->filters[$hook] = [];
        }

        if (!isset($this->filters[$hook][$priority])) {
            $this->filters[$hook][$priority] = [];
        }

        $this->filters[$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args,
        ];

        // Invalidate sorted cache
        unset($this->sortedFiltersCache[$hook]);

        $this->stats['filters']++;
    }

    /**
     * Apply all callbacks registered to a filter hook
     *
     * Callbacks are executed in priority order (lowest to highest).
     * Each callback receives the value from the previous callback.
     *
     * Example:
     * ```php
     * // In core code
     * $content = hooks()->apply_filter('post.content', $content, $postId);
     * ```
     *
     * @param string $hook Hook name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments for callbacks
     * @return mixed Filtered value
     */
    public function apply_filter(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (!isset($this->filters[$hook])) {
            return $value;
        }

        $callbacks = $this->getSortedHooks($hook, 'filter');

        foreach ($callbacks as $callback_data) {
            $callback = $callback_data['callback'];
            $accepted_args = $callback_data['accepted_args'];

            // The first arg is always the value, rest are additional context
            $callback_args = array_merge([$value], array_slice($args, 0, $accepted_args - 1));

            try {
                $value = $callback(...$callback_args);
                $this->stats['executions']++;
            } catch (Throwable $e) {
                // Log error but continue with the current value
                $this->logHookError($hook, $callback, $e);
            }
        }

        return $value;
    }

    /**
     * Remove a specific callback from a hook
     *
     * @param string $hook Hook name
     * @param callable $callback Callback to remove
     * @param int $priority Priority level to search (default: 10)
     * @param string $type Hook type ('action' or 'filter')
     * @return bool True if removed, false if not found
     */
    public function remove_hook(
        string   $hook,
        callable $callback,
        int      $priority = 10,
        string   $type = 'action'
    ): bool
    {
        $registry = &$this->actions;
        if ($type !== 'action') {
            $registry = &$this->filters;
        }

        if (!isset($registry[$hook][$priority])) {
            return false;
        }

        foreach ($registry[$hook][$priority] as $index => $hook_data) {
            if ($hook_data['callback'] === $callback) {
                unset($registry[$hook][$priority][$index]);

                // Reindex array
                $registry[$hook][$priority] = array_values($registry[$hook][$priority]);

                // Invalidate cache
                if ($type === 'action') {
                    unset($this->sortedActionsCache[$hook]);
                } else {
                    unset($this->sortedFiltersCache[$hook]);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Check if a hook has any registered callbacks
     *
     * @param string $hook Hook name
     * @param string $type Hook type ('action', 'filter', or 'any')
     * @return bool True if the hook has callbacks
     */
    public function has_hook(string $hook, string $type = 'any'): bool
    {
        if ($type === 'any') {
            return isset($this->actions[$hook]) || isset($this->filters[$hook]);
        }

        if ($type === 'action') {
            return isset($this->actions[$hook]) && !empty($this->actions[$hook]);
        }

        if ($type === 'filter') {
            return isset($this->filters[$hook]) && !empty($this->filters[$hook]);
        }

        return false;
    }

    /**
     * Get all registered hooks (for debugging)
     *
     * @param string|null $hook Specific hook name, or null for all hooks
     * @param string $type Hook type ('action', 'filter', or 'all')
     * @return array Hook data
     */
    public function get_hooks(?string $hook = null, string $type = 'all'): array
    {
        if ($hook !== null) {
            $result = [];

            if ($type === 'all' || $type === 'action') {
                $result['actions'] = $this->actions[$hook] ?? [];
            }

            if ($type === 'all' || $type === 'filter') {
                $result['filters'] = $this->filters[$hook] ?? [];
            }

            return $result;
        }

        if ($type === 'action') {
            return $this->actions;
        }

        if ($type === 'filter') {
            return $this->filters;
        }

        return [
            'actions' => $this->actions,
            'filters' => $this->filters,
        ];
    }

    /**
     * Get hook execution statistics (for performance monitoring)
     *
     * @return array{actions: int, filters: int, executions: int}
     */
    public function get_stats(): array
    {
        return $this->stats;
    }

    /**
     * Clear all hooks (primarily for testing)
     *
     * @param string|null $hook Specific hook to clear, or null for all
     * @param string $type Hook type ('action', 'filter', or 'all')
     * @return void
     */
    public function clear_hooks(?string $hook = null, string $type = 'all'): void
    {
        if ($hook !== null) {
            if ($type === 'all' || $type === 'action') {
                unset($this->actions[$hook], $this->sortedActionsCache[$hook]);
            }

            if ($type === 'all' || $type === 'filter') {
                unset($this->filters[$hook], $this->sortedFiltersCache[$hook]);
            }
        } else {
            if ($type === 'all' || $type === 'action') {
                $this->actions = [];
                $this->sortedActionsCache = [];
            }

            if ($type === 'all' || $type === 'filter') {
                $this->filters = [];
                $this->sortedFiltersCache = [];
            }
        }

        $this->stats = ['actions' => 0, 'filters' => 0, 'executions' => 0];
    }

    /**
     * Get sorted hooks with caching for performance
     *
     * @param string $hook Hook name
     * @param string $type Hook type ('action' or 'filter')
     * @return array<array{callback: callable, accepted_args: int}> Sorted callbacks
     */
    private function getSortedHooks(string $hook, string $type): array
    {
        $cache = &$this->sortedActionsCache;
        if ($type !== 'action') {
            $cache = &$this->sortedFiltersCache;
        }

        if (isset($cache[$hook])) {
            return $cache[$hook];
        }

        $registry = $type === 'action' ? $this->actions : $this->filters;

        if (!isset($registry[$hook])) {
            return [];
        }

        // Sort by priority (ascending)
        ksort($registry[$hook]);

        // Flatten into a single array
        $sorted = [];
        foreach ($registry[$hook] as $callbacks) {
            foreach ($callbacks as $callback_data) {
                $sorted[] = $callback_data;
            }
        }

        $cache[$hook] = $sorted;

        return $sorted;
    }

    /**
     * Log hook execution errors
     *
     * @param string $hook Hook name
     * @param callable $callback Failed callback
     * @param Throwable $error Exception thrown
     * @return void
     */
    private function logHookError(string $hook, callable $callback, Throwable $error): void
    {
        // Get callback name for logging
        if (is_array($callback)) {
            $callbackName = is_object($callback[0])
                ? get_class($callback[0]) . '::' . $callback[1]
                : $callback[0] . '::' . $callback[1];
        } elseif (is_string($callback)) {
            $callbackName = $callback;
        } else {
            // Object (Closure or invokable)
            /** @var object $callback */
            $callbackName = get_class($callback);
        }

        // Log error (compatible with TorrentPier's logging)
        if (function_exists('bb_log')) {
            bb_log(sprintf(
                'Hook error: %s in callback %s - %s',
                $hook,
                $callbackName,
                $error->getMessage()
            ), 'hook_errors');
        } else {
            error_log(sprintf(
                'TorrentPier Hook Error [%s]: %s in %s - %s',
                $hook,
                $callbackName,
                $error->getFile(),
                $error->getMessage()
            ));
        }
    }
}
