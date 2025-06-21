# TorrentPier Database Layer

This directory contains the new database layer for TorrentPier 3.0 that uses Nette Database internally. **Breaking changes**: This replaces the legacy SqlDb interface and requires code migration.

## Overview

The new database system has completely replaced the legacy SqlDb/Dbs system and provides:

- **Modern API** - New `DB()->method()` calls with improved functionality
- **Nette Database integration** - Modern, efficient database layer under the hood
- **Singleton pattern** - Efficient connection management
- **Complete feature parity** - All original functionality preserved

## Architecture

### Classes

1. **`Database`** - Main singleton database class using Nette Database Connection
2. **`DatabaseFactory`** - Factory that has completely replaced the legacy SqlDb/Dbs system
3. **`DatabaseDebugger`** - Dedicated debug functionality extracted from Database class
4. **`DebugSelection`** - Debug-enabled wrapper for Nette Database Selection

### Key Features

- **Singleton Pattern**: Ensures single database connection per server configuration
- **Multiple Database Support**: Handles multiple database servers via DatabaseFactory
- **Raw SQL Support**: Uses Nette Database's Connection class (SQL way) for minimal code impact
- **Complete Error Handling**: Maintains existing error handling behavior
- **Full Debug Support**: Preserves all debugging, logging, and explain functionality
- **Performance Tracking**: Query timing and slow query detection
- **Clean Architecture**: Debug functionality extracted to dedicated DatabaseDebugger class
- **Modular Design**: Single responsibility principle with separate debug and database concerns

## Implementation Status

