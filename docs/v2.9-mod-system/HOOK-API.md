# Hook System API Documentation

> **TorrentPier v2.9 Mod System - Hook API**
> WordPress-style hooks for extending core functionality without modifying files

---

## Table of Contents

1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [Core Concepts](#core-concepts)
4. [API Reference](#api-reference)
5. [Available Hooks](#available-hooks)
6. [Best Practices](#best-practices)
7. [Advanced Usage](#advanced-usage)
8. [Performance](#performance)
9. [Debugging](#debugging)

---

## Overview

The Hook System provides a WordPress-style event-driven architecture that allows mods to extend TorrentPier's functionality without modifying core files. There are two types of hooks:

- **Actions**: Execute code at specific points (e.g., after a post is created)
- **Filters**: Modify data before it's used (e.g., change post content before display)

### Why Use Hooks?

- ✅ **No core file modifications** - Keep your installation clean and upgradeable
- ✅ **Mod compatibility** - Multiple mods can hook into the same events
- ✅ **Clean separation** - Mod logic is isolated from core code
- ✅ **Priority control** - Control execution order when multiple hooks exist
- ✅ **Testable** - Easy to unit test hook callbacks

---

## Quick Start

### Adding an Action Hook

```php


// Execute code after a post is created
hooks()->add_action('post.after_create', function($postId, $postData) {
    // Your code here
    error_log("New post created: {$postId}");
}, 10, 2); // Priority 10, accepts 2 arguments
```

### Adding a Filter Hook

```php


// Modify post content before display
hooks()->add_filter('post.content', function($content, $postId) {
    // Add signature to all posts
    return $content . "\n\n---\nPosted via TorrentPier v2.9";
}, 10, 2);
```

---

## Core Concepts

### Actions vs Filters

| Feature | Actions | Filters |
|---------|---------|---------|
| **Purpose** | Execute code at a point | Modify data |
| **Return value** | Ignored | Required |
| **Use case** | Send notifications, log events | Transform content, validate data |
| **Execution** | `do_action('hook.name', $arg1, $arg2)` | `$value = apply_filter('hook.name', $value, $arg1)` |

### Priority

- Hooks with **lower priority** execute first (default: 10)
- Use priority to control execution order:
  - `1-5`: Early execution (validation, preprocessing)
  - `10`: Normal execution (default)
  - `15-20`: Late execution (logging, analytics)
  - `99+`: Final execution (cleanup, finalization)

```php
// This runs first
hooks()->add_action('user.login', $callback1, 5);

// This runs second
hooks()->add_action('user.login', $callback2, 10);

// This runs last
hooks()->add_action('user.login', $callback3, 20);
```

### Accepted Arguments

Specify how many arguments your callback accepts:

```php
// Accepts 1 argument (default)
hooks()->add_action('post.create', function($postId) {
    // Only $postId is passed
}, 10, 1);

// Accepts 3 arguments
hooks()->add_action('post.create', function($postId, $postData, $userId) {
    // All 3 arguments are passed
}, 10, 3);
```

---

## API Reference

### Adding Hooks

#### `hooks()->add_action(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void`

Register a function to execute when an action is triggered.

**Parameters:**
- `$hook` - Hook name (e.g., `'post.after_create'`)
- `$callback` - Function to execute (closure, array, or function name)
- `$priority` - Execution priority (lower = earlier, default: 10)
- `$accepted_args` - Number of arguments to pass (default: 1)

**Example:**
```php
hooks()->add_action('post.after_create', function($postId, $postData) {
    // Send notification
    sendNotification($postId, $postData);
}, 10, 2);
```

#### `hooks()->add_filter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void`

Register a function to modify data when a filter is applied.

**Parameters:**
- `$hook` - Hook name (e.g., `'post.content'`)
- `$callback` - Function to modify and return data
- `$priority` - Execution priority (lower = earlier, default: 10)
- `$accepted_args` - Number of arguments to pass (default: 1)

**Example:**
```php
hooks()->add_filter('post.content', function($content, $postId) {
    // Sanitize HTML
    return strip_tags($content, '<p><a><b><i>');
}, 10, 2);
```

### Executing Hooks

#### `hooks()->do_action(string $hook, mixed ...$args): void`

Execute all callbacks registered to an action hook.

**Parameters:**
- `$hook` - Hook name
- `...$args` - Arguments to pass to callbacks

**Example:**
```php
// In core code (library/includes/functions_post.php)
hooks()->do_action('post.after_create', $postId, $postData, $userId);
```

#### `hooks()->apply_filter(string $hook, mixed $value, mixed ...$args): mixed`

Apply all filter callbacks to modify a value.

**Parameters:**
- `$hook` - Hook name
- `$value` - Initial value to modify
- `...$args` - Additional arguments for callbacks

**Returns:** Modified value after all filters applied

**Example:**
```php
// In core code
$content = hooks()->apply_filter('post.content', $rawContent, $postId);
```

### Removing Hooks

#### `hooks()->remove_action(string $hook, callable $callback, int $priority = 10): bool`

Remove a specific action callback.

**Returns:** `true` if removed, `false` if not found

**Example:**
```php
$callback = function($postId) { /* ... */ };
hooks()->add_action('post.create', $callback);

// Later, remove it
hooks()->remove_action('post.create', $callback);
```

#### `hooks()->remove_filter(string $hook, callable $callback, int $priority = 10): bool`

Remove a specific filter callback.

**Returns:** `true` if removed, `false` if not found

#### `hooks()->remove_all_hooks(string $hook, string $type): int`

Remove all hooks of a specific type from a hook.

**Parameters:**
- `$hook` - Hook name
- `$type` - Either `'action'` or `'filter'`

**Returns:** Number of callbacks removed

**Example:**
```php
// Remove all action callbacks from 'post.create'
$removed = hooks()->remove_all_hooks('post.create', 'action');
```

### Querying Hooks

#### `hooks()->has_action(string $hook, ?callable $callback = null): bool`

Check if an action hook has callbacks registered.

**Parameters:**
- `$hook` - Hook name
- `$callback` - Optional specific callback to check for

**Returns:** `true` if hook has callbacks

#### `hooks()->has_filter(string $hook, ?callable $callback = null): bool`

Check if a filter hook has callbacks registered.

#### `hooks()->get_hooks(string $type): array`

Get all registered hooks of a specific type.

**Parameters:**
- `$type` - Either `'action'` or `'filter'`

**Returns:** Array of hook names with their callbacks

**Example:**
```php
// Get all action hooks
$actions = hooks()->get_hooks('action');

// Get all filter hooks
$filters = hooks()->get_hooks('filter');
```

### Performance

#### `hooks()->clear_cache(): void`

Clear the internal cache of sorted hooks. Automatically called when hooks are added/removed.

**Example:**
```php
// Force cache clear (rarely needed)
hooks()->clear_cache();
```

---

## Available Hooks

TorrentPier v2.9 provides 50+ built-in hooks across all major subsystems. For a complete, up-to-date list with usage examples, see:

📄 **[/library/hooks.php](/library/hooks.php)** - Complete hook reference with PHPDoc examples

### Hook Categories

- **AJAX Hooks** - `ajax.before_execute`, `ajax.after_execute`, `ajax.before_{action}`, `ajax.after_{action}`
- **Post Hooks** - `post.before_create`, `post.after_create`, `post.before_delete`, `post.content`
- **Topic Hooks** - `topic.before_create`, `topic.after_create`, `topic.title`, `topic.status_change`
- **User Hooks** - `user.before_login`, `user.after_login`, `user.before_register`, `user.avatar_url`
- **Template Hooks** - `template.before_render`, `template.after_render`, `template.vars`
- **Torrent Hooks** - `torrent.before_register`, `torrent.after_announce`, `torrent.search_results`
- **Search Hooks** - `search.before_query`, `search.results`, `search.query`
- **Admin Hooks** - `admin.panel_nav`, `admin.before_save`, `admin.after_save`
- **Cron Hooks** - `cron.before_task`, `cron.after_task`
- **Database Hooks** - `db.before_query`, `db.after_query`, `db.query_error`
- **Cache Hooks** - `cache.before_get`, `cache.after_set`, `cache.miss`
- **Mod System Hooks** - `mod.before_activate`, `mod.after_activate`, `mod.before_load`

---

## Best Practices

### 1. Use Descriptive Hook Names

```php
// ❌ Bad - Too generic
hooks()->add_action('init', $callback);

// ✅ Good - Specific and clear
hooks()->add_action('user.after_login', $callback);
```

### 2. Always Return Values in Filters

```php
// ❌ Bad - Doesn't return value
hooks()->add_filter('post.content', function($content) {
    $content = sanitize($content);
    // Missing return!
});

// ✅ Good - Returns modified value
hooks()->add_filter('post.content', function($content) {
    return sanitize($content);
});
```

### 3. Use Correct Argument Count

```php
// Hook passes 3 arguments: $postId, $postData, $userId
hooks()->do_action('post.after_create', $postId, $postData, $userId);

// ❌ Bad - Only accepts 1 argument (others ignored)
hooks()->add_action('post.after_create', function($postId) {
    // $postData and $userId are not accessible!
}, 10, 1);

// ✅ Good - Accepts all 3 arguments
hooks()->add_action('post.after_create', function($postId, $postData, $userId) {
    // All arguments accessible
}, 10, 3);
```

### 4. Handle Errors Gracefully

```php
hooks()->add_action('post.after_create', function($postId, $postData) {
    try {
        // Your code that might fail
        sendNotification($postId, $postData);
    } catch (\Exception $e) {
        // Log error instead of breaking the site
        error_log("Notification failed: " . $e->getMessage());
    }
}, 10, 2);
```

### 5. Use Priority Wisely

```php
// Validation runs first (priority 5)
hooks()->add_filter('post.content', 'validateContent', 5);

// Sanitization runs second (priority 10 - default)
hooks()->add_filter('post.content', 'sanitizeContent', 10);

// Formatting runs last (priority 15)
hooks()->add_filter('post.content', 'formatContent', 15);
```

### 6. Namespace Your Hooks in Mods

```php
// ❌ Bad - Generic, conflicts possible
hooks()->add_action('email_sent', $callback);

// ✅ Good - Namespaced to your mod
hooks()->add_action('mymod.email_sent', $callback);
```

### 7. Document Your Custom Hooks

```php
/**
 * Fires after karma points are awarded
 *
 * @param int $userId User ID receiving karma
 * @param int $points Points awarded
 * @param string $reason Reason for award
 *
 * @since 1.0.0
 */
hooks()->do_action('karma.after_award', $userId, $points, $reason);
```

---

## Advanced Usage

### Using Class Methods as Callbacks

```php
class MyMod {
    public function onPostCreate($postId) {
        // Handle post creation
    }

    public function registerHooks() {
        // Instance method
        hooks()->add_action('post.after_create', [$this, 'onPostCreate']);

        // Static method
        hooks()->add_action('post.after_create', [self::class, 'onPostCreateStatic']);
    }

    public static function onPostCreateStatic($postId) {
        // Static method handler
    }
}
```

### Conditional Hook Registration

```php
// Only register hook if feature is enabled
if (config()->get('mods.karma.enabled', false)) {
    hooks()->add_action('post.after_create', function($postId, $postData, $userId) {
        // Award karma for creating post
        KarmaMod::awardPoints($userId, 5, 'post_create');
    }, 10, 3);
}
```

### Chaining Filters

```php
// Multiple filters modify the same value in sequence
$content = "Hello World";

hooks()->add_filter('post.content', fn($c) => strtoupper($c), 10);
hooks()->add_filter('post.content', fn($c) => $c . '!', 20);

$result = hooks()->apply_filter('post.content', $content);
// Result: "HELLO WORLD!"
```

### Accessing Hook State

```php
// Check if a hook has any callbacks before expensive operations
if (hooks()->has_action('post.after_create')) {
    // Gather expensive data only if hooks need it
    $expensiveData = gatherExpensiveData();
    hooks()->do_action('post.after_create', $postId, $expensiveData);
}
```

### Removing Hooks Conditionally

```php
// Temporarily disable a hook
$callback = function($postId) { /* ... */ };
hooks()->add_action('post.create', $callback);

// Disable during bulk operations
hooks()->remove_action('post.create', $callback);
bulkCreatePosts($data);

// Re-enable after bulk operation
hooks()->add_action('post.create', $callback);
```

### Early Returns in Filters

```php
hooks()->add_filter('post.content', function($content, $postId) {
    // Early return if content doesn't need processing
    if (strlen($content) < 10) {
        return $content;
    }

    // Process longer content
    return processContent($content);
}, 10, 2);
```

---

## Performance

### Caching

The Hook system automatically caches sorted hooks for performance. Each hook is sorted by priority only once, then cached until modified.

**Cache Invalidation:**
- Automatic when `add_action()`, `add_filter()`, `remove_action()`, or `remove_filter()` is called
- Manual: `hooks()->clear_cache()`

### Performance Tips

1. **Minimize Hook Callbacks**: Each callback adds overhead
2. **Use Appropriate Priority**: Avoid unnecessary sorting
3. **Check Hook Existence**: Use `has_action()` before expensive operations
4. **Limit Arguments**: Only accept arguments you need (`$accepted_args`)
5. **Avoid Heavy Operations**: Move expensive code outside of high-frequency hooks

### Performance Benchmarks

From `tests/Hooks/HookTest.php`:

```php
// 1000 hook executions with 3 callbacks each
// Average: ~0.15ms per execution
// Total: ~150ms for 1000 executions
```

**Recommendation**: Hooks are fast enough for all practical use cases. Even high-frequency hooks (e.g., `template.vars` called 100+ times per page) add negligible overhead.

---

## Debugging

### Debug Hook Registration

```php
// Get all registered actions
$actions = hooks()->get_hooks('action');
foreach ($actions as $hook => $callbacks) {
    echo "Hook: {$hook}\n";
    foreach ($callbacks as $priority => $callbackList) {
        echo "  Priority {$priority}: " . count($callbackList) . " callbacks\n";
    }
}
```

### Debug Hook Execution

```php
// Add debug callback with high priority to run first
hooks()->add_action('post.after_create', function(...$args) {
    error_log("post.after_create called with args: " . json_encode($args));
}, 1, 99); // Priority 1, accepts up to 99 arguments
```

### Logging All Hook Calls

```php
// Development only - logs all action hooks
hooks()->add_action('*', function($hook, ...$args) {
    error_log("Action: {$hook} - Args: " . json_encode($args));
}, 1, 99);

// Note: This requires implementing wildcard support in Hook.php
// Currently not supported - would need to be added as a feature
```

### Common Issues

#### Callback Not Executing

**Symptoms**: Your hook callback never runs

**Possible Causes:**
1. Wrong hook name (typo)
2. Hook registered after `do_action()` was called
3. Insufficient `$accepted_args` (callback needs more arguments)
4. Exception thrown in earlier callback (check logs)

**Solution:**
```php
// Verify hook exists
if (!hooks()->has_action('post.after_create')) {
    error_log("Hook not registered!");
}

// Check execution order
error_log("Registering hook at: " . debug_backtrace()[0]['file']);
```

#### Filter Not Modifying Value

**Symptoms**: Filter callback runs but value unchanged

**Possible Causes:**
1. Forgot to return modified value
2. Another filter with higher priority overrides your changes
3. Wrong variable being modified

**Solution:**
```php
// Always return value
hooks()->add_filter('post.content', function($content) {
    $content = modify($content);
    return $content; // ← Don't forget this!
});

// Debug priority order
$filters = hooks()->get_hooks('filter')['post.content'] ?? [];
ksort($filters);
print_r($filters); // See execution order
```

---

## Examples

### Example 1: Karma System

```php
// Award karma points when users create posts
hooks()->add_action('post.after_create', function($postId, $postData, $userId) {
    $karma = KARMA(); // Get karma system instance
    $karma->awardPoints($userId, 5, 'Created post: ' . $postId);
}, 10, 3);

// Award karma when posts receive replies
hooks()->add_action('post.after_reply', function($postId, $replyId, $authorId) {
    $karma = KARMA();
    $karma->awardPoints($authorId, 2, 'Received reply on post: ' . $postId);
}, 10, 3);
```

### Example 2: Content Sanitization

```php
// Sanitize post content (runs early)
hooks()->add_filter('post.content', function($content) {
    // Remove dangerous HTML
    $allowed = '<p><a><b><i><u><br><ul><ol><li><blockquote><code><pre>';
    return strip_tags($content, $allowed);
}, 5); // Priority 5 - runs early

// Format BBCode (runs after sanitization)
hooks()->add_filter('post.content', function($content) {
    // Convert BBCode to HTML
    return BBCode::parse($content);
}, 10);
```

### Example 3: Notification System

```php
// Send notifications on various events
hooks()->add_action('post.after_create', 'notifyOnNewPost', 10, 3);
hooks()->add_action('topic.after_reply', 'notifyTopicSubscribers', 10, 3);
hooks()->add_action('user.after_mention', 'notifyMentionedUser', 10, 2);

function notifyOnNewPost($postId, $postData, $userId) {
    $subscribers = getTopicSubscribers($postData['topic_id']);
    foreach ($subscribers as $subscriberId) {
        sendNotification($subscriberId, 'new_post', [
            'post_id' => $postId,
            'topic_id' => $postData['topic_id']
        ]);
    }
}
```

### Example 4: Analytics Tracking

```php
// Track user actions for analytics
hooks()->add_action('user.after_login', function($userId) {
    Analytics::track('user_login', ['user_id' => $userId]);
});

hooks()->add_action('torrent.after_download', function($torrentId, $userId) {
    Analytics::track('torrent_download', [
        'torrent_id' => $torrentId,
        'user_id' => $userId
    ]);
}, 10, 2);

hooks()->add_action('search.after_query', function($query, $results) {
    Analytics::track('search', [
        'query' => $query,
        'result_count' => count($results)
    ]);
}, 10, 2);
```

---

## Migration from Legacy Event System

If you're migrating from TorrentPier's legacy event system:

### Before (Legacy)
```php
// Old way - direct core modification
function my_custom_code($postId) {
    // Your code
}

// Required modifying core files to call your function
```

### After (Hook System)
```php
// New way - no core modifications


hooks()->add_action('post.after_create', function($postId) {
    // Your code
}, 10, 1);
```

### Benefits

- ✅ No core file modifications
- ✅ Easy to enable/disable
- ✅ Multiple mods can hook the same event
- ✅ Priority control
- ✅ Easier testing and debugging

---

## Support

- 📖 **Full hook list**: [/library/hooks.php](/library/hooks.php)
- 📘 **Mod system guide**: [/docs/v2.9-mod-system/MODLOADER-API.md](/docs/v2.9-mod-system/MODLOADER-API.md)
- 🔧 **Source code**: [/src/Hooks/Hook.php](/src/Hooks/Hook.php)
- 🧪 **Test suite**: [/tests/Hooks/HookTest.php](/tests/Hooks/HookTest.php)

---

**Last Updated**: 2025-10-22
**Version**: TorrentPier v3.0.0
