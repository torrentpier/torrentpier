---
sidebar_position: 3
title: Database Layer Migration
---

# Database Layer Migration

TorrentPier has completely replaced its legacy database layer (SqlDb/Dbs) with a modern implementation using Nette Database while maintaining 100% backward compatibility.

## No Code Changes Required

**Important**: All existing `DB()->method()` calls continue to work exactly as before. This is an internal modernization that requires **zero code changes** in your application.

```php
// All existing code continues to work unchanged
$user = DB()->fetch_row("SELECT * FROM users WHERE id = ?", 123);
$users = DB()->fetch_rowset("SELECT * FROM users");
$affected = DB()->affected_rows();
$result = DB()->sql_query("UPDATE users SET status = ? WHERE id = ?", 1, 123);
$escaped = DB()->escape($userInput);
```

## Key Improvements

### Modern Foundation

- **Nette Database v3.2**: Modern, actively maintained database layer
- **PDO-based**: Improved security and performance
- **Type Safety**: Better error detection and IDE support
- **DI Container**: Managed via Application container as singleton service

### Enhanced Reliability

- **Automatic Resource Cleanup**: Better memory management
- **Improved Error Handling**: More detailed error information
- **Connection Stability**: Better handling of connection issues
- **Performance Optimizations**: Reduced overhead and improved query execution

### Debugging and Development

- **Enhanced Explain Support**: Improved query analysis
- **Better Query Logging**: More detailed performance tracking
- **Debug Information**: Comprehensive debugging features
- **Memory Tracking**: Better resource usage monitoring

## Error Handling

All error handling patterns remain identical:

```php
// Error handling works exactly as before
$result = DB()->sql_query("SELECT * FROM users");
if (!$result) {
    $error = DB()->sql_error();
    echo "Error: " . $error['message'];
}
```

## Debug and Explain Features

All debugging functionality is preserved and enhanced:

```php
// Debug features work as before
DB()->debug('start');
// ... run queries ...
DB()->debug('stop');

// EXPLAIN functionality via Tracy debug bar
// Set tracy_explain cookie to enable EXPLAIN for queries
// Tracy DatabasePanel shows EXPLAIN results inline
```

## Performance Benefits

While maintaining compatibility, you get:

- **Faster Connection Handling**: DI container manages single instance
- **Modern Query Execution**: Nette Database optimizations
- **Better Resource Management**: Automatic cleanup and proper connection handling
- **Reduced Memory Usage**: More efficient object management

## Detailed Documentation

For comprehensive information about the database layer changes, implementation details, and technical architecture, see:

**[src/Database/README.md](https://github.com/torrentpier/torrentpier/blob/master/src/Database/README.md)**

This documentation covers:
- Complete architecture overview
- Technical implementation details
- Migration notes and compatibility information
- Debugging features and usage examples
- Performance benefits and benchmarks

## Legacy Code Cleanup

The following legacy files have been removed from the codebase:

- `src/Legacy/SqlDb.php` - Original database class
- `src/Legacy/Dbs.php` - Original database factory

These were completely replaced by:

- `src/Database/Database.php` - Modern database class with Nette Database
- `src/Database/DatabaseDebugger.php` - Dedicated debug functionality extracted from Database class
- `src/Database/DebugSelection.php` - Debug-enabled wrapper for Nette Database Selection

## Verification

To verify the migration is working correctly:

```php
// Test basic database operations
$version = DB()->server_version();
$testQuery = DB()->fetch_row("SELECT 1 as test");
echo "Database version: $version, Test: " . $testQuery['test'];

// Test error handling
$result = DB()->sql_query("SELECT invalid_column FROM non_existent_table");
if (!$result) {
    $error = DB()->sql_error();
    echo "Error handling works: " . $error['message'];
}
```
