---
sidebar_position: 15
title: Breaking Changes
---

# Breaking Changes & Best Practices

This page summarizes all breaking changes and provides best practices for upgrading to TorrentPier 3.0.

## Breaking Changes

### Database Layer Changes

- **No Breaking Changes**: All existing `DB()->method()` calls work exactly as before
- **Removed Files**: `src/Legacy/SqlDb.php` and `src/Legacy/Dbs.php` (replaced by modern implementation)
- **New Implementation**: Uses Nette Database v3.2 internally with full backward compatibility

### Deprecated Functions

| Old | New |
|-----|-----|
| `get_config()` | `config()->get()` |
| `set_config()` | `config()->set()` |
| Direct `$bb_cfg` access | `config()` methods |

### Deprecated Patterns

| Old Pattern | New Pattern |
|-------------|-------------|
| `new TorrentPier\Censor()` | `censor()` global function |
| Direct `$wordCensor` access | `censor()` methods |
| `new TorrentPier\Dev()` | `dev()` global function |
| Static `Dev::` methods | `dev()` instance methods |
| `\TorrentPier\Legacy\Select::` | `\TorrentPier\Legacy\Common\Select::` |
| `\TorrentPier\Helpers\IsHelper::` | `\TorrentPier\Helpers\HttpHelper::` |
| Direct `$_GET`/`$_POST`/`$_REQUEST` access | `request()` methods |
| Direct `$_COOKIE`/`$_SERVER`/`$_FILES` access | `request()` properties |

### File Structure Changes

- New `/src/Database/` directory for modern database classes
- New `/src/` directory for modern PHP classes
- Reorganized template structure

### Template Changes

- Updated template syntax in some areas
- New template variables available
- Deprecated template functions

### Removed Files

**Root Level:**
- `common.php` - Legacy initialization
- `_cleanup.php`, `_release.php` - Old admin scripts
- `.htaccess` - Replaced by web server config

**Admin Interface:**
- `admin/admin_attach_cp.php`
- `admin/admin_attachments.php`
- `admin/admin_extensions.php`
- `admin/pagestart.php`

**BitTorrent Tracker (Legacy):**
- `bt/announce.php`, `bt/scrape.php` (replaced by PSR-7 controllers)
- `bt/includes/init_tr.php`, `bt/index.php`

**Legacy Web:**
- `ajax.php` - Replaced by PSR-7 AJAX controller
- `dl.php` - Download handling refactored
- `install.php` - Replaced by `php bull app:install`

### Removed Constants

- `IN_DEMO_MODE` - Use `app()->isDebug()` or environment checks

### Removed Functions

- `utime()` - Use `microtime(true)`
- `str_compact()` - Use `Illuminate\Support\Str::squish()`
- `make_rand_str()` - Use `Illuminate\Support\Str::random()`

## Best Practices

### Configuration Management

```php
// Always provide defaults
$timeout = config()->get('api.timeout', 30);

// Use type hints
function getMaxUploadSize(): int {
    return (int) config()->get('upload.max_size', 10485760);
}

// Cache frequently used values
class TrackerService {
    private string $announceUrl;

    public function __construct() {
        $this->announceUrl = config()->get('bt_announce_url');
    }
}
```

### Censor Management

```php
// Check if censoring is enabled before processing
function processUserInput(string $text): string {
    if (censor()->isEnabled()) {
        return censor()->censorString($text);
    }
    return $text;
}

// Use the censor() helper consistently
$censoredText = censor()->censorString($input);
```

### HttpHelper Usage

```php
use TorrentPier\Helpers\HttpHelper;

// Always use HttpHelper to detect protocol
if (HttpHelper::isHTTPS()) {
    // Handle HTTPS-specific logic
}

// Prefer HttpHelper over deprecated IsHelper
function getBaseUrl(): string {
    $protocol = HttpHelper::isHTTPS() ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'];
}
```

### Error Handling

```php
// Graceful error handling
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
// Minimize configuration calls in loops
$cacheEnabled = config()->get('cache.enabled', false);
for ($i = 0; $i < 1000; $i++) {
    if ($cacheEnabled) {
        // Use cached value
    }
}
```

### Security Considerations

```php
// Validate configuration values
$maxFileSize = min(
    config()->get('upload.max_size', 1048576),
    1048576 * 100 // Hard limit: 100MB
);

// Sanitize user-configurable values
$siteName = htmlspecialchars(config()->get('sitename', 'TorrentPier'));
```

### Testing

```bash
# Run tests before deploying changes
./vendor/bin/pest

# Validate test coverage for new components
./vendor/bin/pest --coverage
```

For comprehensive testing documentation, see [tests/README.md](https://github.com/torrentpier/torrentpier/blob/master/tests/README.md).

## Migration Checklist

Before upgrading to TorrentPier 3.0:

- [ ] Backup your database
- [ ] Backup your files
- [ ] Test upgrade in staging environment
- [ ] Update any custom code using deprecated patterns
- [ ] Review web server configuration
- [ ] Update any third-party integrations
- [ ] Run migrations after upgrade
- [ ] Clear all caches
- [ ] Verify application functionality

:::warning Production Upgrades
Always test upgrades in a staging environment before applying to production. Keep backups of your database and files until you're confident the upgrade was successful.
:::

## Support

For additional support:
- [Official Forum](https://torrentpier.com)
- [GitHub Repository](https://github.com/torrentpier/torrentpier)
- [Documentation](https://docs.torrentpier.com)
