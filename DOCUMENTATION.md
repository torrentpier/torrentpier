# ⚙️ TorrentPier Configuration System

TorrentPier features a modern, centralized configuration system using the `Config` class with full backward compatibility. The new system provides better IDE support, type safety, and dot notation for nested configurations.

## 📖 Table of Contents

- [Basic Usage](#basic-usage)
- [Advanced Usage](#advanced-usage)
- [Migration Guide](#migration-guide)
- [Configuration Reference](#configuration-reference)
- [Magic Methods](#magic-methods)
- [Type Safety & IDE Support](#type-safety--ide-support)
- [Thread Safety](#thread-safety)
- [Best Practices](#best-practices)

## 🚀 Basic Usage

### Getting Configuration Values

```php
// Get configuration values using dot notation
$siteName = config()->get('sitename');
$dbHost = config()->get('database.host');
$cacheTimeout = config()->get('cache.timeout');

// Get with default value if key doesn't exist
$maxUsers = config()->get('max_users_online', 100);
$debugMode = config()->get('debug.enabled', false);
```

### Setting Configuration Values

```php
// Set configuration values
config()->set('sitename', 'My Awesome Tracker');
config()->set('database.port', 3306);
config()->set('cache.enabled', true);

// Set nested configuration
config()->set('torr_server.enabled', true);
config()->set('torr_server.url', 'http://localhost:8090');
```

### Checking Configuration Existence

```php
// Check if configuration exists
if (config()->has('bt_announce_url')) {
    $announceUrl = config()->get('bt_announce_url');
}

// Check nested configuration
if (config()->has('tracker.retracker_host')) {
    $retrackerHost = config()->get('tracker.retracker_host');
}
```

## 🔧 Advanced Usage

### Working with Configuration Sections

```php
// Get entire configuration section
$dbConfig = config()->getSection('database');
// Returns: ['host' => 'localhost', 'port' => 3306, 'name' => 'torrentpier']

$trackerConfig = config()->getSection('tracker');
// Returns all tracker-related settings

// Get all configuration
$allConfig = config()->all();
```

### Merging Configuration Arrays

```php
// Merge configuration arrays (useful for extending existing config)
config()->merge('tor_icons', [
    10 => '🔥',  // Hot torrent
    11 => '⭐',  // Featured torrent
    12 => '💎'   // Premium torrent
]);

// Merge tracker settings
config()->merge('tracker', [
    'new_feature_enabled' => true,
    'custom_announce_interval' => 1800
]);
```

### Using Global Helper Function

```php
// Alternative syntax using global helper
$serverName = config('server_name');
$dbTimeout = config('database.timeout', 30);

// These are equivalent:
config()->get('sitename');
config('sitename');
```

## 🔄 Migration Guide

The system maintains **full backward compatibility** while providing modern access patterns:

### Old vs New Syntax Examples

```php
// ❌ Old way (still works, but not recommended)
global $bb_cfg;
$announceUrl = $bb_cfg['bt_announce_url'];
$dbHost = $bb_cfg['database']['host'];
$trackerEnabled = $bb_cfg['tracker']['enabled'];
$atomPath = $bb_cfg['atom']['path'];

// ✅ New way (recommended)
$announceUrl = config()->get('bt_announce_url');
$dbHost = config()->get('database.host');
$trackerEnabled = config()->get('tracker.enabled');
$atomPath = config()->get('atom.path');
```

### Migration Steps

1. **Replace global $bb_cfg declarations** with config() calls
2. **Convert array access** to dot notation
3. **Add null checks** where appropriate
4. **Use type hints** for better IDE support

```php
// Before migration
function getDbConnection() {
    global $bb_cfg;
    $host = $bb_cfg['database']['host'];
    $port = $bb_cfg['database']['port'] ?? 3306;
    // ...
}

// After migration
function getDbConnection() {
    $host = config()->get('database.host');
    $port = config()->get('database.port', 3306);
    // ...
}
```

## 📚 Configuration Reference

### Site Settings
```php
config()->get('sitename')                    // $bb_cfg['sitename']
config()->get('site_desc')                   // $bb_cfg['site_desc']
config()->get('server_name')                 // $bb_cfg['server_name']
config()->get('server_port')                 // $bb_cfg['server_port']
config()->get('default_lang')                // $bb_cfg['default_lang']
config()->get('board_timezone')              // $bb_cfg['board_timezone']
```

### Database Settings
```php
config()->get('database.host')               // $bb_cfg['database']['host']
config()->get('database.port')               // $bb_cfg['database']['port']
config()->get('database.name')               // $bb_cfg['database']['name']
config()->get('database.user')               // $bb_cfg['database']['user']
config()->get('database.password')           // $bb_cfg['database']['password']
```

### BitTorrent Settings
```php
config()->get('bt_announce_url')             // $bb_cfg['bt_announce_url']
config()->get('bt_disable_dht')              // $bb_cfg['bt_disable_dht']
config()->get('bt_check_announce_url')       // $bb_cfg['bt_check_announce_url']
config()->get('bt_add_auth_key')             // $bb_cfg['bt_add_auth_key']
config()->get('bt_replace_ann_url')          // $bb_cfg['bt_replace_ann_url']
config()->get('bt_del_addit_ann_urls')       // $bb_cfg['bt_del_addit_ann_urls']
config()->get('bt_set_dltype_on_tor_reg')    // $bb_cfg['bt_set_dltype_on_tor_reg']
config()->get('bt_unset_dltype_on_tor_unreg') // $bb_cfg['bt_unset_dltype_on_tor_unreg']
config()->get('bt_min_ratio_allow_dl_tor')   // $bb_cfg['bt_min_ratio_allow_dl_tor']
config()->get('bt_newtopic_auto_reg')        // $bb_cfg['bt_newtopic_auto_reg']
```

### Tracker Settings
```php
config()->get('tracker.disabled_v1_torrents') // $bb_cfg['tracker']['disabled_v1_torrents']
config()->get('tracker.disabled_v2_torrents') // $bb_cfg['tracker']['disabled_v2_torrents']
config()->get('tracker.retracker_host')       // $bb_cfg['tracker']['retracker_host']
config()->get('tracker.retracker')            // $bb_cfg['tracker']['retracker']
config()->get('tracker.tor_topic_up')         // $bb_cfg['tracker']['tor_topic_up']
config()->get('tracker.use_old_torrent_name_format') // $bb_cfg['tracker']['use_old_torrent_name_format']
```

### TorrServer Integration
```php
config()->get('torr_server.enabled')         // $bb_cfg['torr_server']['enabled']
config()->get('torr_server.url')             // $bb_cfg['torr_server']['url']
config()->get('torr_server.timeout')         // $bb_cfg['torr_server']['timeout']
config()->get('torr_server.disable_for_guest') // $bb_cfg['torr_server']['disable_for_guest']
```

### Security & Authentication
```php
config()->get('captcha.disabled')            // $bb_cfg['captcha']['disabled']
config()->get('passkey_key')                 // $bb_cfg['passkey_key']
config()->get('password_symbols.nums')       // $bb_cfg['password_symbols']['nums']
config()->get('password_symbols.chars')      // $bb_cfg['password_symbols']['chars']
config()->get('password_hash_options.algo')  // $bb_cfg['password_hash_options']['algo']
config()->get('password_hash_options.options') // $bb_cfg['password_hash_options']['options']
config()->get('allow_autologin')             // $bb_cfg['allow_autologin']
config()->get('max_autologin_time')          // $bb_cfg['max_autologin_time']
config()->get('session_update_intrv')        // $bb_cfg['session_update_intrv']
config()->get('invalid_logins')              // $bb_cfg['invalid_logins']
config()->get('first_logon_redirect_url')    // $bb_cfg['first_logon_redirect_url']
```

### Cache Settings
```php
config()->get('cache.prefix')                // $bb_cfg['cache']['prefix']
config()->get('cache.timeout')               // $bb_cfg['cache']['timeout']
config()->get('cache.enabled')               // $bb_cfg['cache']['enabled']
```

### Forum Settings
```php
config()->get('topics_per_page')             // $bb_cfg['topics_per_page']
config()->get('posts_per_page')              // $bb_cfg['posts_per_page']
config()->get('allowed_topics_per_page')     // $bb_cfg['allowed_topics_per_page']
config()->get('hot_threshold')               // $bb_cfg['hot_threshold']
config()->get('show_dl_status_in_forum')     // $bb_cfg['show_dl_status_in_forum']
config()->get('sf_on_first_page_only')       // $bb_cfg['sf_on_first_page_only']
config()->get('last_post_date_format')       // $bb_cfg['last_post_date_format']
config()->get('post_date_format')            // $bb_cfg['post_date_format']
config()->get('group_members_per_page')      // $bb_cfg['group_members_per_page']
config()->get('flist_timeout')               // $bb_cfg['flist_timeout']
config()->get('flist_max_files')             // $bb_cfg['flist_max_files']
```

### News & Feeds
```php
config()->get('show_latest_news')            // $bb_cfg['show_latest_news']
config()->get('latest_news_forum_id')        // $bb_cfg['latest_news_forum_id']
config()->get('show_network_news')           // $bb_cfg['show_network_news']
config()->get('network_news_forum_id')       // $bb_cfg['network_news_forum_id']
config()->get('atom.path')                   // $bb_cfg['atom']['path']
config()->get('atom.url')                    // $bb_cfg['atom']['url']
config()->get('atom.direct_down')            // $bb_cfg['atom']['direct_down']
config()->get('atom.direct_view')            // $bb_cfg['atom']['direct_view']
```

### Torrent Status & Icons
```php
config()->get('tor_icons')                   // $bb_cfg['tor_icons']
config()->get('tor_frozen')                  // $bb_cfg['tor_frozen']
config()->get('tor_cannot_new')              // $bb_cfg['tor_cannot_new']
config()->get('tor_cannot_edit')             // $bb_cfg['tor_cannot_edit']
```

### Poll Settings
```php
config()->get('max_poll_options')            // $bb_cfg['max_poll_options']
config()->get('poll_max_days')               // $bb_cfg['poll_max_days']
```

### Debug & Development
```php
config()->get('xs_use_cache')                // $bb_cfg['xs_use_cache']
config()->get('auto_language_detection')     // $bb_cfg['auto_language_detection']
config()->get('dbg_users')                   // $bb_cfg['dbg_users']
config()->get('super_admins')                // $bb_cfg['super_admins']
config()->get('torhelp_enabled')             // $bb_cfg['torhelp_enabled']
config()->get('premod')                      // $bb_cfg['premod']
```

### Email Settings
```php
config()->get('emailer.enabled')             // $bb_cfg['emailer']['enabled']
config()->get('emailer.smtp.host')           // $bb_cfg['emailer']['smtp']['host']
config()->get('emailer.smtp.port')           // $bb_cfg['emailer']['smtp']['port']
config()->get('group_send_email')            // $bb_cfg['group_send_email']
```

### Avatar & Upload Settings
```php
config()->get('group_avatars.up_allowed')    // $bb_cfg['group_avatars']['up_allowed']
config()->get('group_avatars.max_size')      // $bb_cfg['group_avatars']['max_size']
```

## 🪄 Magic Methods

The Config class supports magic methods for convenient access:

### Magic Getter
```php
// Using magic getter (equivalent to get())
$siteName = config()->sitename;
$serverName = config()->server_name;

// For nested keys, use curly braces
$dbHost = config()->{'database.host'};
$atomPath = config()->{'atom.path'};
```

### Magic Setter
```php
// Using magic setter (equivalent to set())
config()->sitename = 'New Site Name';
config()->server_port = 8080;

// For nested keys
config()->{'database.port'} = 3306;
config()->{'torr_server.enabled'} = true;
```

### Magic Isset
```php
// Using magic isset (equivalent to has())
if (isset(config()->bt_announce_url)) {
    // Configuration exists
}

if (isset(config()->{'tracker.retracker_host'})) {
    // Nested configuration exists
}
```

## 🔍 Type Safety & IDE Support

The Config class provides better type safety and IDE autocomplete support:

```php
// IDE will provide autocomplete and type hints
$config = config();
$siteName = $config->get('sitename');         // Returns string|null
$isEnabled = $config->has('feature_enabled');  // Returns bool
$allSettings = $config->all();                 // Returns array
$section = $config->getSection('database');   // Returns array|null
```

### Type Declarations
```php
// You can add type hints for better code documentation
function getDatabaseConfig(): ?array {
    return config()->getSection('database');
}

function getSiteName(): string {
    return config()->get('sitename', 'TorrentPier');
}

function isFeatureEnabled(string $feature): bool {
    return config()->get($feature, false);
}
```

## 🧵 Thread Safety

The Config class is implemented as a thread-safe singleton, ensuring consistent configuration access across your application:

```php
// These will return the same instance
$config1 = config();
$config2 = \TorrentPier\Config::getInstance();
var_dump($config1 === $config2); // true

// Configuration is shared across all instances
config()->set('test_value', 'hello');
echo \TorrentPier\Config::getInstance()->get('test_value'); // "hello"
```

## 📋 Best Practices

### 1. Use Dot Notation for Nested Config
```php
// ✅ Recommended
$host = config()->get('database.host');
$enabled = config()->get('torr_server.enabled');

// ❌ Avoid (though it works)
$dbConfig = config()->get('database');
$host = $dbConfig['host'];
```

### 2. Always Provide Defaults for Optional Settings
```php
// ✅ Good - provides sensible defaults
$timeout = config()->get('api.timeout', 30);
$maxRetries = config()->get('api.max_retries', 3);
$debugMode = config()->get('debug.enabled', false);

// ❌ Avoid - might return null unexpectedly
$timeout = config()->get('api.timeout');
```

### 3. Use Type Hints and Validation
```php
// ✅ Good - validates and converts types
function getMaxUploadSize(): int {
    $size = config()->get('upload.max_size', 10485760); // 10MB default
    return (int) $size;
}

function isMaintenanceMode(): bool {
    return (bool) config()->get('maintenance.enabled', false);
}
```

### 4. Cache Frequently Used Config Values
```php
// ✅ Good for performance-critical code
class TrackerService {
    private string $announceUrl;
    private int $announceInterval;
    
    public function __construct() {
        $this->announceUrl = config()->get('bt_announce_url');
        $this->announceInterval = config()->get('bt_announce_interval', 1800);
    }
}
```

### 5. Use Sections for Related Configuration
```php
// ✅ Good - organize related settings
function setupEmailer(): void {
    $emailConfig = config()->getSection('emailer');
    
    if ($emailConfig['enabled'] ?? false) {
        // Configure SMTP with $emailConfig['smtp']
    }
}
```

### 6. Validate Critical Configuration at Startup
```php
// ✅ Good - validate required configuration early
function validateConfig(): void {
    $required = [
        'database.host',
        'database.name',
        'bt_announce_url',
        'server_name'
    ];
    
    foreach ($required as $key) {
        if (!config()->has($key)) {
            throw new ConfigurationException("Required configuration missing: {$key}");
        }
    }
}
```

## 🚨 Migration Checklist

When migrating from `$bb_cfg` to the Config class:

- [ ] Replace `global $bb_cfg;` with `config()` calls
- [ ] Convert array syntax `$bb_cfg['key']['nested']` to dot notation `config()->get('key.nested')`
- [ ] Add default values where appropriate
- [ ] Add type hints to function returns
- [ ] Test all configuration-dependent functionality
- [ ] Update documentation and comments
- [ ] Consider caching frequently accessed values

## 📞 Support

If you encounter issues with the configuration system:

1. Join our [Official Support Forum](https://torrentpier.com)
2. Search existing [GitHub Issues](https://github.com/torrentpier/torrentpier/issues)

---

**Note**: The old `$bb_cfg` global array system continues to work for backward compatibility, but using the new Config class is recommended for all new code and when refactoring existing code. 