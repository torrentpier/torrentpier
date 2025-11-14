# AbstractMod API Documentation

**Version:** 3.0.0
**Component:** `TorrentPier\ModSystem\AbstractMod`
**Status:** Production Ready

## Overview

AbstractMod is the base class that all TorrentPier mods must extend. It provides a comprehensive set of lifecycle hooks, helper methods, and utilities for building mods with database access, caching, configuration management, and hook registration.

## Key Features

- **Lifecycle Hooks**: activate(), deactivate(), uninstall(), upgrade()
- **Configuration Management**: Isolated mod-specific config namespace
- **Database Access**: Direct access to Database instance
- **Cache Management**: Dedicated cache namespace per mod
- **Migrations**: SQL migration runner with transaction support
- **Permissions**: Permission registration system
- **Logging**: Structured logging to database
- **Hook System**: Easy hook registration (actions/filters)
- **File Management**: Safe file inclusion with validation
- **State Queries**: Check activation status, get metadata

## Creating a Mod

### Minimal Mod Example

```php
<?php

declare(strict_types=1);

use TorrentPier\ModSystem\AbstractMod;

class KarmaMod extends AbstractMod
{
    public function activate(): void
    {
        // Run migrations
        $this->runMigrations('migrations');

        // Register permissions
        $this->registerPermissions([
            'karma.view' => 'View karma points',
            'karma.give' => 'Give karma to users',
            'karma.moderate' => 'Moderate karma system'
        ]);

        // Set default config
        $this->setConfig('initial_points', 100);
        $this->setConfig('max_points', 10000);

        $this->log('activate', 'Karma mod activated successfully');
    }

    public function deactivate(): void
    {
        // Clear caches
        $this->getCache()->clean();

        $this->log('deactivate', 'Karma mod deactivated');
    }
}
```

### Directory Structure

```
/mods/karma/
├── manifest.json          # Mod metadata (required)
├── KarmaMod.php          # Main mod class (entrypoint)
├── migrations/           # Database migrations
│   ├── 001_create_karma_table.sql
│   └── 002_add_karma_history.sql
├── templates/            # Mod-specific templates
│   └── karma_widget.tpl
├── assets/              # CSS, JS, images
│   ├── karma.css
│   └── karma.js
└── README.md            # Mod documentation
```

---

## Lifecycle Hooks

Lifecycle hooks are called by ModManager during installation, activation, deactivation, and upgrades.

### `activate(): void`

Called when mod is activated (turned on).

**Purpose:**
- Run database migrations
- Register permissions
- Set up initial configuration
- Register hooks
- Initialize mod features

**Example:**
```php
public function activate(): void
{
    // Run migrations
    $this->runMigrations('migrations');

    // Register permissions
    $this->registerPermissions([
        'reputation.view' => 'View reputation scores',
        'reputation.edit' => 'Edit reputation values'
    ]);

    // Set default config
    $this->setConfig('points_per_post', 5);
    $this->setConfig('points_per_like', 1);

    // Register hooks
    $this->addAction('post_create', [$this, 'onPostCreate'], 10, 2);
    $this->addFilter('user_profile_data', [$this, 'addReputationToProfile'], 10, 1);

    $this->log('activate', 'Reputation mod activated successfully');
}
```

**Notes:**
- Should be idempotent (safe to call multiple times)
- Should NOT delete data (use uninstall for that)
- Throws `ModException` on errors

---

### `deactivate(): void`

Called when mod is deactivated (turned off).

**Purpose:**
- Unregister hooks
- Clear caches
- Disable features
- Clean up temporary data

**Example:**
```php
public function deactivate(): void
{
    // Clear mod-specific cache
    $this->getCache()->clean();

    // Log deactivation
    $this->log('deactivate', 'Mod deactivated', [
        'cached_items_cleared' => $this->getCache()->getStats()['items']
    ]);
}
```

**Notes:**
- Should NOT delete user data or drop tables
- Should be reversible (activate() can be called again)
- Silent failures preferred over exceptions

---

### `uninstall(): void`

Called when mod is completely removed.