- ✅ **Complete Replacement**: Legacy SqlDb/Dbs classes have been removed from the codebase
- ✅ **Backward Compatibility**: All existing `DB()->method()` calls work unchanged
- ✅ **Debug System**: Full explain(), logging, and performance tracking
- ✅ **Error Handling**: Complete error handling with sql_error() support
- ✅ **Connection Management**: Singleton pattern with proper initialization
- ✅ **Clean Architecture**: Debug functionality extracted to dedicated classes
- ✅ **Class Renaming**: Renamed DB → Database, DbFactory → DatabaseFactory for consistency

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
TorrentPier\Database\DatabaseFactory::init(
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

- `Database.php` - Main database class with full backward compatibility
- `DatabaseFactory.php` - Factory for managing database instances
- `DatabaseDebugger.php` - Dedicated debug functionality class
- `DebugSelection.php` - Debug-enabled Nette Selection wrapper
- `README.md` - This documentation

## Future Enhancement: Gradual Migration to Nette Explorer

While the current implementation uses Nette Database's **Connection** class (SQL way) for maximum compatibility, TorrentPier can gradually migrate to **Nette Database Explorer** for more modern ORM-style database operations.

### Phase 1: Hybrid Approach

Add Explorer support alongside existing Connection-based methods:

```php
// Current Connection-based approach (maintains compatibility)
$users = DB()->fetch_rowset("SELECT * FROM users WHERE status = ?", 1);

// New Explorer-based approach (added gradually)
$users = DB()->table('users')->where('status', 1)->fetchAll();
```

### Phase 2: Explorer Method Examples

#### Basic Table Operations
```php
// Select operations
$user = DB()->table('users')->get(123);                           // Get by ID
$users = DB()->table('users')->where('status', 1)->fetchAll();    // Where condition
$count = DB()->table('users')->where('status', 1)->count();       // Count records

// Insert operations
$user_id = DB()->table('users')->insert([
    'username' => 'john',
    'email' => 'john@example.com',
    'reg_time' => time()
]);

// Update operations
DB()->table('users')
    ->where('id', 123)
    ->update(['last_visit' => time()]);

// Delete operations
DB()->table('users')
    ->where('status', 0)
    ->delete();
```

#### Advanced Explorer Features
```php
// Joins and relationships
$posts = DB()->table('posts')
    ->select('posts.*, users.username')
    ->where('posts.forum_id', 5)
    ->order('posts.post_time DESC')
    ->limit(20)
    ->fetchAll();

// Aggregations
$stats = DB()->table('torrents')
    ->select('forum_id, COUNT(*) as total, SUM(size) as total_size')
    ->where('approved', 1)
    ->group('forum_id')
    ->fetchAll();

// Subqueries
$active_users = DB()->table('users')
    ->where('last_visit > ?', time() - 86400)
    ->where('id IN', DB()->table('posts')
        ->select('user_id')
        ->where('post_time > ?', time() - 86400)
    )
    ->fetchAll();
```

#### Working with Related Data
```php
// One-to-many relationships
$user = DB()->table('users')->get(123);
$user_posts = $user->related('posts')->order('post_time DESC');

// Many-to-many through junction table
$torrent = DB()->table('torrents')->get(456);
$seeders = $torrent->related('bt_tracker', 'torrent_id')
    ->where('seeder', 'yes')
    ->select('user_id');
```

### Phase 3: Migration Strategy

#### Step-by-Step Conversion
1. **Identify Patterns**: Find common SQL patterns in the codebase
2. **Create Helpers**: Build wrapper methods for complex queries
3. **Test Incrementally**: Convert one module at a time
4. **Maintain Compatibility**: Keep both approaches during transition

#### Example Migration Pattern
```php
// Before: Raw SQL
$result = DB()->sql_query("
    SELECT t.*, u.username
    FROM torrents t
    JOIN users u ON t.poster_id = u.user_id
    WHERE t.forum_id = ? AND t.approved = 1
    ORDER BY t.reg_time DESC
    LIMIT ?
", $forum_id, $limit);

$torrents = [];
while ($row = DB()->sql_fetchrow($result)) {
    $torrents[] = $row;
}

// After: Explorer ORM
$torrents = DB()->table('torrents')
    ->alias('t')
    ->select('t.*, u.username')
    ->where('t.forum_id', $forum_id)
    ->where('t.approved', 1)
    ->order('t.reg_time DESC')
    ->limit($limit)
    ->fetchAll();
```

### Phase 4: Advanced Explorer Features

#### Custom Repository Classes
```php
// Create specialized repository classes
class TorrentRepository
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getApprovedByForum($forum_id, $limit = 20)
    {
        return $this->db->table('torrents')
            ->where('forum_id', $forum_id)
            ->where('approved', 1)
            ->order('reg_time DESC')
            ->limit($limit)
            ->fetchAll();
    }

    public function getTopSeeded($limit = 10)
    {
        return $this->db->table('torrents')
            ->where('approved', 1)
            ->order('seeders DESC')
            ->limit($limit)
            ->fetchAll();
    }
}

// Usage
$torrentRepo = new TorrentRepository(DB());
$popular = $torrentRepo->getTopSeeded();
```

#### Database Events and Caching
```php
// Add caching layer
$cached_result = DB()->table('users')
    ->where('status', 1)
    ->cache('active_users', 3600)  // Cache for 1 hour
    ->fetchAll();

// Database events for logging
DB()->onQuery[] = function ($query, $parameters, $time) {
    if ($time > 1.0) {  // Log slow queries
        error_log("Slow query ({$time}s): $query");
    }
};
```

### Benefits of Explorer Migration

#### Developer Experience
- **Fluent Interface**: Chainable method calls
- **IDE Support**: Better autocomplete and type hints
- **Less SQL**: Reduced raw SQL writing
- **Built-in Security**: Automatic parameter binding

#### Code Quality
- **Readable Code**: Self-documenting query building
- **Reusable Patterns**: Common queries become methods
- **Type Safety**: Better error detection
- **Testing**: Easier to mock and test

#### Performance
- **Query Optimization**: Explorer can optimize queries
- **Lazy Loading**: Load related data only when needed
- **Connection Pooling**: Better resource management
- **Caching Integration**: Built-in caching support
