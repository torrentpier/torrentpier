# 🚀 TorrentPier Upgrade Guide

This guide helps you upgrade your TorrentPier installation to the latest version, covering breaking changes, new features, and migration strategies.

## 📖 Table of Contents

- [Configuration System Migration](#configuration-system-migration)
- [Censor System Migration](#censor-system-migration)
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

## 🛡️ Censor System Migration

The word censoring system has been refactored to use a singleton pattern, similar to the Configuration system, providing better performance and consistency.

### Quick Migration Overview

```php
// ❌ Old way (still works, but not recommended)
global $wordCensor;
$censored = $wordCensor->censorString($text);

// ✅ New way (recommended)
$censored = censor()->censorString($text);
```

### Key Censor Changes

#### Basic Usage
```php
// Censor a string
$text = "This contains badword content";
$censored = censor()->censorString($text);

// Check if censoring is enabled
if (censor()->isEnabled()) {
    $censored = censor()->censorString($text);
} else {
    $censored = $text;
}

// Get count of loaded censored words
$wordCount = censor()->getWordsCount();
```

#### Advanced Usage
```php
// Add runtime censored words (temporary, not saved to database)
censor()->addWord('badword', '***');
censor()->addWord('anotherbad*', 'replaced'); // Wildcards supported

// Reload censored words from database (useful after admin updates)
censor()->reload();

// Check if censoring is enabled
$isEnabled = censor()->isEnabled();
```

### Backward Compatibility

The global `$wordCensor` variable is still available and works exactly as before:

```php
// This still works - backward compatibility maintained
global $wordCensor;
$censored = $wordCensor->censorString($text);

// But this is now preferred
$censored = censor()->censorString($text);
```

### Performance Benefits

- **Single Instance**: Only one censor instance loads words from database
- **Automatic Reloading**: Words are automatically reloaded when updated in admin panel
- **Memory Efficient**: Shared instance across entire application
- **Lazy Loading**: Words only loaded when censoring is enabled

### Admin Panel Updates

When you update censored words in the admin panel, the system now automatically:
1. Updates the datastore cache
2. Reloads the singleton instance with fresh words
3. Applies changes immediately without requiring page refresh

## ⚠️ Breaking Changes

### Deprecated Functions
- `get_config()` → Use `config()->get()`
- `set_config()` → Use `config()->set()`
- Direct `$bb_cfg` access → Use `config()` methods

### Deprecated Patterns
- `new TorrentPier\Censor()` → Use `censor()` global function
- Direct `$wordCensor` access → Use `censor()` methods

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

### Censor Management
```php
// ✅ Check if censoring is enabled before processing
function processUserInput(string $text): string {
    if (censor()->isEnabled()) {
        return censor()->censorString($text);
    }
    return $text;
}

// ✅ Use the singleton consistently
class ForumPost {
    public function getDisplayText(): string {
        return censor()->censorString($this->text);
    }
}

// ✅ Add runtime words when needed
function setupCustomCensoring(): void {
    if (isCustomModeEnabled()) {
        censor()->addWord('custombad*', '[censored]');
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
