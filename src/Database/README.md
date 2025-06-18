# TorrentPier Database Layer

This directory contains the new database layer for TorrentPier that uses Nette Database internally while maintaining full backward compatibility with the original SqlDb interface.

## Overview

The new database system has completely replaced the legacy SqlDb/Dbs system and provides:

- **Full backward compatibility** - All existing `DB()->method()` calls work unchanged
- **Nette Database integration** - Modern, efficient database layer under the hood
- **Singleton pattern** - Efficient connection management
- **Complete feature parity** - All original functionality preserved

## Architecture

### Classes

1. **`DB`** - Main singleton database class using Nette Database Connection
2. **`DbFactory`** - Factory that has completely replaced the legacy SqlDb/Dbs system

### Key Features

- **Singleton Pattern**: Ensures single database connection per server configuration
- **Multiple Database Support**: Handles multiple database servers via DbFactory
- **Raw SQL Support**: Uses Nette Database's Connection class (SQL way) for minimal code impact
- **Complete Error Handling**: Maintains existing error handling behavior
- **Full Debug Support**: Preserves all debugging, logging, and explain functionality
- **Performance Tracking**: Query timing and slow query detection

## Implementation Status

- ✅ **Complete Replacement**: Legacy SqlDb/Dbs classes have been removed from the codebase
- ✅ **Backward Compatibility**: All existing `DB()->method()` calls work unchanged
- ✅ **Debug System**: Full explain(), logging, and performance tracking
- ✅ **Error Handling**: Complete error handling with sql_error() support
- ✅ **Connection Management**: Singleton pattern with proper initialization

## Usage

### Standard Database Operations
```php
// All existing code works unchanged
$user = DB()->fetch_row("SELECT * FROM users WHERE id = ?", 123);
$users = DB()->fetch_rowset("SELECT * FROM users");
$affected = DB()->affected_rows();

// Raw queries
$result = DB()->sql_query("UPDATE users SET status = ? WHERE id = ?", 1, 123);

// Data building
$data = ['name' => 'John', 'email' => 'john@example.com'];
$sql = DB()->build_array('INSERT', $data);
```

### Multiple Database Servers
```php
// Access different database servers
$main_db = DB('db');           // Main database
$tracker_db = DB('tr');        // Tracker database
$stats_db = DB('stats');       // Statistics database
```

### Error Handling
```php
$result = DB()->sql_query("SELECT * FROM users");
if (!$result) {
    $error = DB()->sql_error();
    echo "Error: " . $error['message'];
}
```

## Configuration

The database configuration is handled through the existing TorrentPier config system:

```php
// Initialized in common.php
TorrentPier\Database\DbFactory::init(
    config()->get('db'),           // Database configurations
    config()->get('db_alias', [])  // Database aliases
);
```

## Benefits

### Performance
- **Efficient Connections**: Singleton pattern prevents connection overhead
- **Modern Database Layer**: Nette Database v3.2 optimizations
- **Resource Management**: Automatic cleanup and proper connection handling

### Maintainability
- **Modern Codebase**: Uses current PHP standards and type declarations
- **Better Architecture**: Clean separation of concerns
- **Nette Ecosystem**: Part of actively maintained Nette framework

### Reliability
- **Proven Technology**: Nette Database is battle-tested
- **Regular Updates**: Automatic security and bug fixes through composer
- **Type Safety**: Better error detection and IDE support

## Debugging Features

All original debugging features are preserved and enhanced:

### Query Logging
- SQL query logging with timing
- Slow query detection and logging
- Memory usage tracking

### Debug Information
```php
// Enable debugging (same as before)
DB()->debug('start');
// ... run queries ...
DB()->debug('stop');
```

### Explain Functionality
```php
// Explain queries (same interface as before)
DB()->explain('start');
DB()->explain('display');
```

## Technical Details

### Nette Database Integration
- Uses Nette Database **Connection** class (SQL way)
- Maintains raw SQL approach for minimal migration impact
- PDO-based with proper parameter binding

### Compatibility Layer
- All original method signatures preserved
- Same return types and behavior
- Error handling matches original implementation

### Connection Management
- Single connection per database server
- Lazy connection initialization
- Proper connection cleanup

## Migration Notes

This is a **complete replacement** that maintains 100% backward compatibility:

1. **No Code Changes Required**: All existing `DB()->method()` calls work unchanged
2. **Same Configuration**: Uses existing database configuration
3. **Same Behavior**: Error handling, return values, and debugging work identically
4. **Enhanced Performance**: Better connection management and modern database layer

## Dependencies

- **Nette Database v3.2**: Already included in composer.json
- **PHP 8.0+**: Required for type declarations and modern features

## Files

- `DB.php` - Main database class with full backward compatibility
- `DbFactory.php` - Factory for managing database instances
- `README.md` - This documentation