**Purpose:**
- Drop database tables
- Delete mod data
- Remove configuration
- Clean up files

**Example:**
```php
public function uninstall(): void
{
    // Drop tables
    if ($this->tableExists('bb_karma_points')) {
        $this->getDb()->query("DROP TABLE bb_karma_points");
    }

    if ($this->tableExists('bb_karma_history')) {
        $this->getDb()->query("DROP TABLE bb_karma_history");
    }

    // Clear all mod config
    config()->clearModConfig($this->getId());

    // Clear cache
    $this->getCache()->clean();

    $this->log('uninstall', 'Mod uninstalled completely');
}
```

**Notes:**
- Destructive operation - cannot be undone
- Should clean up ALL mod data
- Should NOT affect other mods' data

---

### `upgrade(string $oldVersion): void`

Called when mod is upgraded to a new version.

**Purpose:**
- Run version-specific migrations
- Update configuration structure
- Migrate data formats
- Update permissions

**Example:**
```php
public function upgrade(string $oldVersion): void
{
    // Version-specific upgrades
    if (version_compare($oldVersion, '1.1.0', '<')) {
        // Upgrade from 1.0.x to 1.1.0
        $this->runMigrations('migrations/v1.1.0');
        $this->setConfig('new_feature_enabled', true);
    }

    if (version_compare($oldVersion, '2.0.0', '<')) {
        // Upgrade from 1.x to 2.0.0 (breaking changes)
        $this->migrateDataStructure();
        $this->updatePermissions();
    }

    $this->log('upgrade', "Upgraded from {$oldVersion} to {$this->getVersion()}");
}

private function migrateDataStructure(): void
{
    // Example: Rename column
    $this->getDb()->query("
        ALTER TABLE bb_karma_points
        CHANGE COLUMN points karma_score INT NOT NULL
    ");
}
```

**Parameters:**
- `$oldVersion`: Previous version string (e.g., "1.0.0")

**Notes:**
- Called automatically by ModManager
- Should handle all upgrade paths
- Should be idempotent (safe to run multiple times)

---

## Configuration Methods

Mods have an isolated configuration namespace: `mods.{mod_id}.*`

### `config(string $key, mixed $default = null): mixed`

Get mod-specific configuration value.

**Example:**
```php
// Get simple value
$maxPoints = $this->config('max_points', 10000);

// Get nested value
$apiUrl = $this->config('api.endpoint', 'https://api.example.com');

// Different types
$enabled = $this->config('enabled', false);        // bool
$items = $this->config('allowed_items', []);      // array
$timeout = $this->config('timeout', 30);          // int
```

**Key Format:**
- Simple keys: `'max_points'`
- Nested keys: `'api.endpoint.url'`
- Dot notation supported

---

### `setConfig(string $key, mixed $value): void`

Set mod-specific configuration value.

**Example:**
```php
// Set simple value
$this->setConfig('max_points', 15000);

// Set nested value
$this->setConfig('api.endpoint', 'https://api.example.com');
$this->setConfig('api.timeout', 60);

// Set complex value
$this->setConfig('permissions', [
    'view' => true,
    'edit' => false,
    'delete' => false
]);
```

**Notes:**
- Creates namespace if doesn't exist
- Overwrites existing values
- Supports all PHP types

---

## Database Methods

### `getDb(): Database`

Get direct access to database instance.

**Example:**
```php
$db = $this->getDb();

// Direct query
$result = $db->query("SELECT * FROM bb_users WHERE id = ?", [123]);
$user = $result->fetch(\PDO::FETCH_ASSOC);

// Prepared statement
$stmt = $db->prepare("INSERT INTO bb_karma_points (user_id, points) VALUES (?, ?)");
$stmt->execute([$userId, $points]);

// Transaction
$db->beginTransaction();
try {
    $db->query("UPDATE bb_karma_points SET points = points + ? WHERE user_id = ?", [$delta, $userId]);
    $db->query("INSERT INTO bb_karma_history (user_id, delta, reason) VALUES (?, ?, ?)", [$userId, $delta, $reason]);
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
    throw $e;
}
```

---

### `runMigrations(string $migrationsPath): void`

