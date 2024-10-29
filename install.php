<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', __DIR__ . '/');
define('BB_PATH', __DIR__);

// Check CLI mode
if (php_sapi_name() !== 'cli') {
    die('Please run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php ' . basename(__FILE__) . '</code> in CLI mode');
}

// Get all constants
require_once BB_ROOT . '/library/defines.php';

// Include functions
require_once INC_DIR . '/functions_cli.php';

/**
 * System requirements
 */
define('CHECK_REQUIREMENTS', [
    'php_min_version' => '8.1.0',
    'ext_list' => [
        'json',
        'curl',
        'readline',
        'mysqli',
        'bcmath',
        'mbstring',
        'intl',
        'xml',
        'xmlwriter',
        'zip'
    ],
]);

/**
 * Remove directory recursively
 *
 * @param string $dir
 * @return void
 */
function rmdir_rec(string $dir): void
{
    $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it,
        RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($dir);
}

/**
 * Setting permissions recursively
 *
 * @param string $dir
 * @param int $dirPermissions
 * @param int $filePermissions
 * @return void
 */
function chmod_r(string $dir, int $dirPermissions, int $filePermissions): void
{
    $dp = opendir($dir);
    while ($file = readdir($dp)) {
        if (($file == '.') || ($file == '..')) {
            continue;
        }

        $fullPath = realpath($dir . '/' . $file);
        if (is_dir($fullPath)) {
            cli_out("- Directory: $fullPath");
            chmod($fullPath, $dirPermissions);
            chmod_r($fullPath, $dirPermissions, $filePermissions);
        } elseif (is_file($fullPath)) {
            // out("- File: $fullPath");
            chmod($fullPath, $filePermissions);
        } else {
            cli_out("- Cannot find target path: $fullPath", 'error');
            return;
        }
    }

    closedir($dp);
}

// Welcoming message
cli_out("--- TorrentPier Installer ---\n", 'info');

// Checking extensions
cli_out("- Checking installed extensions...", 'info');

// [1] Check PHP Version
if (!version_compare(PHP_VERSION, CHECK_REQUIREMENTS['php_min_version'], '>=')) {
    cli_out("- TorrentPier requires PHP version " . CHECK_REQUIREMENTS['php_min_version'] . "+ Your PHP version " . PHP_VERSION, 'warning');
}

// [2] Check installed PHP Extensions on server
foreach (CHECK_REQUIREMENTS['ext_list'] as $ext) {
    if (!extension_loaded($ext)) {
        cli_out("- ext-$ext not installed. Check out php.ini file", 'error');
        exit;
    } else {
        cli_out("- ext-$ext installed!");
    }
}
cli_out("- All extensions are installed!\n", 'success');

// Check if already installed
if (is_file(BB_ROOT . '.env')) {
    cli_out('- TorrentPier already installed', 'warning');
    // Re-install confirmation
    if (!cli_confirm('Are you sure want to re-install TorrentPier? [y/N]: ')) {
        exit;
    }
    cli_out("\n- Re-install process started...", 'info');
    // environment
    if (is_file(BB_ROOT . '.env')) {
        if (unlink(BB_ROOT . '.env')) {
            cli_out('- Environment file successfully removed!');
        } else {
            cli_out('- Cannot remove environment (.env) file. Delete it manually', 'error');
            exit;
        }
    }
    // composer.phar
    if (is_file(BB_ROOT . 'composer.phar')) {
        if (unlink(BB_ROOT . 'composer.phar')) {
            cli_out("- composer.phar file successfully removed!");
        } else {
            cli_out('- Cannot remove composer.phar file. Delete it manually', 'error');
            exit;
        }
    }
    // composer dir
    if (is_dir(BB_ROOT . 'vendor')) {
        rmdir_rec(BB_ROOT . 'vendor');
        if (!is_dir(BB_ROOT . 'vendor')) {
            cli_out("- Composer directory successfully removed!");
        } else {
            cli_out('- Cannot remove Composer directory. Delete it manually', 'error');
            exit;
        }
    }
    cli_out("- Re-install process completed!\n", 'success');
}

// Applying permissions
cli_out("- Applying permissions for folders...", 'info');
chmod_r(BB_ROOT . 'data', 0755, 0644);
chmod_r(BB_ROOT . 'internal_data', 0755, 0644);
chmod_r(BB_ROOT . 'sitemap', 0755, 0644);
cli_out("- Permissions successfully applied!\n", 'success');

