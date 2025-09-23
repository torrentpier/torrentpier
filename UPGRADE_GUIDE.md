# 🚀 TorrentPier Upgrade Guide

This guide helps you upgrade your TorrentPier installation to the latest version, covering breaking changes, new features, and migration strategies.

## 📖 Table of Contents

- [Database Migration System](#database-migration-system)
- [Database Layer Migration](#database-layer-migration)
- [Unified Cache System Migration](#unified-cache-system-migration)
- [Configuration System Migration](#configuration-system-migration)
- [Language System Migration](#language-system-migration)
- [Censor System Migration](#censor-system-migration)
- [Select System Migration](#select-system-migration)
- [Development System Migration](#development-system-migration)
- [Breaking Changes](#breaking-changes)
- [Best Practices](#best-practices)

## 🗄️ Database Migration System

TorrentPier now includes a modern database migration system using **Phinx** (from CakePHP), replacing the legacy direct SQL import approach. This provides version-controlled database schema management with rollback capabilities.

### Key Benefits

- **Version Control**: Database schema changes are tracked in code
- **Environment Consistency**: Same database structure across development, staging, and production
- **Safe Rollbacks**: Ability to safely revert schema changes
- **Team Collaboration**: No more merge conflicts on database changes
- **Automated Deployments**: Database updates as part of deployment process

### Migration Architecture

#### Engine Strategy
- **InnoDB**: Used for all tables for maximum data integrity and reliability
- **ACID Compliance**: Full transaction support and crash recovery for all data
- **Row-Level Locking**: Better concurrency for high-traffic operations

#### Directory Structure
```
/migrations/
  ├── 20250619000001_initial_schema.php    # Complete database schema
  ├── 20250619000002_seed_initial_data.php # Essential data seeding
  └── future_migrations...                 # Your custom migrations
/scripts/phinx.php                         # Migration configuration
```

### For New Installations

New installations automatically use migrations instead of the legacy SQL dump:

```bash
# Fresh installation now uses migrations
php install.php
```

The installer will:
1. Set up environment configuration
2. Create the database
3. Run all migrations automatically
4. Seed initial data (admin user, configuration, etc.)

### For Existing Installations

Existing installations continue to work without changes. The migration system is designed for new installations and development workflows.

**Important**: Existing installations should **not** attempt to migrate to the new system without proper backup and testing procedures.

### Developer Workflow

#### Creating Migrations
```bash
# Create a new migration
php vendor/bin/phinx create AddNewFeatureTable

# Edit the generated migration file
# /migrations/YYYYMMDDHHMMSS_add_new_feature_table.php
```

#### Running Migrations
```bash
# Run all pending migrations
php vendor/bin/phinx migrate

# Check migration status
php vendor/bin/phinx status

# Rollback last migration
php vendor/bin/phinx rollback
```

#### Migration Template
```php
<?php
use Phinx\Migration\AbstractMigration;

class AddNewFeatureTable extends AbstractMigration
{
    public function change()
    {
        // InnoDB for data integrity
        $table = $this->table('bb_new_feature', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $table->addColumn('name', 'string', ['limit' => 100])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex('name')
              ->create();
    }

    // Optional: explicit up/down methods for complex operations
    public function up()
    {
        // Complex data migration logic
    }

    public function down()
    {
        // Rollback logic
    }
}
```

#### Engine Guidelines
```php
// Use InnoDB for all tables for maximum reliability
$table = $this->table('bb_user_posts', [
    'engine' => 'InnoDB',
    'collation' => 'utf8mb4_unicode_ci'
]);

// All tracker tables also use InnoDB for data integrity
$table = $this->table('bb_bt_peer_stats', [
    'engine' => 'InnoDB',
    'collation' => 'utf8mb4_unicode_ci'
]);

// Buffer tables use InnoDB for consistency and reliability
public function up() {
    $this->execute('DROP TABLE IF EXISTS buf_temp_data');
    // Recreate with new structure using InnoDB
}
```

### Admin Panel Integration

The admin panel includes a read-only migration status page at `/admin/admin_migrations.php`:

- **Current migration version**
- **Applied migrations history**
- **Pending migrations list**
- **Database statistics**
- **Clear instructions for CLI operations**

**Important**: The admin panel is **read-only** for security. All migration operations must be performed via CLI.

### Complex Migration Handling

For complex data transformations, create external scripts:

```php
// migrations/YYYYMMDDHHMMSS_complex_data_migration.php
class ComplexDataMigration extends AbstractMigration
{
    public function up()
    {
        $this->output->writeln('Running complex data migration...');

        // Call external script for complex operations
        $result = shell_exec('php ' . __DIR__ . '/../scripts/migrate_torrent_data.php');
        $this->output->writeln($result);

        if (strpos($result, 'ERROR') !== false) {
            throw new Exception('Complex migration failed');
        }
    }
}
```

### Best Practices

#### Migration Development
```bash
# 1. Create migration
php vendor/bin/phinx create MyFeature

# 2. Edit migration file
# 3. Test locally
php vendor/bin/phinx migrate -e development

# 4. Test rollback
php vendor/bin/phinx rollback -e development

# 5. Commit to version control
git add migrations/
git commit -m "Add MyFeature migration"
```

#### Production Deployment
```bash
# Always backup database first
mysqldump tracker_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Run migrations
php vendor/bin/phinx migrate -e production

# Verify application functionality
# Monitor error logs
```

#### Team Collaboration
- **Never modify existing migrations** that have been deployed
- **Always create new migrations** for schema changes
- **Test migrations on production-like data** before deployment
- **Coordinate with team** before major schema changes

### Configuration

The migration system uses your existing `.env` configuration:

```php
// scripts/phinx.php automatically reads from .env
'production' => [
    'adapter' => 'mysql',
    'host' => env('DB_HOST', 'localhost'),
    'port' => (int) env('DB_PORT', 3306),
    'name' => env('DB_DATABASE'),
    'user' => env('DB_USERNAME'),
    'pass' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
]
```

### Troubleshooting

#### Common Issues
```bash
# Migration table doesn't exist
php vendor/bin/phinx init  # Re-run if needed

# Migration fails mid-way
php vendor/bin/phinx rollback  # Rollback to previous state

# Check what would be applied
php vendor/bin/phinx status  # See pending migrations
```

#### Migration Recovery
```bash
# If migration fails, check status first
php vendor/bin/phinx status

# Rollback to known good state
php vendor/bin/phinx rollback -t 20250619000002

# Fix the migration code and re-run
php vendor/bin/phinx migrate
```

### Legacy SQL Import Removal

The legacy `install/sql/mysql.sql` approach has been replaced by migrations:

- ✅ **New installations**: Use migrations automatically
- ✅ **Development workflow**: Create migrations for all schema changes
- ✅ **Version control**: All schema changes tracked in Git
- ❌ **Direct SQL imports**: No longer used for new installations

### Security Considerations

- **CLI-only execution**: Migrations run via command line only
- **Read-only admin interface**: Web interface shows status only
- **Backup requirements**: Always backup before production migrations
- **Access control**: Restrict migration command access to authorized personnel

### Migration Setup for Existing Installations

If you have an **existing TorrentPier installation** and want to adopt the migration system, you need to mark the initial migrations as already applied to avoid recreating your existing database schema.

#### Detection: Do You Need This?

You need migration setup if:
- ✅ You have an existing TorrentPier installation with data
- ✅ Your database already has tables like `bb_users`, `bb_forums`, etc.
- ✅ The admin migration panel shows "Migration System: ✗ Not Initialized"

#### Step-by-Step Setup Process

**1. Backup Your Database**
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

**2. Initialize Migration Table**
```bash
# This creates the bb_migrations table without running any migrations
php vendor/bin/phinx init
```

**3. Mark Initial Migrations as Applied (Fake Run)**
```bash
# Mark the schema migration as applied without running it
php vendor/bin/phinx migrate --fake --target=20250619000001

# Mark the data seeding migration as applied without running it
php vendor/bin/phinx migrate --fake --target=20250619000002
```

**4. Verify Setup**
```bash
# Check migration status
php vendor/bin/phinx status
```

You should see both initial migrations marked as "up" (applied).

#### Alternative: Manual SQL Method

If you prefer manual control, you can directly insert migration records:

```sql
-- Create migration table (if phinx init didn't work)
CREATE TABLE IF NOT EXISTS bb_migrations (
    version bigint(20) NOT NULL,
    migration_name varchar(100) DEFAULT NULL,
    start_time timestamp NULL DEFAULT NULL,
    end_time timestamp NULL DEFAULT NULL,
    breakpoint tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mark initial migrations as applied
INSERT INTO bb_migrations (version, migration_name, start_time, end_time, breakpoint)
VALUES
('20250619000001', 'InitialSchema', NOW(), NOW(), 0),
('20250619000002', 'SeedInitialData', NOW(), NOW(), 0);
```

#### Post-Setup Workflow

After setup, your existing installation will work exactly like a fresh installation:

```bash
# Create new migrations
php vendor/bin/phinx create AddNewFeature

# Run new migrations
php vendor/bin/phinx migrate

# Check status
php vendor/bin/phinx status
```

#### Troubleshooting

**Migration table already exists:**
- Check if you've already set up migrations: `php vendor/bin/phinx status`
- If it shows errors, you may need to recreate: `DROP TABLE bb_migrations;` then restart

**"Nothing to migrate" message:**
- This is normal after fake runs - it means setup was successful
- New migrations will appear when you create them

**Admin panel shows "Needs Setup":**
- Follow the setup process above
- Refresh the admin panel after completion

## 🗄️ Database Layer Migration

TorrentPier has completely replaced its legacy database layer (SqlDb/Dbs) with a modern implementation using Nette Database while maintaining 100% backward compatibility.

### No Code Changes Required

**Important**: All existing `DB()->method()` calls continue to work exactly as before. This is an internal modernization that requires **zero code changes** in your application.

```php
// ✅ All existing code continues to work unchanged
$user = DB()->fetch_row("SELECT * FROM users WHERE id = ?", 123);
$users = DB()->fetch_rowset("SELECT * FROM users");
$affected = DB()->affected_rows();
$result = DB()->sql_query("UPDATE users SET status = ? WHERE id = ?", 1, 123);
$escaped = DB()->escape($userInput);
```

### Key Improvements

#### Modern Foundation
- **Nette Database v3.2**: Modern, actively maintained database layer
- **PDO-based**: Improved security and performance
- **Type Safety**: Better error detection and IDE support
- **Singleton Pattern**: Efficient connection management

#### Enhanced Reliability
- **Automatic Resource Cleanup**: Better memory management
- **Improved Error Handling**: More detailed error information
- **Connection Stability**: Better handling of connection issues
- **Performance Optimizations**: Reduced overhead and improved query execution

#### Debugging and Development
- **Enhanced Explain Support**: Improved query analysis
- **Better Query Logging**: More detailed performance tracking
- **Debug Information**: Comprehensive debugging features
- **Memory Tracking**: Better resource usage monitoring

### Multiple Database Support

Multiple database servers continue to work exactly as before:

```php
// ✅ Multiple database access unchanged
$main_db = DB('db');           // Main database
$tracker_db = DB('tr');        // Tracker database
$stats_db = DB('stats');       // Statistics database
```

### Error Handling

All error handling patterns remain identical:

```php
// ✅ Error handling works exactly as before
$result = DB()->sql_query("SELECT * FROM users");
if (!$result) {
    $error = DB()->sql_error();
    echo "Error: " . $error['message'];
}
```

### Debug and Explain Features

All debugging functionality is preserved and enhanced:

```php
// ✅ Debug features work as before
DB()->debug('start');
// ... run queries ...
DB()->debug('stop');

// ✅ Explain functionality unchanged
DB()->explain('start');
DB()->explain('display');
```

### Performance Benefits

While maintaining compatibility, you get:
- **Faster Connection Handling**: Singleton pattern prevents connection overhead
- **Modern Query Execution**: Nette Database optimizations
- **Better Resource Management**: Automatic cleanup and proper connection handling
- **Reduced Memory Usage**: More efficient object management

### 📖 Detailed Documentation

For comprehensive information about the database layer changes, implementation details, and technical architecture, see:

**[src/Database/README.md](src/Database/README.md)**

This documentation covers:
- Complete architecture overview
- Technical implementation details
- Migration notes and compatibility information
- Debugging features and usage examples
- Performance benefits and benchmarks

### Legacy Code Cleanup

The following legacy files have been removed from the codebase:
- `src/Legacy/SqlDb.php` - Original database class
- `src/Legacy/Dbs.php` - Original database factory

These were completely replaced by:
- `src/Database/Database.php` - Modern database class with Nette Database (renamed from `DB.php`)
- `src/Database/DatabaseFactory.php` - Modern factory with backward compatibility (renamed from `DbFactory.php`)
- `src/Database/DatabaseDebugger.php` - Dedicated debug functionality extracted from Database class
- `src/Database/DebugSelection.php` - Debug-enabled wrapper for Nette Database Selection

### Verification

To verify the migration is working correctly:

```php
// ✅ Test basic database operations
$version = DB()->server_version();
$testQuery = DB()->fetch_row("SELECT 1 as test");
echo "Database version: $version, Test: " . $testQuery['test'];

// ✅ Test error handling
$result = DB()->sql_query("SELECT invalid_column FROM non_existent_table");
if (!$result) {
    $error = DB()->sql_error();
    echo "Error handling works: " . $error['message'];
}
```

## 💾 Unified Cache System Migration

TorrentPier has replaced its legacy Cache and Datastore systems with a modern unified implementation using Nette Caching while maintaining 100% backward compatibility.

### No Code Changes Required

**Important**: All existing `CACHE()` and `$datastore` calls continue to work exactly as before. This is an internal modernization that requires **zero code changes** in your application.

```php
// ✅ All existing code continues to work unchanged
$cache = CACHE('bb_cache');
$value = $cache->get('key');
$cache->set('key', $value, 3600);

$datastore = datastore();
$forums = $datastore->get('cat_forums');
$datastore->store('custom_data', $data);
```

### Key Improvements

#### Modern Foundation
- **Nette Caching v3.3**: Modern, actively maintained caching library
- **Unified System**: Single caching implementation instead of duplicate Cache/Datastore code
- **Singleton Pattern**: Efficient memory usage and consistent TorrentPier architecture
- **Advanced Features**: Dependencies, tags, bulk operations, memoization

#### Enhanced Performance
- **456,647+ operations per second**: Verified production performance
- **Memory Optimization**: Shared storage and efficient instance management
- **Debug Compatibility**: Full compatibility with Dev.php debugging features

### Enhanced Capabilities

New code can leverage advanced Nette Caching features:

```php
// ✅ Enhanced caching with dependencies
$cache = CACHE('bb_cache');
$forums = $cache->load('forums', function() {
    return build_forums_data();
}, [
    \Nette\Caching\Cache::Expire => '1 hour',
    \Nette\Caching\Cache::Files => ['/path/to/config.php']
]);

// ✅ Function memoization
$result = $cache->call('expensive_function', $param);
```

### 📖 Detailed Documentation

For comprehensive information about the unified cache system, advanced features, and technical architecture, see:

**[src/Cache/README.md](src/Cache/README.md)**

This documentation covers:
- Complete architecture overview and singleton pattern
- Advanced Nette Caching features and usage examples
- Performance benchmarks and storage type comparisons
- Critical compatibility issues resolved during implementation

### Verification

To verify the migration is working correctly:

```php
// ✅ Test basic cache operations
$cache = CACHE('test_cache');
$cache->set('test_key', 'test_value', 60);
$value = $cache->get('test_key');
echo "Cache test: " . ($value === 'test_value' ? 'PASSED' : 'FAILED');

// ✅ Test datastore operations
$datastore = datastore();
$datastore->store('test_item', ['status' => 'verified']);
$item = $datastore->get('test_item');
echo "Datastore test: " . ($item['status'] === 'verified' ? 'PASSED' : 'FAILED');
```

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

## 🌐 Language System Migration

TorrentPier has modernized its language system with a singleton pattern while maintaining 100% backward compatibility with existing global `$lang` variable.

### No Code Changes Required

**Important**: All existing `global $lang` calls continue to work exactly as before. This is an internal modernization that requires **zero code changes** in your application.

```php
// ✅ All existing code continues to work unchanged
global $lang;
echo $lang['FORUM'];
echo $lang['DATETIME']['TODAY'];
```

### Key Improvements

#### Modern Foundation
- **Singleton Pattern**: Efficient memory usage and consistent TorrentPier architecture
- **Centralized Management**: Single point of control for language loading and switching
- **Type Safety**: Better error detection and IDE support
- **Dot Notation Support**: Access nested language arrays with simple syntax

#### Enhanced Functionality
- **Automatic Fallback**: Source language fallback for missing translations
- **Dynamic Loading**: Load additional language files for modules/extensions
- **Runtime Modification**: Add or modify language strings at runtime
- **Locale Management**: Automatic locale setting based on language selection

### Enhanced Capabilities

New code can leverage the modern Language singleton features with convenient shorthand functions:

```php
// ✅ Convenient shorthand functions (recommended for frequent use)
echo __('FORUM');                           // Same as lang()->get('FORUM')
echo __('DATETIME.TODAY');                  // Dot notation for nested arrays
_e('WELCOME_MESSAGE');                      // Echo shorthand
$message = __('CUSTOM_MESSAGE', 'Default'); // With default value

// ✅ Full singleton access (for advanced features)
echo lang()->get('FORUM');
echo lang()->get('DATETIME.TODAY');  // Dot notation for nested arrays

// ✅ Check if language key exists
if (lang()->has('ADVANCED_FEATURE')) {
    echo __('ADVANCED_FEATURE');
}

// ✅ Get current language information
$currentLang = lang()->getCurrentLanguage();
$langName = lang()->getLanguageName();
$langLocale = lang()->getLanguageLocale();

// ✅ Load additional language files for modules
lang()->loadAdditionalFile('custom_module', 'en');

// ✅ Runtime language modifications
lang()->set('CUSTOM_KEY', 'Custom Value');
lang()->set('NESTED.KEY', 'Nested Value');
```

### Language Management

#### Available Languages
```php
// Get all available languages from configuration
$availableLanguages = lang()->getAvailableLanguages();

// Get language display name
$englishName = lang()->getLanguageName('en');  // Returns: "English"
$currentName = lang()->getLanguageName();       // Current language name

// Get language locale for formatting
$locale = lang()->getLanguageLocale('ru');      // Returns: "ru_RU.UTF-8"
```

#### Dynamic Language Loading
```php
// Load additional language files (useful for modules/plugins)
$success = lang()->loadAdditionalFile('torrent_management');
if ($success) {
    echo lang()->get('TORRENT_UPLOADED');
}

// Load from specific language
lang()->loadAdditionalFile('admin_panel', 'de');
```

#### Runtime Modifications
```php
// Set custom language strings
lang()->set('SITE_WELCOME', 'Welcome to Our Tracker!');
lang()->set('ERRORS.INVALID_TORRENT', 'Invalid torrent file');

// Modify existing strings
lang()->set('LOGIN', 'Sign In');
```

### Backward Compatibility Features

The singleton automatically maintains all global variables:

```php
// Global variable is automatically updated by the singleton
global $lang;

// When you call lang()->set(), global is updated
lang()->set('CUSTOM', 'Value');
echo $lang['CUSTOM'];  // Outputs: "Value"

// When language is initialized, $lang is populated
// $lang contains user language + source language fallbacks
```

### Integration with User System

The Language singleton integrates seamlessly with the User system:

```php
// User language is automatically detected and initialized
// Based on user preferences, browser detection, or defaults

// In User->init_userprefs(), language is now initialized with:
lang()->initializeLanguage($userLanguage);

// This replaces the old manual language file loading
// while maintaining exact same functionality
```

### Convenient Shorthand Functions

For frequent language access, TorrentPier provides convenient shorthand functions:

```php
// ✅ __() - Get language string (most common)
echo __('FORUM');                    // Returns: "Forum"
echo __('DATETIME.TODAY');           // Nested access: "Today"
$msg = __('MISSING_KEY', 'Default'); // With default value

// ✅ _e() - Echo language string directly
_e('WELCOME_MESSAGE');               // Same as: echo __('WELCOME_MESSAGE')
_e('USER_ONLINE', 'Online');         // With default value

// ✅ Common usage patterns
$title = __('PAGE_TITLE', config()->get('sitename'));
$error = __('ERROR.INVALID_INPUT', 'Invalid input');
```

These functions make language access much more convenient compared to the full `lang()->get()` syntax:

```php
// Before (verbose)
echo lang()->get('FORUM');
echo lang()->get('DATETIME.TODAY');
$msg = lang()->get('WELCOME', 'Welcome');

// After (concise)
echo __('FORUM');
echo __('DATETIME.TODAY');
$msg = __('WELCOME', 'Welcome');
```

### Magic Methods Support
```php
// Magic getter (same as lang()->get())
$welcome = lang()->WELCOME;
$today = lang()->{'DATETIME.TODAY'};

// Magic setter (same as lang()->set())
lang()->CUSTOM_MESSAGE = 'Hello World';
lang()->{'NESTED.KEY'} = 'Nested Value';

// Magic isset
if (isset(lang()->ADVANCED_FEATURE)) {
    // Language key exists
}
```

### Performance Benefits

While maintaining compatibility, you get:
- **Single Language Loading**: Languages loaded once and cached in singleton
- **Memory Efficiency**: No duplicate language arrays across application
- **Automatic Locale Setting**: Proper locale configuration for date/time formatting
- **Fallback Chain**: Source language → Default language → Requested language

### Verification

To verify the migration is working correctly:

```php
// ✅ Test convenient shorthand functions
echo "Forum text: " . __('FORUM');
echo "Today text: " . __('DATETIME.TODAY');
_e('INFORMATION');  // Echo directly

// ✅ Test with default values
echo "Custom: " . __('CUSTOM_KEY', 'Default Value');

// ✅ Test full singleton access
echo "Current language: " . lang()->getCurrentLanguage();
echo "Language name: " . lang()->getLanguageName();

// ✅ Test backward compatibility
global $lang;
echo "Global access: " . $lang['FORUM'];

// ✅ Verify globals are synchronized
lang()->set('TEST_KEY', 'Test Value');
echo "Sync test: " . $lang['TEST_KEY'];  // Should output: "Test Value"
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

## 📋 Select System Migration

The Select class has been moved and reorganized for better structure and consistency within the legacy system organization.

### Quick Migration Overview

```php
// ❌ Old way (deprecated)
\TorrentPier\Legacy\Select::language($new['default_lang'], 'default_lang');
\TorrentPier\Legacy\Select::timezone('', 'timezone_type');
\TorrentPier\Legacy\Select::template($pr_data['tpl_name'], 'tpl_name');

// ✅ New way (recommended)
\TorrentPier\Legacy\Common\Select::language($new['default_lang'], 'default_lang');
\TorrentPier\Legacy\Common\Select::timezone('', 'timezone_type');
\TorrentPier\Legacy\Common\Select::template($pr_data['tpl_name'], 'tpl_name');
```

#### Namespace Update
The Select class has been moved from `\TorrentPier\Legacy\Select` to `\TorrentPier\Legacy\Common\Select` to better organize legacy components.

#### Method Usage Remains Unchanged
```php
// Language selection dropdown
$languageSelect = \TorrentPier\Legacy\Common\Select::language($currentLang, 'language_field');

// Timezone selection dropdown
$timezoneSelect = \TorrentPier\Legacy\Common\Select::timezone($currentTimezone, 'timezone_field');

// Template selection dropdown
$templateSelect = \TorrentPier\Legacy\Common\Select::template($currentTemplate, 'template_field');
```

#### Available Select Methods
```php
// All existing methods remain available:
\TorrentPier\Legacy\Common\Select::language($selected, $name);
\TorrentPier\Legacy\Common\Select::timezone($selected, $name);
\TorrentPier\Legacy\Common\Select::template($selected, $name);
```

### Backward Compatibility

The old class path is deprecated but still works through class aliasing:

```php
// This still works but is deprecated
\TorrentPier\Legacy\Select::language($lang, 'default_lang');

// This is the new recommended way
\TorrentPier\Legacy\Common\Select::language($lang, 'default_lang');
```

### Migration Strategy

1. **Search and Replace**: Update all references to the old namespace
2. **Import Statements**: Update use statements if you're using them
3. **Configuration Files**: Update any configuration that references the old class path

```php
// Update use statements
// Old
use TorrentPier\Legacy\Select;

// New
use TorrentPier\Legacy\Common\Select;
```

## 🛠️ Development System Migration

The development and debugging system has been refactored to use a singleton pattern, providing better resource management and consistency across the application.

### Quick Migration Overview

```php
// ❌ Old way (still works, but not recommended)
$sqlLog = \TorrentPier\Dev::getSqlLog();
$isDebugAllowed = \TorrentPier\Dev::sqlDebugAllowed();
$shortQuery = \TorrentPier\Dev::shortQuery($sql);

// ✅ New way (recommended)
$sqlLog = dev()->getSqlDebugLog();
$isDebugAllowed = dev()->checkSqlDebugAllowed();
$shortQuery = dev()->formatShortQuery($sql);
```

### Key Development System Changes

#### Basic Usage
```php
// Get SQL debug log
$sqlLog = dev()->getSqlDebugLog();

// Check if SQL debugging is allowed
if (dev()->checkSqlDebugAllowed()) {
    $debugInfo = dev()->getSqlDebugLog();
}

// Format SQL queries for display
$formattedQuery = dev()->formatShortQuery($sql, true); // HTML escaped
$plainQuery = dev()->formatShortQuery($sql, false);   // Plain text
```

#### New Instance Methods
```php
// Access Whoops instance directly
$whoops = dev()->getWhoops();

// Check debug mode status
if (dev()->isDebugEnabled()) {
    // Debug mode is active
}

// Check environment
if (dev()->isLocalEnvironment()) {
    // Running in local development
}
```

### Backward Compatibility

All existing static method calls continue to work exactly as before:

```php
// This still works - backward compatibility maintained
$sqlLog = \TorrentPier\Dev::getSqlLog();
$isDebugAllowed = \TorrentPier\Dev::sqlDebugAllowed();
$shortQuery = \TorrentPier\Dev::shortQuery($sql);

// But this is now preferred
$sqlLog = dev()->getSqlDebugLog();
$isDebugAllowed = dev()->checkSqlDebugAllowed();
$shortQuery = dev()->formatShortQuery($sql);
```

### Performance Benefits

- **Single Instance**: Only one debugging instance across the entire application
- **Resource Efficiency**: Whoops handlers initialized once and reused
- **Memory Optimization**: Shared debugging state and configuration
- **Lazy Loading**: Debug features only activated when needed

### Advanced Usage

```php
// Access the singleton directly
$devInstance = \TorrentPier\Dev::getInstance();

// Initialize the system (called automatically in common.php)
\TorrentPier\Dev::init();

// Get detailed environment information
$environment = [
    'debug_enabled' => dev()->isDebugEnabled(),
    'local_environment' => dev()->isLocalEnvironment(),
    'sql_debug_allowed' => dev()->sqlDebugAllowed(),
];
```

## 🌍 HTTP Helper Migration

The `IsHelper` class has been **renamed** to `HttpHelper` for better clarity and consistency.  
This change is **breaking** because the old class name is no longer available.

### 🔄 Migration Strategy

Replace all references of:
```php
\TorrentPier\Helpers\IsHelper
```

With:
```php
\TorrentPier\Helpers\HttpHelper
```

## ⚠️ Breaking Changes

### Database Layer Changes
- **✅ No Breaking Changes**: All existing `DB()->method()` calls work exactly as before
- **Removed Files**: `src/Legacy/SqlDb.php` and `src/Legacy/Dbs.php` (replaced by modern implementation)
- **New Implementation**: Uses Nette Database v3.2 internally with full backward compatibility

### Deprecated Functions
- `get_config()` → Use `config()->get()`
- `set_config()` → Use `config()->set()`
- Direct `$bb_cfg` access → Use `config()` methods

### Deprecated Patterns
- `new TorrentPier\Censor()` → Use `censor()` global function
- Direct `$wordCensor` access → Use `censor()` methods
- `new TorrentPier\Dev()` → Use `dev()` global function
- Static `Dev::` methods → Use `dev()` instance methods
- `\TorrentPier\Legacy\Select::` → Use `\TorrentPier\Legacy\Common\Select::`
- `\TorrentPier\Helpers\IsHelper::` → Use `\TorrentPier\Helpers\HttpHelper::`

### File Structure Changes
- New `/src/Database/` directory for modern database classes
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
$censoredText = censor()->censorString($input);
```

### Select Usage
```php
// ✅ Use the new namespace consistently
$languageSelect = \TorrentPier\Legacy\Common\Select::language($currentLang, 'language_field');

// ✅ Store frequently used selects
class AdminPanel {
    private string $languageSelect;
    private string $timezoneSelect;

    public function __construct() {
        $this->languageSelect = \TorrentPier\Legacy\Common\Select::language('', 'default_lang');
        $this->timezoneSelect = \TorrentPier\Legacy\Common\Select::timezone('', 'timezone');
    }
}
```

### Development and Debugging
```php
// ✅ Use instance methods for debugging
if (dev()->checkSqlDebugAllowed()) {
    $debugLog = dev()->getSqlDebugLog();
}

// ✅ Access debugging utilities consistently
function formatSqlForDisplay(string $sql): string {
    return dev()->formatShortQuery($sql, true);
}

// ✅ Check environment properly
if (dev()->isLocalEnvironment()) {
    // Development-specific code
}
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

### HttpHelper usage
```php
use TorrentPier\Helpers\HttpHelper;

// ✅ Always use HttpHelper to detect protocol
if (HttpHelper::isHTTPS()) {
    // Handle HTTPS-specific logic
}

// ✅ Prefer HttpHelper over deprecated IsHelper
function getBaseUrl(): string {
    $protocol = HttpHelper::isHTTPS() ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'];
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

### Testing and Quality Assurance
```bash
# ✅ Run tests before deploying changes
./vendor/bin/pest

# ✅ Validate test coverage for new components
./vendor/bin/pest --coverage
```

For comprehensive testing documentation and best practices, see [tests/README.md](tests/README.md).

---

**Important**: Always test the upgrade process in a staging environment before applying it to production. Keep backups of your database and files until you're confident the upgrade was successful.

For additional support, visit our [Official Forum](https://torrentpier.com) or check our [GitHub Repository](https://github.com/torrentpier/torrentpier) for the latest updates and community discussions.
