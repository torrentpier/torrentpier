---
sidebar_position: 10
title: Development System
---

# Development System Migration

The development and debugging system has been refactored with Tracy debug bar integration, replacing the legacy HTML-based debug panel.

## Quick Migration Overview

```php
// Removed methods (use Tracy debug bar instead)
// \TorrentPier\Dev::getSqlLog();      // Removed - use Tracy DatabasePanel
// \TorrentPier\Dev::getSqlLogHtml();  // Removed - use Tracy DatabasePanel

// Still available
$isDebugAllowed = dev()->checkSqlDebugAllowed();
$shortQuery = dev()->formatShortQuery($sql);
```

## Key Development System Changes

### Tracy Debug Bar Integration

SQL query debugging is now handled by Tracy debug bar panels:

- **DatabasePanel** - Shows all SQL queries with timing, EXPLAIN support
- **PerformancePanel** - Execution time, memory usage
- **CachePanel** - Cache/datastore operations
- **TemplatePanel** - Twig template info

```php
// Enable Tracy debug bar in config
$bb_cfg['debug'] = [
    'enable' => true,
    'panels' => [
        'performance' => true,
        'database' => true,
        'cache' => true,
        'template' => true,
    ],
];

// Enable EXPLAIN for queries via cookie
// Set tracy_explain=1 cookie
```

### Available Instance Methods

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

// Format SQL queries for display
$formattedQuery = dev()->formatShortQuery($sql, true); // HTML escaped
$plainQuery = dev()->formatShortQuery($sql, false);   // Plain text
```

## Breaking Changes

The following methods were removed (replaced by Tracy debug bar):

- `\TorrentPier\Dev::getSqlLog()` - use Tracy DatabasePanel
- `\TorrentPier\Dev::getSqlLogHtml()` - use Tracy DatabasePanel
- `\TorrentPier\Dev::getSqlLogInstance()` - use Tracy DatabasePanel
- `\TorrentPier\Dev::sqlDebugAllowedInstance()` - use `dev()->checkSqlDebugAllowed()`
- `\TorrentPier\Dev::shortQueryInstance()` - use `dev()->formatShortQuery()`
- `DB()->explain()` method - use Tracy DatabasePanel with tracy_explain cookie

## Performance Benefits

- **Single Instance**: Only one debugging instance across the entire application
- **Resource Efficiency**: Whoops handlers initialized once and reused
- **Memory Optimization**: Shared debugging state and configuration
- **Lazy Loading**: Debug features only activated when needed

## Advanced Usage

```php
// Access via dev() helper
$devInstance = dev();

// Initialize the system (called automatically in common.php)
// Dev is automatically initialized when first accessed

// Get detailed environment information
$environment = [
    'debug_enabled' => dev()->isDebugEnabled(),
    'local_environment' => dev()->isLocalEnvironment(),
    'sql_debug_allowed' => dev()->checkSqlDebugAllowed(),
];
```

## Best Practices

### Use Instance Methods for Debugging

```php
if (dev()->checkSqlDebugAllowed()) {
    $debugLog = dev()->getSqlDebugLog();
}
```

### Access Debugging Utilities Consistently

```php
function formatSqlForDisplay(string $sql): string {
    return dev()->formatShortQuery($sql, true);
}
```

### Check Environment Properly

```php
if (dev()->isLocalEnvironment()) {
    // Development-specific code
}
```