Run SQL migrations from directory.

**Directory Structure:**
```
mods/karma/migrations/
├── 001_create_karma_points_table.sql
├── 002_create_karma_history_table.sql
└── 003_add_karma_constraints.sql
```

**Migration File Format:**
```sql
-- 001_create_karma_points_table.sql
CREATE TABLE bb_karma_points (
    user_id INT UNSIGNED NOT NULL,
    points INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    INDEX idx_points (points)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bb_karma_settings (
    setting_key VARCHAR(255) NOT NULL,
    setting_value TEXT,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Example:**
```php
public function activate(): void
{
    $this->runMigrations('migrations');
}
```

**Behavior:**
- Executes .sql files in alphabetical order
- Splits by semicolons for multiple statements
- Throws `ModException` on SQL errors
- Logs each migration execution

**Error Handling:**
```php
try {
    $this->runMigrations('migrations');
} catch (ModException $e) {
    $this->log('migration_error', $e->getMessage(), $e->getContext());
    throw $e;
}
```

---

### `tableExists(string $tableName): bool`

Check if database table exists.

**Example:**
```php
// Check before dropping
if ($this->tableExists('bb_karma_points')) {
    $this->getDb()->query("DROP TABLE bb_karma_points");
}

// Conditional migration
if (!$this->tableExists('bb_karma_history')) {
    $this->getDb()->query("CREATE TABLE bb_karma_history ...");
}

// Verify migration
$this->runMigrations('migrations');
if (!$this->tableExists('bb_karma_points')) {
    throw new ModException('Migration failed - table not created');
}
```

---

## Cache Methods

Each mod has a dedicated cache namespace to prevent conflicts.

### `getCache(): CacheManager`

Get cache instance for this mod.

**Example:**
```php
$cache = $this->getCache();

// Set cache value
$cache->set('user_karma', $karmaData, 3600); // 1 hour TTL

// Get cache value
$karmaData = $cache->get('user_karma');
if ($karmaData === false) {
    // Cache miss - fetch from database
    $karmaData = $this->fetchKarmaFromDatabase($userId);
    $cache->set('user_karma', $karmaData, 3600);
}

// Remove cache key
$cache->rm('user_karma');

// Clear all mod cache
$cache->clean();
```

---

### `getCacheKey(string $key): string`

Get mod-specific cache key with automatic prefixing.

**Example:**
```php
$key = $this->getCacheKey('user_data');
// Returns: "mod.karma.user_data"

$key = $this->getCacheKey('leaderboard.daily');
// Returns: "mod.karma.leaderboard.daily"
```

**Purpose:** Prevents cache key collisions between mods

---

## Permission Methods

### `registerPermissions(array $permissions): void`

Register mod permissions.

**Example:**
```php
$this->registerPermissions([
    'karma.view' => 'View karma points and rankings',
    'karma.give' => 'Award karma points to users',
    'karma.take' => 'Remove karma points from users',
    'karma.moderate' => 'Access karma moderation tools',
    'karma.admin' => 'Full karma system administration'
]);
```

**Notes:**
- Logs each permission
- TODO: Will integrate with TorrentPier permission system in future

---

## Logging Methods

### `log(string $action, string $message, ?array $details = null): void`

Log mod activity to database.

**Example:**
```php
// Simple log
$this->log('post_created', 'User created a new post');

// Log with details
$this->log('karma_awarded', 'Awarded karma to user', [
    'user_id' => 123,
    'points' => 50,
    'reason' => 'Excellent post'
]);

// Log error
$this->log('api_error', 'External API request failed', [
    'endpoint' => 'https://api.example.com/data',
    'status_code' => 500,
    'response' => $errorResponse
]);
```

**Database Schema:**
```sql
CREATE TABLE bb_mod_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mod_id VARCHAR(255),
    action VARCHAR(50),
    message TEXT,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mod_id (mod_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);
