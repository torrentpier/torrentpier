# Application Configuration

System configuration files using PHP arrays for type safety and IDE support:

- **app.php**: Core application settings
  - Site name, URL, timezone
  - Debug mode, environment
  - Feature flags and toggles

- **database.php**: Database connection settings
  - Multiple connection definitions
  - Read/write splitting configuration
  - Connection pooling settings

- **cache.php**: Cache driver configurations
  - Redis, Memcached, file-based settings
  - TTL defaults per cache type
  - Cache key prefixes

- **tracker.php**: BitTorrent tracker settings
  - Announce intervals
  - Peer limits
  - Ratio requirements

- **environments/**: Environment-specific overrides
  - Development, staging, production settings
  - Local developer configurations

Example database configuration:
```php
<?php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'tp'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];
```