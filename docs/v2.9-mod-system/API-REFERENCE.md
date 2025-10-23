# TorrentPier v3.0.0 Mod System - API Reference

**Version:** 3.0.0
**Last Updated:** 2025-01-15

---

## Table of Contents

1. [Hook System API](#hook-system-api)
2. [Core Hooks Reference](#core-hooks-reference)
3. [ModLoader API](#modloader-api)
4. [AbstractMod API](#abstractmod-api)
5. [ModManager API](#modmanager-api)
6. [ModMigrationManager API](#modmigrationmanager-api)
7. [Template System API](#template-system-api)
8. [Configuration API](#configuration-api)
9. [Database Helpers](#database-helpers)

---

## Hook System API

### Hook::add_action()

Register an action hook.

**Signature:**
```php
Hook::add_action(string $name, callable $callback, int $priority = 10, int $args = 1): void
```

**Parameters:**
- `$name` - Hook name (e.g., `'user.after_login'`)
- `$callback` - Function to execute
- `$priority` - Execution order (lower = earlier, default: 10)
- `$args` - Number of arguments to pass to callback

**Example:**
```php
Hook::add_action('user.after_login', function($user_id) {
    bb_log("User {$user_id} logged in", 'login_log');
}, 10, 1);
```

---

### Hook::do_action()

Execute all registered action hooks.

**Signature:**
```php
Hook::do_action(string $name, ...$args): void
```

**Parameters:**
- `$name` - Hook name
- `...$args` - Arguments to pass to callbacks

**Example:**
```php
// Core code
Hook::do_action('user.after_login', $user_id);
```

---

### Hook::add_filter()

Register a filter hook.

**Signature:**
```php
Hook::add_filter(string $name, callable $callback, int $priority = 10, int $args = 1): void
```

**Parameters:**
- `$name` - Hook name (e.g., `'post.can_edit'`)
- `$callback` - Function that transforms value
- `$priority` - Execution order (lower = earlier, default: 10)
- `$args` - Number of arguments to pass to callback

**Example:**
```php
Hook::add_filter('post.can_edit', function($can_edit, $post_id, $userdata) {
    // Block editing if user is in readonly mode
    if ($userdata['readonly'] != 0) {
        return false;
    }
    return $can_edit;
}, 10, 3);
```

---

### Hook::apply_filter()

Apply all registered filters and return transformed value.

**Signature:**
```php
Hook::apply_filter(string $name, mixed $value, ...$args): mixed
```

**Parameters:**
- `$name` - Hook name
- `$value` - Initial value to transform
- `...$args` - Additional arguments to pass to callbacks

**Returns:** Transformed value

**Example:**
```php
// Core code
$can_edit = Hook::apply_filter('post.can_edit', true, $post_id, $userdata);
if (!$can_edit) {
    message_die(GENERAL_MESSAGE, 'Cannot edit post');
}
```

---

### Hook::remove_action()

Remove a registered action hook.

**Signature:**
```php
Hook::remove_action(string $name, callable $callback, int $priority = 10): bool
```

**Example:**
```php
$callback = function($user_id) { ... };
Hook::add_action('user.after_login', $callback);
// Later...
Hook::remove_action('user.after_login', $callback);
```

---

### Hook::remove_filter()

Remove a registered filter hook.

**Signature:**
```php
Hook::remove_filter(string $name, callable $callback, int $priority = 10): bool
```

---

### Hook::has_listeners()

Check if a hook has any registered listeners.

**Signature:**
```php
Hook::has_listeners(string $name): bool
```

**Example:**
```php
if (Hook::has_listeners('post.before_create')) {
    // Load mods that registered this hook
}
```

---

## Core Hooks Reference

### User Hooks

#### `user.after_login`
Fired after successful user login.

**Parameters:**
- `$user_id` (int) - User ID

**Example:**
```php
Hook::add_action('user.after_login', function($user_id) {
    // Log login time
    DB()->query("UPDATE " . BB_USERS . "
                 SET last_login = " . TIMENOW . "
                 WHERE user_id = " . (int)$user_id);
});
```

---

#### `user.after_logout`
Fired after user logout.

**Parameters:**
- `$user_id` (int) - User ID

---

#### `user.after_register`
Fired after new user registration.

**Parameters:**
- `$user_id` (int) - New user ID
- `$username` (string) - Username
- `$email` (string) - Email address

**Example:**
```php
Hook::add_action('user.after_register', function($user_id, $username, $email) {
    // Send welcome email
    send_email($email, 'Welcome to TorrentPier!', ...);
}, 10, 3);
```

---

#### `user.can_register`
Filter whether a user can register.

**Parameters:**
- `$can_register` (bool) - Default: true
- `$username` (string) - Requested username
- `$email` (string) - Requested email

**Returns:** bool

**Example:**
```php
Hook::add_filter('user.can_register', function($can, $username, $email) {
    // Block disposable emails
    if (str_ends_with($email, '@tempmail.com')) {
        return false;
    }
    return $can;
}, 10, 3);
```

---

#### `user.profile_data`
Modify user profile data before rendering.

**Parameters:**
- `$data` (array) - Profile data

**Returns:** array

**Example:**
```php
Hook::add_filter('user.profile_data', function($data) {
    // Add custom field
    $data['custom_title'] = get_user_custom_title($data['user_id']);
    return $data;
});
```

---

### Post Hooks

#### `post.before_create`
Fired before creating a new post.

**Parameters:**
- `$post_data` (array) - Post data (by reference)

**Example:**
```php
Hook::add_action('post.before_create', function(&$post_data) {
    // Auto-add signature
    $post_data['post_text'] .= "\n\n[auto-signature]";
});
```

---

#### `post.after_create`
Fired after creating a new post.

**Parameters:**
- `$post_id` (int) - New post ID
- `$topic_id` (int) - Topic ID
- `$forum_id` (int) - Forum ID

---

#### `post.before_edit`
Fired before editing a post.

**Parameters:**
- `$post_id` (int) - Post ID
- `$old_data` (array) - Original post data
- `$new_data` (array) - Modified post data (by reference)

---

#### `post.after_edit`
Fired after editing a post.

**Parameters:**
- `$post_id` (int) - Post ID
- `$old_data` (array) - Original post data
- `$new_data` (array) - New post data

---

#### `post.can_edit`
Filter whether a post can be edited.

**Parameters:**
- `$can_edit` (bool) - Default permission
- `$post_id` (int) - Post ID
- `$userdata` (array) - Current user data

**Returns:** bool

**Example:**
```php
Hook::add_filter('post.can_edit', function($can, $post_id, $user) {
    // Block editing for users in readonly mode
    if ($user['readonly'] != 0) {
        return false;
    }
    return $can;
}, 10, 3);
```

---

#### `post.can_delete`
Filter whether a post can be deleted.

**Parameters:**
- `$can_delete` (bool) - Default permission
- `$post_id` (int) - Post ID
- `$userdata` (array) - Current user data

**Returns:** bool

---

### Topic Hooks

#### `topic.before_create`
Fired before creating a new topic.

**Parameters:**
- `$topic_data` (array) - Topic data (by reference)

---

#### `topic.after_create`
Fired after creating a new topic.

**Parameters:**
- `$topic_id` (int) - New topic ID
- `$forum_id` (int) - Forum ID

---

#### `topic.before_move`
Fired before moving a topic.

**Parameters:**
- `$topic_id` (int) - Topic ID
- `$from_forum_id` (int) - Source forum
- `$to_forum_id` (int) - Destination forum

---

#### `topic.after_move`
Fired after moving a topic.

**Parameters:**
- `$topic_id` (int) - Topic ID
- `$from_forum_id` (int) - Source forum
- `$to_forum_id` (int) - Destination forum

---

### AJAX Hooks

#### `ajax.register_handlers`
Register custom AJAX handlers.

**Parameters:**
- `$handlers` (array) - Handler map (by reference)

**Example:**
```php
Hook::add_action('ajax.register_handlers', function(&$handlers) {
    $handlers['karma'] = MOD_PATH . 'karma/ajax/karma.php';
});
```

---

#### `ajax.before_handler`
Fired before executing an AJAX handler.

**Parameters:**
- `$action` (string) - AJAX action name
- `$request` (array) - Request data

---

#### `ajax.after_handler`
Fired after executing an AJAX handler.

**Parameters:**
- `$action` (string) - AJAX action name
- `$result` (mixed) - Handler result

---

### Template Hooks

#### `template.before_render`
Fired before rendering a template.

**Parameters:**
- `$template_name` (string) - Template name
- `$vars` (array) - Template variables (by reference)

**Example:**
```php
Hook::add_action('template.before_render', function($name, &$vars) {
    if ($name === 'viewtopic') {
        // Add custom variable
        $vars['CUSTOM_HEADER'] = 'My Custom Header';
    }
}, 10, 2);
```

---

#### `template.after_render`
Fired after rendering a template.

**Parameters:**
- `$template_name` (string) - Template name
- `$output` (string) - Rendered HTML (by reference)

---

#### `template.file`
Filter template file path (for overrides).

**Parameters:**
- `$file` (string) - Template file path
- `$name` (string) - Template name

**Returns:** string (modified path)

**Example:**
```php
Hook::add_filter('template.file', function($file, $name) {
    if ($name === 'viewtopic') {
        // Use mod's custom template
        return MOD_PATH . 'karma/templates/viewtopic.tpl';
    }
    return $file;
}, 10, 2);
```

---

### ViewTopic Hooks

#### `viewtopic.post_data`
Modify post data before rendering.

**Parameters:**
- `$post_data` (array) - Post data (by reference)

**Example:**
```php
Hook::add_action('viewtopic.post_data', function(&$post) {
    // Add karma display
    $post['KARMA'] = $post['user_karma'];
    $post['KARMA_COLOR'] = $post['post_karma'] < 0 ? 'red' : 'green';
});
```

---

#### `viewtopic.query_before`
Modify SQL query for fetching posts.

**Parameters:**
- `$sql` (string) - SQL query (by reference)

---

#### `viewtopic.query_after`
Modify fetched posts data.

**Parameters:**
- `$posts` (array) - Array of posts (by reference)

---

### Search Hooks

#### `search.query_before`
Modify search query.

**Parameters:**
- `$query` (string) - Search query (by reference)
- `$options` (array) - Search options

---

#### `search.results_after`
Modify search results.

**Parameters:**
- `$results` (array) - Search results (by reference)

---

### Cron Hooks

#### `cron.register_jobs`
Register custom cron jobs.

**Parameters:**
- `$jobs` (array) - Job definitions (by reference)

**Example:**
```php
Hook::add_action('cron.register_jobs', function(&$jobs) {
    $jobs['karma_cleanup'] = [
        'file' => MOD_PATH . 'karma/cron/cleanup.php',
        'schedule' => '0 2 * * *', // Daily at 2 AM
    ];
});
```

---

#### `cron.before_job`
Fired before running a cron job.

**Parameters:**
- `$job_name` (string) - Job name

---

#### `cron.after_job`
Fired after running a cron job.

**Parameters:**
- `$job_name` (string) - Job name
- `$result` (mixed) - Job result

---

### Configuration Hooks

#### `config.get`
Filter configuration value.

**Parameters:**
- `$value` (mixed) - Config value
- `$key` (string) - Config key

**Returns:** mixed

**Example:**
```php
Hook::add_filter('config.get', function($value, $key) {
    if ($key === 'karma.hide' && $value === null) {
        return -500; // Default value
    }
    return $value;
}, 10, 2);
```

---

#### `config.set`
Fired when configuration is changed.

**Parameters:**
- `$key` (string) - Config key
- `$value` (mixed) - New value
- `$old_value` (mixed) - Previous value

---

### Mod System Hooks

#### `mod.before_activate`
Fired before activating a mod.

**Parameters:**
- `$mod_id` (string) - Mod ID

---

#### `mod.after_activate`
Fired after activating a mod.

**Parameters:**
- `$mod_id` (string) - Mod ID

---

#### `mod.before_deactivate`
Fired before deactivating a mod.

**Parameters:**
- `$mod_id` (string) - Mod ID

---

#### `mod.after_deactivate`
Fired after deactivating a mod.

**Parameters:**
- `$mod_id` (string) - Mod ID

---

## ModLoader API

### ModLoader::getInstance()

Get ModLoader singleton instance.

**Signature:**
```php
ModLoader::getInstance(): ModLoader
```

**Example:**
```php
$loader = ModLoader::getInstance();
$mods = $loader->getActiveMods();
```

---

### ModLoader::getActiveMods()

Get all active mods.

**Returns:** array

**Example:**
```php
$active_mods = ModLoader::getInstance()->getActiveMods();
foreach ($active_mods as $mod_id => $mod) {
    echo $mod->getName() . "\n";
}
```

---

### ModLoader::getMod()

Get a specific mod instance.

**Signature:**
```php
ModLoader::getMod(string $mod_id): ?AbstractMod
```

**Example:**
```php
$karma_mod = ModLoader::getInstance()->getMod('karma');
if ($karma_mod) {
    $hide_threshold = $karma_mod->config('hide', -500);
}
```

---

## AbstractMod API

### Properties

```php
protected array $manifest;   // Mod manifest data
protected string $path;      // Mod directory path
protected array $config;     // Mod configuration
```

---

### Lifecycle Methods

#### activate()

Called when mod is activated.

**Example:**
```php
public function activate(): void
{
    // Run database migrations
    $this->runMigrations();

    // Register permissions
    $this->registerPermissions(['manage_karma', 'view_karma']);

    // Clear cache
    CACHE()->clear('user_permissions');
}
```

---

#### deactivate()

Called when mod is deactivated.

**Example:**
```php
public function deactivate(): void
{
    // Optional cleanup
    CACHE()->clear('karma_data');
}
```

---

#### uninstall()

Called when mod is uninstalled.

**Example:**
```php
public function uninstall(): void
{
    if ($this->config('remove_data_on_uninstall', false)) {
        // Drop tables
        DB()->query("DROP TABLE IF EXISTS " . BB_KARMA);

        // Remove user data
        DB()->query("ALTER TABLE " . BB_USERS . " DROP COLUMN user_karma");
    }
}
```

---

#### upgrade()

Called when mod is upgraded to a new version.

**Parameters:**
- `$from_version` (string) - Previous version

**Example:**
```php
public function upgrade(string $from_version): void
{
    if (version_compare($from_version, '2.0.0', '<')) {
        // Run migration for v2.0.0
        $this->runMigration('migrations/002_add_karma_v2.sql');
    }
}
```

---

### Helper Methods

#### config()

Get mod configuration value.

**Signature:**
```php
protected function config(string $key, mixed $default = null): mixed
```

**Example:**
```php
$hide_threshold = $this->config('hide', -500);
$time_rules = $this->config('time', []);
```

---

#### trans()

Get translated string.

**Signature:**
```php
protected function trans(string $key, array $params = []): string
```

**Example:**
```php
$message = $this->trans('karma.vote_success', ['points' => 10]);
```

---

#### runMigrations()

Run all pending database migrations.

**Signature:**
```php
protected function runMigrations(): void
```

---

#### registerPermissions()

Register mod permissions.

**Signature:**
```php
protected function registerPermissions(array $permissions): void
```

**Example:**
```php
$this->registerPermissions([
    'manage_karma' => 'Manage karma system',
    'view_karma' => 'View karma statistics',
]);
```

---

## ModManager API

### ModManager::activate()

Activate a mod.

**Signature:**
```php
ModManager::activate(string $mod_id): Result
```

**Example:**
```php
$result = ModManager::activate('karma');
if ($result->isSuccess()) {
    echo "Activated successfully";
} else {
    echo "Error: " . $result->getError();
}
```

---

### ModManager::deactivate()

Deactivate a mod.

**Signature:**
```php
ModManager::deactivate(string $mod_id): Result
```

---

### ModManager::install()

Install a mod from ZIP file.

**Signature:**
```php
ModManager::install(string $zip_path): Result
```

**Example:**
```php
$result = ModManager::install('/tmp/karma.zip');
if ($result->isSuccess()) {
    $mod_id = $result->getData('mod_id');
    echo "Installed mod: {$mod_id}";
}
```

---

### ModManager::uninstall()

Uninstall a mod.

**Signature:**
```php
ModManager::uninstall(string $mod_id, bool $remove_data = false): Result
```

---

### ModManager::getInstalled()

Get all installed mods.

**Returns:** array

---

### ModManager::checkCompatibility()

Check if mod is compatible with current TorrentPier version.

**Signature:**
```php
ModManager::checkCompatibility(string $mod_id): ValidationResult
```

---

## ModMigrationManager API

**Class:** `TorrentPier\ModSystem\ModMigrationManager`

Handles database migrations for mods. All mod migrations are tracked in a single `bb_mod_migrations` table to avoid creating separate tables for each mod.

**Database schema:**
```sql
CREATE TABLE bb_mod_migrations (
    mod_id VARCHAR(64) NOT NULL,
    version BIGINT NOT NULL,
    migration_name VARCHAR(100),
    start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    breakpoint BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (mod_id, version)
);
```

---

### ModMigrationManager::run()

Execute pending migrations for a mod.

**Signature:**
```php
ModMigrationManager::run(string $modId, string $migrationsPath): void
```

**Parameters:**
- `$modId` - Mod identifier (e.g., `'karma-system'`)
- `$migrationsPath` - Path to migrations directory (e.g., `'/path/to/mods/karma-system/migrations'`)

**Behavior:**
1. Reads `bb_mod_migrations` to find executed migrations for this mod
2. Scans `$migrationsPath` for `*.sql` files
3. Executes pending migrations in alphabetical order
4. Each migration runs in a transaction
5. Records execution in `bb_mod_migrations` table

**Example:**
```php
$manager = new ModMigrationManager();
$manager->run('karma-system', __DIR__ . '/migrations');
// Executes: 001_create_tables.sql, 002_add_indexes.sql (if not already run)
```

**Migration file format:**
```sql
-- Migration: 001_create_tables.sql
-- Description: Create karma tables
-- Date: 2025-01-15

CREATE TABLE IF NOT EXISTS bb_karma (
    user_id INT NOT NULL PRIMARY KEY,
    karma_points INT NOT NULL DEFAULT 0,
    -- ...
);
```

**Error handling:**
- If migration fails, transaction rolls back
- Error is logged with migration filename
- Execution stops at failed migration
- Previously executed migrations remain recorded

---

### ModMigrationManager::rollback()

Rollback migrations for a mod (used during deactivation).

**Signature:**
```php
ModMigrationManager::rollback(string $modId, ?int $targetVersion = null): void
```

**Parameters:**
- `$modId` - Mod identifier
- `$targetVersion` - Version to rollback to (null = rollback all)

**Warning:** This executes `DROP TABLE` statements. Use with caution!

**Example:**
```php
// Rollback all migrations for karma-system
$manager->rollback('karma-system');

// Rollback to specific version
$manager->rollback('karma-system', 001);
```

---

### ModMigrationManager::getExecuted()

Get list of executed migrations for a mod.

**Signature:**
```php
ModMigrationManager::getExecuted(string $modId): array
```

**Returns:**
```php
[
    [
        'mod_id' => 'karma-system',
        'version' => 1,
        'migration_name' => '001_create_tables.sql',
        'start_time' => '2025-01-15 10:00:00',
        'end_time' => '2025-01-15 10:00:01'
    ],
    // ...
]
```

---

### ModMigrationManager::getPending()

Get list of pending migrations for a mod.

**Signature:**
```php
ModMigrationManager::getPending(string $modId, string $migrationsPath): array
```

**Returns:** Array of migration filenames that haven't been executed yet.

---

### Usage in AbstractMod

```php
class MyMod extends AbstractMod
{
    public function activate(): void
    {
        // Run migrations during activation
        $this->runMigrations();
    }

    public function deactivate(): void
    {
        // Optionally rollback migrations
        if ($this->config('rollback_on_deactivate', false)) {
            $this->rollbackMigrations();
        }
    }

    protected function runMigrations(): void
    {
        $manager = new ModMigrationManager();
        $manager->run($this->id, $this->path . '/migrations');
    }

    protected function rollbackMigrations(): void
    {
        $manager = new ModMigrationManager();
        $manager->rollback($this->id);
    }
}
```

---

## Template System API

### Template Hook System

TorrentPier provides a comprehensive template hook system that allows mods to integrate with the template rendering pipeline.

**Available Hooks:**
- `template_before_assign_vars` - Modify template variables before assignment
- `template_before_compile` - Modify template source before compilation
- `template_after_compile` - Modify compiled PHP code
- `template_before_render` - Execute code before template rendering
- `template_after_render` - Modify final HTML output

**Basic Example:**
```php
// In your mod's init() method
public function init(): void
{
    $template = $GLOBALS['template'] ?? null;

    if ($template instanceof \TorrentPier\Legacy\Template) {
        // Inject template variables
        $template->registerHook('template_before_assign_vars', [$this, 'addVars']);

        // Modify final output
        $template->registerHook('template_after_render', [$this, 'injectCode']);
    }
}

public function addVars(array $vars): array
{
    $vars['MOD_KARMA'] = $this->getUserKarma();
    return $vars;
}

public function injectCode(string $output): string
{
    // Inject CSS/JS
    $css = '<link rel="stylesheet" href="/mods/karma/karma.css">';
    return str_replace('</head>', $css . '</head>', $output);
}
```

**See:** [Template Hooks API Documentation](TEMPLATE-HOOKS.md) for complete reference.

### Legacy Template Hook Points

Templates can also include hook points for mods to inject content (legacy method).

**Syntax:**
```html
<!-- BEGIN mod_hook_name -->
{VARIABLE_NAME}
<!-- END mod_hook_name -->
```

**Example:**
```html
<div class="post-author">
    {postrow.POSTER_NAME}

    <!-- BEGIN mod_hook_author -->
    {postrow.MOD_HOOK_AUTHOR}
    <!-- END mod_hook_author -->
</div>
```

**Mod injection:**
```php
Hook::add_action('viewtopic.post_data', function(&$post) {
    $post['MOD_HOOK_AUTHOR'] = '<span class="karma">Karma: ' . $post['user_karma'] . '</span>';
});
```

---

### Template Override

Mods can override entire templates.

**Example:**
```php
Hook::add_filter('template.file', function($file, $name) {
    if ($name === 'viewtopic' && mod_config('karma.custom_template')) {
        return MOD_PATH . 'karma/templates/viewtopic.tpl';
    }
    return $file;
}, 10, 2);
```

---

## Configuration API

### config()

Global configuration helper.

**Signature:**
```php
config(string $key = null, mixed $default = null): mixed
```

**Examples:**
```php
// Get value
$timezone = config('board_timezone', 0);

// Get mod config
$karma_hide = config('mods.karma.hide', -500);

// Get all config
$all_config = config();
```

---

### Mod Config Namespacing

All mod configs are automatically namespaced under `mods.{mod_id}`.

**Mod config file (`config.php`):**
```php
return [
    'enabled' => true,
    'hide' => -500,
];
```

**Access:**
```php
config('mods.karma.enabled'); // true
config('mods.karma.hide'); // -500
```

---

### Config Overrides

Admin can override mod config in `config.local.php`:

```php
// config.local.php
$bb_cfg['mods']['karma']['hide'] = -1000; // Override default -500
```

---

## Database Helpers

### DB()

Database singleton.

**Examples:**
```php
// Select
$row = DB()->fetch_row("SELECT * FROM " . BB_KARMA . " WHERE user_id = " . (int)$user_id);

// Insert
DB()->query("INSERT INTO " . BB_KARMA . " SET user_id = " . (int)$user_id . ", karma = 0");

// Update
DB()->query("UPDATE " . BB_KARMA . " SET karma = karma + 1 WHERE user_id = " . (int)$user_id);

// Delete
DB()->query("DELETE FROM " . BB_KARMA . " WHERE user_id = " . (int)$user_id);
```

---

### Table Name Constants

Define mod table constants in `Mod.php`:

```php
define('BB_KARMA', 'bb_karma');
```

Access in hooks:
```php
DB()->query("SELECT * FROM " . BB_KARMA);
```

---

## Constants

### Mod Path Constants

```php
MOD_PATH             // /path/to/torrentpier/mods/
BB_ROOT              // /path/to/torrentpier/
TEMPLATES_DIR        // /path/to/torrentpier/templates/
```

---

### Hook Priority Constants

```php
HOOK_PRIORITY_FIRST   = 1     // Run first
HOOK_PRIORITY_HIGH    = 5     // High priority
HOOK_PRIORITY_NORMAL  = 10    // Default
HOOK_PRIORITY_LOW     = 15    // Low priority
HOOK_PRIORITY_LAST    = 999   // Run last
```

---

## Best Practices

### 1. Always Escape User Input

```php
// ❌ Bad
$username = $_POST['username'];
DB()->query("SELECT * FROM " . BB_USERS . " WHERE username = '$username'");

// ✅ Good
$username = DB()->escape($_POST['username']);
DB()->query("SELECT * FROM " . BB_USERS . " WHERE username = '$username'");
```

---

### 2. Use Priorities Wisely

```php
// Run before other mods
Hook::add_filter('post.can_edit', $callback, 5);

// Run after other mods
Hook::add_filter('post.can_edit', $callback, 15);
```

---

### 3. Check Hook Existence

```php
if (Hook::has_listeners('post.before_create')) {
    // Hook is registered, safe to use
}
```

---

### 4. Cache Expensive Operations

```php
$karma = CACHE('karma')->get("karma:{$user_id}");
if (!$karma) {
    $karma = calculate_user_karma($user_id);
    CACHE('karma')->set("karma:{$user_id}", $karma, 3600);
}
```

---

### 5. Use Transactions for Multiple Queries

```php
DB()->transaction(function() use ($user_id) {
    DB()->query("UPDATE " . BB_USERS . " SET user_karma = user_karma + 1 WHERE user_id = " . (int)$user_id);
    DB()->query("INSERT INTO " . BB_KARMA . " SET user_id = " . (int)$user_id . ", action = 'upvote'");
});
```

---

## Debugging

### Enable Mod Debug Mode

```php
// config.local.php
$bb_cfg['mod_debug'] = true;
```

This will:
- Log all hook executions
- Show loaded mods on every page
- Display hook execution times

---

### Check Registered Hooks

```php
// In admin panel or CLI
$hooks = Hook::getRegisteredHooks();
print_r($hooks);
```

---

### Test Mod Activation

```php
try {
    ModManager::activate('karma');
} catch (Exception $e) {
    echo "Activation failed: " . $e->getMessage();
    echo "\nStack trace:\n" . $e->getTraceAsString();
}
```

---

**End of API Reference**