```

**Log Entry Example:**
```json
{
    "mod_id": "karma",
    "action": "karma_awarded",
    "message": "Awarded karma to user",
    "details": "{\"user_id\":123,\"points\":50,\"reason\":\"Excellent post\"}",
    "created_at": "2025-01-23 10:30:00"
}
```

---

## Hook Methods

### `addAction(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void`

Register action hook (WordPress-style).

**Example:**
```php
public function activate(): void
{
    // Hook into post creation
    $this->addAction('post_create', [$this, 'onPostCreate'], 10, 2);

    // Hook into user registration
    $this->addAction('user_register', [$this, 'onUserRegister'], 10, 1);
}

public function onPostCreate(int $postId, array $postData): void
{
    $userId = $postData['user_id'];
    $this->awardKarma($userId, 5, 'Post created');
}

public function onUserRegister(int $userId): void
{
    // Give new users starting karma
    $this->setUserKarma($userId, 100);
}
```

**Parameters:**
- `$hook`: Hook name (e.g., 'post_create', 'user_register')
- `$callback`: Callback function/method
- `$priority`: Execution order (lower = earlier). Default: 10
- `$accepted_args`: Number of arguments passed to callback. Default: 1

---

### `addFilter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void`

Register filter hook (WordPress-style).

**Example:**
```php
public function activate(): void
{
    // Filter user profile data to add karma
    $this->addFilter('user_profile_data', [$this, 'addKarmaToProfile'], 10, 1);

    // Filter post display to add karma info
    $this->addFilter('post_display', [$this, 'addKarmaToPost'], 10, 2);
}

public function addKarmaToProfile(array $profileData): array
{
    $userId = $profileData['user_id'];
    $karma = $this->getUserKarma($userId);

    $profileData['karma'] = $karma;
    $profileData['karma_rank'] = $this->getKarmaRank($userId);

    return $profileData;
}

public function addKarmaToPost(string $postHtml, array $postData): string
{
    $userId = $postData['user_id'];
    $karma = $this->getUserKarma($userId);

    $karmaWidget = "<div class='karma-badge'>{$karma} karma</div>";

    return $postHtml . $karmaWidget;
}
```

**Parameters:**
- `$hook`: Filter name (e.g., 'user_profile_data', 'post_display')
- `$callback`: Callback function/method (must return modified value)
- `$priority`: Execution order (lower = earlier). Default: 10
- `$accepted_args`: Number of arguments. Default: 1

---

## File Methods

### `includeFile(string $file): void`

Include file relative to mod root directory.

**Example:**
```php
// Include helper functions
$this->includeFile('includes/helpers.php');

// Include class file
$this->includeFile('classes/KarmaCalculator.php');

// Include with leading slash (also works)
$this->includeFile('/includes/helpers.php');
```

**Error Handling:**
```php
try {
    $this->includeFile('config/settings.php');
} catch (ModException $e) {
    $this->log('include_error', $e->getMessage());
    // Use default settings
}
```

**Throws:** `ModException` if file not found

---

## State Query Methods

### `getId(): string`

Get mod identifier.

**Example:**
```php
$modId = $this->getId();
// Returns: "karma"
```

---

### `getVersion(): string`

Get mod version.

**Example:**
```php
$version = $this->getVersion();
// Returns: "1.0.0"

// Version comparison
if (version_compare($this->getVersion(), '2.0.0', '>=')) {
    // Use new features
}
```

---

### `getName(): string`

Get mod display name.

**Example:**
```php
$name = $this->getName();
// Returns: "Karma System"

echo "Installing {$name}...";
```

**Fallback:** Returns mod ID if name not in manifest

---

### `getDescription(): string`

Get mod description.

**Example:**
```php
$description = $this->getDescription();
// Returns: "User reputation and karma tracking system"

echo "<p>{$description}</p>";
```

**Fallback:** Returns empty string if not in manifest

---

### `getAuthor(): string`

Get mod author.

**Example:**
```php
$author = $this->getAuthor();
// Returns: "TorrentPier Team"

echo "Created by {$author}";
```

**Fallback:** Returns empty string if not in manifest

---

### `getManifest(): array`

Get full manifest data.

**Example:**
```php
$manifest = $this->getManifest();

// [
//     'id' => 'karma',
//     'name' => 'Karma System',
//     'version' => '1.0.0',
//     'description' => '...',
//     'author' => 'TorrentPier Team',
//     'homepage' => 'https://torrentpier.com/mods/karma',
//     'entrypoint' => 'KarmaMod.php',
//     'requires' => [...],
//     'config' => [...],
//     'permissions' => [...],
//     'hooks' => [...]
// ]

// Access custom fields
$homepage = $manifest['homepage'] ?? '';
$license = $manifest['license'] ?? 'MIT';
```

---

### `getPath(): string`

Get mod directory path.

**Example:**
```php
$path = $this->getPath();
// Returns: "/path/to/torrentpier/mods/karma"

// Build file paths
$configFile = $this->getPath() . '/config/settings.php';
$templateDir = $this->getPath() . '/templates';
```

---

### `isActive(): bool`

Check if mod is currently active in database.

**Example:**
```php
if ($this->isActive()) {
    // Mod is active - run features
    $this->processKarmaUpdates();
} else {
    // Mod is inactive - skip processing
    return;
}
```

**Database Query:**
```sql
SELECT is_active FROM bb_mods WHERE mod_id = ?
```

---

## Advanced Examples

### Complete Karma Mod

```php
<?php

declare(strict_types=1);

use TorrentPier\ModSystem\AbstractMod;
use TorrentPier\ModSystem\ModException;

class KarmaMod extends AbstractMod
{
    // Constants
    private const POINTS_PER_POST = 5;
    private const POINTS_PER_LIKE = 1;
    private const INITIAL_KARMA = 100;

    /**
     * Activate mod
     */
    public function activate(): void
    {
        // Run migrations
        $this->runMigrations('migrations');

        // Register permissions
        $this->registerPermissions([
            'karma.view' => 'View karma points and rankings',
            'karma.give' => 'Award karma points to users',
            'karma.take' => 'Remove karma points from users',
            'karma.moderate' => 'Access karma moderation tools'
        ]);

        // Set default config
        $this->setConfig('initial_karma', self::INITIAL_KARMA);
        $this->setConfig('points_per_post', self::POINTS_PER_POST);
        $this->setConfig('points_per_like', self::POINTS_PER_LIKE);
        $this->setConfig('max_karma', 10000);
        $this->setConfig('min_karma', 0);
        $this->setConfig('leaderboard_enabled', true);
        $this->setConfig('leaderboard_size', 10);

        // Register hooks
        $this->registerHooks();

        $this->log('activate', 'Karma mod activated successfully');
    }

    /**
     * Register event hooks
     */
    private function registerHooks(): void
    {
        // Post events
        $this->addAction('post_create', [$this, 'onPostCreate'], 10, 2);
        $this->addAction('post_like', [$this, 'onPostLike'], 10, 2);

        // User events
        $this->addAction('user_register', [$this, 'onUserRegister'], 10, 1);

        // Profile filters
        $this->addFilter('user_profile_data', [$this, 'addKarmaToProfile'], 10, 1);
    }

    /**
     * Handle post creation
     */
    public function onPostCreate(int $postId, array $postData): void
    {
        $userId = $postData['user_id'];
        $points = $this->config('points_per_post', self::POINTS_PER_POST);

        $this->awardKarma($userId, $points, 'Post created', [
            'post_id' => $postId,
            'post_title' => $postData['title'] ?? ''
        ]);
    }

    /**
     * Handle post like
     */
    public function onPostLike(int $postId, int $userId): void
    {
        $points = $this->config('points_per_like', self::POINTS_PER_LIKE);

        $this->awardKarma($userId, $points, 'Post liked', [
            'post_id' => $postId
        ]);
    }

    /**
     * Handle user registration
     */
    public function onUserRegister(int $userId): void
    {
        $initialKarma = $this->config('initial_karma', self::INITIAL_KARMA);

        $stmt = $this->getDb()->prepare("
            INSERT INTO bb_karma_points (user_id, points)
            VALUES (?, ?)
        ");

        $stmt->execute([$userId, $initialKarma]);

        $this->log('user_registered', 'Set initial karma for new user', [
            'user_id' => $userId,
            'initial_karma' => $initialKarma
        ]);
    }

    /**
     * Add karma to user profile data
     */
    public function addKarmaToProfile(array $profileData): array
    {
        $userId = $profileData['user_id'];

        $profileData['karma'] = $this->getUserKarma($userId);
        $profileData['karma_rank'] = $this->getKarmaRank($userId);

        return $profileData;
    }

    /**
     * Award karma to user
     */
    private function awardKarma(int $userId, int $points, string $reason, array $metadata = []): void
    {
        $db = $this->getDb();

        $db->beginTransaction();

        try {
            // Update points
            $stmt = $db->prepare("
                UPDATE bb_karma_points
                SET points = GREATEST(?, LEAST(points + ?, ?))
                WHERE user_id = ?
            ");

            $minKarma = $this->config('min_karma', 0);
            $maxKarma = $this->config('max_karma', 10000);

            $stmt->execute([$minKarma, $points, $maxKarma, $userId]);

            // Log history
            $stmt = $db->prepare("
                INSERT INTO bb_karma_history (user_id, delta, reason, metadata, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $userId,
                $points,
                $reason,
                json_encode($metadata)
            ]);

            $db->commit();

            // Clear cache
            $cacheKey = $this->getCacheKey("user_{$userId}_karma");
            $this->getCache()->rm($cacheKey);

            $this->log('karma_awarded', $reason, array_merge($metadata, [
                'user_id' => $userId,
                'points' => $points
            ]));

        } catch (\Exception $e) {
            $db->rollBack();

            $this->log('karma_award_error', $e->getMessage(), [
                'user_id' => $userId,
                'points' => $points,
                'reason' => $reason
            ]);
        }
    }

    /**
     * Get user karma with caching
     */
    private function getUserKarma(int $userId): int
    {
        $cacheKey = $this->getCacheKey("user_{$userId}_karma");

        $karma = $this->getCache()->get($cacheKey);

        if ($karma === false) {
            $stmt = $this->getDb()->prepare("
                SELECT points FROM bb_karma_points WHERE user_id = ?
            ");

            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $karma = $result ? $result['points'] : 0;

            $this->getCache()->set($cacheKey, $karma, 3600); // 1 hour
        }

        return (int)$karma;
    }

    /**
     * Get user karma rank
     */
    private function getKarmaRank(int $userId): int
    {
        $stmt = $this->getDb()->prepare("
            SELECT COUNT(*) + 1 as rank
            FROM bb_karma_points
            WHERE points > (SELECT points FROM bb_karma_points WHERE user_id = ?)
        ");

        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? (int)$result['rank'] : 0;
    }

    /**
     * Deactivate mod
     */
    public function deactivate(): void
    {
        $this->getCache()->clean();
        $this->log('deactivate', 'Karma mod deactivated');
    }

    /**
     * Uninstall mod
     */
    public function uninstall(): void
    {
        // Drop tables
        if ($this->tableExists('bb_karma_points')) {
            $this->getDb()->query("DROP TABLE bb_karma_points");
        }

        if ($this->tableExists('bb_karma_history')) {
            $this->getDb()->query("DROP TABLE bb_karma_history");
        }

        // Clear config
        config()->clearModConfig($this->getId());

        $this->log('uninstall', 'Karma mod uninstalled completely');
    }

    /**
     * Upgrade mod
     */
    public function upgrade(string $oldVersion): void
    {
        if (version_compare($oldVersion, '1.1.0', '<')) {
            $this->runMigrations('migrations/v1.1.0');
        }

        $this->log('upgrade', "Upgraded from {$oldVersion} to {$this->getVersion()}");
    }
}
```

---

## Error Handling

### ModException

All mod operations should throw `ModException` on errors:

```php
use TorrentPier\ModSystem\ModException;

try {
    $this->runMigrations('migrations');
} catch (ModException $e) {
    $this->log('error', $e->getMessage(), $e->getContext());
    throw $e; // Re-throw for ModManager to handle
}
```

### Error Codes

```php
// Manifest errors
ModException::MANIFEST_NOT_FOUND
ModException::MANIFEST_INVALID_JSON
ModException::MANIFEST_MISSING_FIELD
ModException::MANIFEST_INVALID_SCHEMA

// Compatibility errors
ModException::COMPATIBILITY_TP_VERSION
ModException::COMPATIBILITY_PHP_VERSION
ModException::COMPATIBILITY_MISSING_DEPENDENCY

// Loading errors
ModException::MOD_NOT_FOUND
ModException::FILE_OPERATION_ERROR

// Database errors
ModException::DATABASE_ERROR
```

---

## Best Practices

### 1. Use Namespaced Configuration
```php
// Good - isolated
$this->config('max_points', 10000);
$this->setConfig('max_points', 15000);

// Bad - global namespace collision
config()->set('max_points', 15000);
```

### 2. Cache Expensive Operations
```php
// Good - with caching
public function getLeaderboard(): array
{
    $cacheKey = $this->getCacheKey('leaderboard');

    $data = $this->getCache()->get($cacheKey);
    if ($data === false) {
        $data = $this->fetchLeaderboardFromDb();
        $this->getCache()->set($cacheKey, $data, 3600);
    }

    return $data;
}

// Bad - no caching
public function getLeaderboard(): array
{
    return $this->fetchLeaderboardFromDb(); // Slow!
}
```

### 3. Log Important Operations
```php
// Good - detailed logging
$this->log('karma_awarded', 'Awarded karma to user', [
    'user_id' => $userId,
    'points' => $points,
    'reason' => $reason,
    'from_action' => $action
]);

// Bad - no logging
// Silent operations are hard to debug
```

### 4. Use Transactions for Multi-Step Operations
```php
// Good - atomic operation
$db = $this->getDb();
$db->beginTransaction();
try {
    $db->query("UPDATE ...");
    $db->query("INSERT ...");
    $db->commit();
} catch (\Exception $e) {
    $db->rollBack();
    throw $e;
}

// Bad - partial updates on error
$db->query("UPDATE ...");
$db->query("INSERT ..."); // Fails, but UPDATE already applied!
```

### 5. Make Lifecycle Hooks Idempotent
```php
// Good - safe to run multiple times
public function activate(): void
{
    if (!$this->tableExists('bb_karma_points')) {
        $this->runMigrations('migrations');
    }

    if (!$this->config('initial_karma')) {
        $this->setConfig('initial_karma', 100);
    }
}

// Bad - creates duplicates on re-activation
public function activate(): void
{
    $this->runMigrations('migrations'); // Fails if tables exist!
    $this->setConfig('initial_karma', 100); // Overwrites user changes!
}
```

---

## Testing

### Unit Test Example

```php
<?php

namespace Tests\Mods;

use PHPUnit\Framework\TestCase;
use KarmaMod;

class KarmaModTest extends TestCase
{
    private KarmaMod $mod;
    private string $tempPath;

    protected function setUp(): void
    {
        $this->tempPath = sys_get_temp_dir() . '/karma_test_' . uniqid();
        mkdir($this->tempPath);

        $manifest = [
            'id' => 'karma',
            'name' => 'Karma System',
            'version' => '1.0.0',
            'entrypoint' => 'KarmaMod.php'
        ];

        $this->mod = new KarmaMod($manifest, $this->tempPath);
    }

    public function test_get_id_returns_karma(): void
    {
        expect($this->mod->getId())->toBe('karma');
    }

    public function test_get_version_returns_correct_version(): void
    {
        expect($this->mod->getVersion())->toBe('1.0.0');
    }

    public function test_activate_creates_tables(): void
    {
        $this->mod->activate();

        expect($this->mod->tableExists('bb_karma_points'))->toBeTrue();
    }
}
```

---

## See Also

- [MODLOADER-API.md](MODLOADER-API.md) - ModLoader API reference
- [MOD-SYSTEM.md](MOD-SYSTEM.md) - Complete mod system architecture
- [manifest.json Schema](../docs/schemas/manifest.json) - Manifest validation schema

---

**Last Updated:** 2025-01-23
**Maintainer:** TorrentPier Development Team
