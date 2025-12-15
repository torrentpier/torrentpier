---
sidebar_position: 4
title: Unified Cache System
---

# Unified Cache System Migration

TorrentPier has replaced its legacy Cache and Datastore systems with a modern unified implementation using Nette Caching while maintaining 100% backward compatibility.

## No Code Changes Required

**Important**: All existing `CACHE()` and `$datastore` calls continue to work exactly as before. This is an internal modernization that requires **zero code changes** in your application.

```php
// All existing code continues to work unchanged
$cache = CACHE('bb_cache');
$value = $cache->get('key');
$cache->set('key', $value, 3600);

$datastore = datastore();
$forums = $datastore->get('cat_forums');
$datastore->store('custom_data', $data);
```

## Key Improvements

### Modern Foundation

- **Nette Caching v3.3**: Modern, actively maintained caching library
- **Unified System**: Single caching implementation instead of duplicate Cache/Datastore code
- **DI Container**: Managed via Application container for efficient memory usage
- **Advanced Features**: Dependencies, tags, bulk operations, memoization

### Enhanced Performance

- **456,647+ operations per second**: Verified production performance
- **Memory Optimization**: Shared storage and efficient instance management
- **Debug Compatibility**: Full compatibility with Dev.php debugging features

## Enhanced Capabilities

New code can leverage advanced Nette Caching features:

```php
// Enhanced caching with dependencies
$cache = CACHE('bb_cache');
$forums = $cache->load('forums', function() {
    return build_forums_data();
}, [
    \Nette\Caching\Cache::Expire => '1 hour',
    \Nette\Caching\Cache::Files => ['/path/to/config.php']
]);

// Function memoization
$result = $cache->call('expensive_function', $param);
```

## Detailed Documentation

For comprehensive information about the unified cache system, advanced features, and technical architecture, see:

**[src/Cache/README.md](https://github.com/torrentpier/torrentpier/blob/master/src/Cache/README.md)**

This documentation covers:
- Complete architecture overview and DI container integration
- Advanced Nette Caching features and usage examples
- Performance benchmarks and storage type comparisons
- Critical compatibility issues resolved during implementation

## Verification

To verify the migration is working correctly:

```php
// Test basic cache operations
$cache = CACHE('test_cache');
$cache->set('test_key', 'test_value', 60);
$value = $cache->get('test_key');
echo "Cache test: " . ($value === 'test_value' ? 'PASSED' : 'FAILED');

// Test datastore operations
$datastore = datastore();
$datastore->store('test_item', ['status' => 'verified']);
$item = $datastore->get('test_item');
echo "Datastore test: " . ($item['status'] === 'verified' ? 'PASSED' : 'FAILED');
```