// Check composer installation
if (!is_file(BB_ROOT . 'vendor/autoload.php')) {
    cli_out('- Hmm, it seems there are no Composer dependencies', 'info');

    // Downloading composer
    if (!is_file(BB_ROOT . 'composer.phar')) {
        cli_out('- Downloading Composer...', 'info');
        if (copy('https://getcomposer.org/installer', BB_ROOT . 'composer-setup.php')) {
            cli_out("- Composer successfully downloaded!\n", 'success');
            cli_runProcess('php ' . BB_ROOT . 'composer-setup.php --install-dir=' . BB_ROOT);
        } else {
            cli_out('- Cannot download Composer. Please, download it (composer.phar) manually', 'error');
            exit;
        }
        if (is_file(BB_ROOT . 'composer-setup.php')) {
            if (unlink(BB_ROOT . 'composer-setup.php')) {
                cli_out("- Composer installation file successfully removed!\n", 'success');
            } else {
                cli_out('- Cannot remove Composer installation file (composer-setup.php). Please, delete it manually', 'warning');
            }
        }
    }

    // Installing dependencies
    if (is_file(BB_ROOT . 'composer.phar')) {
        cli_out('- Installing dependencies...', 'info');
        cli_runProcess('php ' . BB_ROOT . 'composer.phar install --no-interaction --no-ansi');
        cli_out("- Completed! Composer dependencies are installed!\n", 'success');
    } else {
        cli_out('- composer.phar not found. Please, download it (composer.phar) manually', 'error');
        exit;
    }
} else {
    cli_out('- Composer dependencies are present!', 'success');
    cli_out("- Note: Remove 'vendor' folder if you want to re-install dependencies\n");
}

// Preparing ENV
if (is_file(BB_ROOT . '.env.example') && !is_file(BB_ROOT . '.env')) {
    if (copy(BB_ROOT . '.env.example', BB_ROOT . '.env')) {
        cli_out("- Environment file created!\n", 'success');
    } else {
        cli_out('- Cannot create environment file', 'error');
        exit;
    }
}

// Editing ENV file
$DB_HOST = '';
$DB_PORT = 3306;
$DB_DATABASE = '';
$DB_USERNAME = '';
$DB_PASSWORD = '';

if (is_file(BB_ROOT . '.env')) {
    cli_out("--- Configuring TorrentPier ---", 'info');

    $envContent = file_get_contents(BB_ROOT . '.env');
    if ($envContent === false) {
        cli_out('- Cannot open environment file', 'error');
        exit;
    }
    $envLines = explode("\n", $envContent);

    $editedLines = [];
    foreach ($envLines as $line) {
        if (trim($line) !== '' && !str_starts_with($line, '#')) {
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            // Database default values
            if (in_array($key, ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'])) {
                $$key = $value;
            }

            cli_out("\nCurrent value of $key: $value", 'debug');
            echo "Enter a new value for $key (or leave empty to not change): ";
            $newValue = readline();

            if (!empty($newValue)) {
                $line = "$key=$newValue";
                // Configuring database connection
                if (in_array($key, ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'])) {
                    $$key = $newValue;
                }
            }
        }

        $editedLines[] = $line;
    }

    $newEnvContent = implode("\n", $editedLines);
    if (file_put_contents(BB_ROOT . '.env', $newEnvContent)) {
        cli_out("- TorrentPier successfully configured!\n", 'success');
    } else {
        cli_out('- Cannot save environment file', 'error');
        exit;
    }
} else {
    cli_out('- Environment file not found', 'error');
    exit;
}

if (!empty($DB_HOST) && !empty($DB_DATABASE) && !empty($DB_USERNAME)) {
    cli_out("--- Checking environment settings ---\n", 'info');
    // Connecting to database
    cli_out("- Trying connect to MySQL...", 'info');

    // Checking mysqli extension installed
    if (!extension_loaded('mysqli')) {
        cli_out('- ext-mysqli not found. Check out php.ini file', 'error');
        exit;
    }

    // Connect to MySQL server
    try {
        $conn = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, port: $DB_PORT);
    } catch (mysqli_sql_exception $exception) {
        cli_out("- Connection failed: {$exception->getMessage()}", 'error');
        exit;
    }
    if (!$conn->connect_error) {
        cli_out('- Connected successfully!', 'success');
    }

    // Creating database if not exist
    if ($conn->query("CREATE DATABASE IF NOT EXISTS $DB_DATABASE")) {
        cli_out('- Database created successfully!', 'success');
    } else {
        cli_out("- Cannot create database: $DB_DATABASE", 'error');
        exit;
    }
    $conn->select_db($DB_DATABASE);

    // Checking SQL dump
    $dumpPath = BB_ROOT . 'install/sql/mysql.sql';
    if (is_file($dumpPath) && is_readable($dumpPath)) {
        cli_out('- SQL dump file found and readable!', 'success');
    } else {
        cli_out('- SQL dump file not found / not readable', 'error');
        exit;
    }

    // Inserting SQL dump
    cli_out('- Start importing SQL dump...', 'info');
    $tempLine = '';
    foreach (file($dumpPath) as $line) {
        if (str_starts_with($line, '--') || $line == '') {
            continue;
        }

        $tempLine .= $line;
        if (str_ends_with(trim($line), ';')) {
            if (!$conn->query($tempLine)) {
                cli_out("- Error performing query: $tempLine", 'error');
                exit;
            }
            $tempLine = '';
        }
    }

    $conn->close();
    cli_out("- Importing SQL dump completed!\n", 'success');
    cli_out("- Voila! Good luck & have fun!", 'success');
    rename(__FILE__, __FILE__ . '_' . hash('md5', time()));
}
