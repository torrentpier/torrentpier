<?php
/**
 * Phinx configuration for TorrentPier
 */

if (PHP_SAPI != 'cli') {
    die(basename(__FILE__));
}

// Only load what's needed for Phinx - don't bootstrap the entire application
const BB_ROOT = __DIR__ . DIRECTORY_SEPARATOR;
const BB_PATH = __DIR__;
require_once BB_ROOT . 'library/defines.php';

// Load environment variables
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createMutable(__DIR__);
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
        'migrations' => __DIR__ . '/migrations'
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
