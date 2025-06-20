# TorrentPier Whoops Enhanced Error Reporting

This directory contains enhanced Whoops error handlers specifically designed for TorrentPier to provide better debugging information when database errors occur.

## Features

### Enhanced Database Error Reporting

The enhanced Whoops handlers provide comprehensive database information when errors occur:

1. **Current SQL Query** - Shows the exact query that caused the error
2. **Recent Query History** - Displays the last 5 SQL queries executed
3. **Database Connection Status** - Connection state, server info, database name
4. **Error Context** - PDO error codes, exception details, source location
5. **TorrentPier Environment** - Debug mode status, system information

### Components

#### EnhancedPrettyPageHandler

Extends Whoops' default `PrettyPageHandler` to include:
- **Database Information** table with connection details and current query
- **Recent SQL Queries** table showing query history with timing
- **TorrentPier Environment** table with system status

#### DatabaseErrorHandler

Specialized handler that:
- Adds database context to exception stack frames
- Identifies database-related code in the call stack
- Collects comprehensive database state information
- Formats SQL queries for readable display

## Usage

The enhanced handlers are automatically activated when `DBG_USER` is enabled in TorrentPier configuration.

### Automatic Integration

```php
// In src/Dev.php - automatically configured
$prettyPageHandler = new \TorrentPier\Whoops\EnhancedPrettyPageHandler();
```

### Database Error Logging

Database errors are now automatically logged even when they occur in Nette Database layer:

```php
// Enhanced error handling in Database.php
try {
    $row = $result->fetch();
} catch (\Exception $e) {
    // Log the error including the query that caused it
    $this->debugger->log_error($e);
    throw $e; // Re-throw for Whoops display
}
```

## Error Information Displayed

When a database error occurs, Whoops will now show:

### Database Information
- Connection Status: Connected/Disconnected
- Database Server: Host and port information
- Selected Database: Current database name
- Database Engine: MySQL/PostgreSQL/etc.
- Total Queries: Number of queries executed
- Total Query Time: Cumulative execution time
- Current Query: The SQL that caused the error
- Last Database Error: Error code and message
- PDO Driver: Database driver information
- Server Version: Database server version

### Recent SQL Queries
- **Query #1-5**: Last 5 queries executed
  - SQL: Formatted query text
  - Time: Execution time in seconds
  - Source: File and line where query originated
  - Info: Additional query information
  - Memory: Memory usage if available

### TorrentPier Environment
- Application Environment: local/production/etc.
- Debug Mode: Enabled/Disabled
- SQL Debug: Enabled/Disabled
- TorrentPier Version: Current version
- Config Loaded: Configuration status
- Cache System: Availability status
- Language System: Status and encoding
- Template System: Twig-based availability
- Execution Time: Request processing time
- Peak Memory: Maximum memory used
- Current Memory: Current memory usage
- Request Method: GET/POST/etc.
- Request URI: Current page
- User Agent: Browser information
- Remote IP: Client IP address

## Configuration

The enhanced handlers respect TorrentPier's debug configuration:

- `DBG_USER`: Must be enabled to show enhanced error pages
- `SQL_DEBUG`: Enables SQL query logging and timing
- `APP_ENV`: Determines environment-specific features

## Logging

Database errors are now logged in multiple locations:

1. **PHP Error Log**: Basic error message
2. **TorrentPier bb_log**: Detailed error with context (`database_errors.log`)
3. **Whoops Log**: Complete error details (`php_whoops.log`)

## Security

The enhanced handlers maintain security by:
- Only showing detailed information when `DBG_USER` is enabled
- Using Whoops' blacklist for sensitive data
- Logging detailed information to files (not user-accessible)
- Providing generic error messages to non-debug users

## Backward Compatibility

All enhancements are:
- **100% backward compatible** with existing TorrentPier code
- **Non-breaking** - existing error handling continues to work
- **Optional** - only activated in debug mode
- **Safe** - no security implications for production use