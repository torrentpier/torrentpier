# 🚀 TorrentPier Upgrade Guide

This guide helps you upgrade your TorrentPier installation to the latest version, covering breaking changes, new features, and migration strategies.

## 📖 Table of Contents

- [Configuration System Migration](#configuration-system-migration)
- [Breaking Changes](#breaking-changes)
- [Best Practices](#best-practices)

## ⚙️ Configuration System Migration

The new TorrentPier features a modern, centralized configuration system with full backward compatibility.

### Quick Migration Overview

```php
// ❌ Old way (still works, but not recommended)
global $bb_cfg;
$announceUrl = $bb_cfg['bt_announce_url'];
$dbHost = $bb_cfg['database']['host'];

// ✅ New way (recommended)
$announceUrl = config()->get('bt_announce_url');
$dbHost = config()->get('database.host');
```

### Key Configuration Changes

#### Basic Usage
```php
// Get configuration values using dot notation
$siteName = config()->get('sitename');
$dbHost = config()->get('database.host');
$cacheTimeout = config()->get('cache.timeout');

// Get with default value if key doesn't exist
$maxUsers = config()->get('max_users_online', 100);
$debugMode = config()->get('debug.enabled', false);
```

#### Setting Values
```php
// Set configuration values
config()->set('sitename', 'My Awesome Tracker');
config()->set('database.port', 3306);
config()->set('cache.enabled', true);
```

#### Working with Sections
```php
// Get entire configuration section
$dbConfig = config()->getSection('database');
$trackerConfig = config()->getSection('tracker');

// Check if configuration exists
if (config()->has('bt_announce_url')) {
    $announceUrl = config()->get('bt_announce_url');
}
```

### Common Configuration Mappings

| Old Syntax | New Syntax |
|------------|------------|
| `$bb_cfg['sitename']` | `config()->get('sitename')` |
| `$bb_cfg['database']['host']` | `config()->get('database.host')` |
| `$bb_cfg['tracker']['enabled']` | `config()->get('tracker.enabled')` |
| `$bb_cfg['cache']['timeout']` | `config()->get('cache.timeout')` |
| `$bb_cfg['torr_server']['url']` | `config()->get('torr_server.url')` |

### Magic Methods Support
```php
// Magic getter
$siteName = config()->sitename;
$dbHost = config()->{'database.host'};

// Magic setter
config()->sitename = 'New Site Name';
config()->{'database.port'} = 3306;

// Magic isset
if (isset(config()->bt_announce_url)) {
    // Configuration exists
}
```

## ⚠️ Breaking Changes

### Deprecated Functions
- `get_config()` → Use `config()->get()`
- `set_config()` → Use `config()->set()`
- Direct `$bb_cfg` access → Use `config()` methods

### File Structure Changes
- New `/src/` directory for modern PHP classes
- Reorganized template structure

### Template Changes
- Updated template syntax in some areas
- New template variables available
- Deprecated template functions

## 📋 Best Practices

### Configuration Management
```php
// ✅ Always provide defaults
$timeout = config()->get('api.timeout', 30);

// ✅ Use type hints
function getMaxUploadSize(): int {
    return (int) config()->get('upload.max_size', 10485760);
}

// ✅ Cache frequently used values
class TrackerService {
    private string $announceUrl;

    public function __construct() {
        $this->announceUrl = config()->get('bt_announce_url');
    }
}
```

### Error Handling
```php
// ✅ Graceful error handling
try {
    $dbConfig = config()->getSection('database');
    // Database operations
} catch (Exception $e) {
    error_log("Database configuration error: " . $e->getMessage());
    // Fallback behavior
}
```

### Performance Optimization
```php
// ✅ Minimize configuration calls in loops
$cacheEnabled = config()->get('cache.enabled', false);
for ($i = 0; $i < 1000; $i++) {
    if ($cacheEnabled) {
        // Use cached value
    }
}
```

### Security Considerations
```php
// ✅ Validate configuration values
$maxFileSize = min(
    config()->get('upload.max_size', 1048576),
    1048576 * 100 // Hard limit: 100MB
);

// ✅ Sanitize user-configurable values
$siteName = htmlspecialchars(config()->get('sitename', 'TorrentPier'));
```

---

**Important**: Always test the upgrade process in a staging environment before applying it to production. Keep backups of your database and files until you're confident the upgrade was successful.

For additional support, visit our [Official Forum](https://torrentpier.com) or check our [GitHub Repository](https://github.com/torrentpier/torrentpier) for the latest updates and community discussions.
