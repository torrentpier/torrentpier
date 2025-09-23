<?php
/**
 * Phinx configuration for TorrentPier
 */

define('BB_ROOT', dirname(__DIR__) . '/');
define('BB_PATH', BB_ROOT);

if (PHP_SAPI != 'cli') {
    die(basename(__FILE__));
}

// Only load what's needed for Phinx - don't bootstrap the entire application
require_once BB_ROOT . 'library/defines.php';

// Load environment variables
use Dotenv\Dotenv;

require_once BB_ROOT . 'vendor/autoload.php';

if (file_exists(BB_ROOT . '.env')) {
    $dotenv = Dotenv::createMutable(BB_ROOT);
    $dotenv->load();
}

// Helper function for environment variables
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

return [
    'paths' => [
        'migrations' => BB_ROOT . '/migrations'
    ],
    'environments' => [
        'default_migration_table' => BB_MIGRATIONS,
        'default_environment' => env('APP_ENV', 'production'),
        'production' => [
            'adapter' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => (int)env('DB_PORT', 3306),
            'name' => env('DB_DATABASE'),
            'user' => env('DB_USERNAME'),
            'pass' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'table_options' => [
                'ENGINE' => 'InnoDB',
                'DEFAULT CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_unicode_ci'
            ]
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => (int)env('DB_PORT', 3306),
            'name' => env('DB_DATABASE'),
            'user' => env('DB_USERNAME'),
            'pass' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'table_options' => [
                'ENGINE' => 'InnoDB',
                'DEFAULT CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_unicode_ci'
            ]
        ]
    ],
    'version_order' => 'creation',
];
