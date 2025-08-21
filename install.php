<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('BB_PATH', BB_ROOT);

// Check CLI mode
if (PHP_SAPI != 'cli') {
    die('Please run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php ' . basename(__FILE__) . '</code> in CLI mode');
}

// Get all constants
require_once BB_ROOT . 'library/defines.php';

// Include CLI functions
require INC_DIR . '/functions_cli.php';

/**
 * System requirements
 */
const CHECK_REQUIREMENTS = [
    'php_min_version' => '8.2.0',
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
        'zip',
        'gd'
    ],
];

// Welcoming message
out("--- TorrentPier Installer ---\n", 'info');

// Checking extensions
out("- Checking installed extensions...", 'info');

// [1] Check PHP Version
if (!version_compare(PHP_VERSION, CHECK_REQUIREMENTS['php_min_version'], '>=')) {
    out("- TorrentPier requires PHP version " . CHECK_REQUIREMENTS['php_min_version'] . "+ Your PHP version " . PHP_VERSION, 'warning');
}

// [2] Check installed PHP Extensions on server
foreach (CHECK_REQUIREMENTS['ext_list'] as $ext) {
    if (!extension_loaded($ext)) {
        out("- ext-$ext not installed. Check out php.ini file", 'error');
        if (!defined('EXTENSIONS_NOT_INSTALLED')) {
            define('EXTENSIONS_NOT_INSTALLED', true);
        }
    } else {
        out("- ext-$ext installed!");
    }
}
if (!defined('EXTENSIONS_NOT_INSTALLED')) {
    out("- All extensions are installed!\n", 'success');
} else {
    exit;
}

// Check if already installed
if (is_file(BB_ROOT . '.env')) {
    out('- TorrentPier already installed', 'warning');
    echo 'Are you sure want to re-install TorrentPier? [y/N]: ';
    if (str_starts_with(mb_strtolower(trim(readline())), 'y')) {
        out("\n- Re-install process started...", 'info');
        // environment
        if (is_file(BB_ROOT . '.env')) {
            if (unlink(BB_ROOT . '.env')) {
                out('- Environment file successfully removed!');
            } else {
                out('- Cannot remove environment (.env) file. Delete it manually', 'error');
                exit;
            }
        }
        // composer.phar
        if (is_file(BB_ROOT . 'composer.phar')) {
            if (unlink(BB_ROOT . 'composer.phar')) {
                out("- composer.phar file successfully removed!");
            } else {
                out('- Cannot remove composer.phar file. Delete it manually', 'error');
                exit;
            }
        }
        // composer dir
        if (is_dir(BB_ROOT . 'vendor')) {
            removeDir(BB_ROOT . 'vendor', true);
            if (!is_dir(BB_ROOT . 'vendor')) {
                out("- Composer directory successfully removed!");
            } else {
                out('- Cannot remove Composer directory. Delete it manually', 'error');
                exit;
            }
        }
        out("- Re-install process completed!\n", 'success');
        out('- Starting installation...', 'info');
    } else {
        exit;
    }
}

// Applying permissions
out("- Applying permissions for folders...", 'info');
chmod_r(BB_ROOT . 'data', 0755, 0644);
chmod_r(BB_ROOT . 'internal_data', 0755, 0644);
chmod_r(BB_ROOT . 'sitemap', 0755, 0644);
out("- Permissions successfully applied!\n", 'success');

// Check composer installation
if (!is_file(BB_ROOT . 'vendor/autoload.php')) {
    out('- Hmm, it seems there are no Composer dependencies', 'info');

    // Downloading composer
    if (!is_file(BB_ROOT . 'composer.phar')) {
        out('- Downloading Composer...', 'info');
        if (copy('https://getcomposer.org/installer', BB_ROOT . 'composer-setup.php')) {
            out("- Composer successfully downloaded!\n", 'success');
            runProcess('php ' . BB_ROOT . 'composer-setup.php --install-dir=' . BB_ROOT);
        } else {
            out('- Cannot download Composer. Please, download it (composer.phar) manually', 'error');
            exit;
        }
        if (is_file(BB_ROOT . 'composer-setup.php')) {
            if (unlink(BB_ROOT . 'composer-setup.php')) {
                out("- Composer installation file successfully removed!\n", 'success');
            } else {
                out('- Cannot remove Composer installation file (composer-setup.php). Please, delete it manually', 'warning');
            }
        }
    } else {
        out("- composer.phar file found!\n", 'success');
    }

    // Installing dependencies
    if (is_file(BB_ROOT . 'composer.phar')) {
        out('- Installing dependencies...', 'info');

        runProcess('php ' . BB_ROOT . 'composer.phar install --no-interaction --no-ansi');
        define('COMPOSER_COMPLETED', true);
    } else {
        out('- composer.phar not found. Please, download it (composer.phar) manually', 'error');
        exit;
    }
} else {
    out('- Composer dependencies are present!', 'success');
    out("- Note: Remove 'vendor' folder if you want to re-install dependencies\n");
}

// Check composer dependencies
if (defined('COMPOSER_COMPLETED')) {
    if (is_file(BB_ROOT . 'vendor/autoload.php')) {
        out("- Completed! Composer dependencies are installed!\n", 'success');
    } else {
        exit;
    }
}

// Preparing ENV
if (is_file(BB_ROOT . '.env.example') && !is_file(BB_ROOT . '.env')) {
    if (copy(BB_ROOT . '.env.example', BB_ROOT . '.env')) {
        out("- Environment file created!\n", 'success');
    } else {
        out('- Cannot create environment file', 'error');
        exit;
    }
}

// Editing ENV file
$DB_HOST = 'localhost';
$DB_PORT = 3306;
$DB_DATABASE = '';
$DB_USERNAME = '';
$DB_PASSWORD = '';

if (is_file(BB_ROOT . '.env')) {
    out("--- Configuring TorrentPier ---", 'info');

    $envContent = file_get_contents(BB_ROOT . '.env');
    if ($envContent === false) {
        out('- Cannot open environment file', 'error');
        exit;
    }
    $envLines = explode("\n", $envContent);

    $editedLines = [];
    foreach ($envLines as $line) {
        if (trim($line) !== '' && !str_starts_with($line, '#')) {
            $parts = explode('=', $line, 2);
            $key = trim($parts[0]);
            $value = (!empty($parts[1]) && $key !== 'DB_PASSWORD') ? trim($parts[1]) : '';

            out("\nCurrent value of $key: $value", 'debug');
            echo "Enter a new value for $key (or leave empty to not change): ";
            $newValue = trim(readline());

            if (!empty($newValue) || $key === 'DB_PASSWORD') {
                if ($key === 'TP_HOST') {
                    if (!preg_match('/^https?:\/\//', $newValue)) {
                        $newValue = 'https://' . $newValue;
                    }
                    $newValue = parse_url($newValue, PHP_URL_HOST);
                }
                $line = "$key=$newValue";
                $$key = $newValue;
            } else {
                $$key = $value;
            }
        }

        $editedLines[] = $line;
    }

    $newEnvContent = implode("\n", $editedLines);
    if (file_put_contents(BB_ROOT . '.env', $newEnvContent)) {
        out("- TorrentPier successfully configured!\n", 'success');
    } else {
        out('- Cannot save environment file', 'error');
        exit;
    }
} else {
    out('- Environment file not found', 'error');
    exit;
}

if (!empty($DB_HOST) && !empty($DB_DATABASE) && !empty($DB_USERNAME)) {
    out("--- Checking environment settings ---\n", 'info');
    // Connecting to database
    out("- Trying connect to MySQL...", 'info');

    // Checking mysqli extension installed
    if (!extension_loaded('mysqli')) {
        out('- ext-mysqli not found. Check out php.ini file', 'error');
        exit;
    }

    // Connect to MySQL server
    try {
        $conn = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, port: $DB_PORT);
    } catch (mysqli_sql_exception $exception) {
        out("- Connection failed: {$exception->getMessage()}", 'error');
        exit;
    }
    if (!$conn->connect_error) {
        out('- Connected successfully!', 'success');
    }

    // Creating database if not exist
    if ($conn->query("CREATE DATABASE IF NOT EXISTS $DB_DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        out('- Database created successfully!', 'success');
    } else {
        out("- Cannot create database: $DB_DATABASE", 'error');
        exit;
    }
    $conn->select_db($DB_DATABASE);

    // Close database connection - migrations will handle their own connections
    $conn->close();

    // Run database migrations
    out('- Setting up database using migrations...', 'info');

    // Check if phinx.php exists
    if (!is_file(BB_ROOT . 'phinx.php')) {
        out('- Migration configuration (phinx.php) not found', 'error');
        exit;
    }

    // Run migrations
    $migrationResult = runProcess('php vendor/bin/phinx migrate --configuration=' . BB_ROOT . 'phinx.php');
    if ($migrationResult !== 0) {
        out('- Database migration failed', 'error');
        exit;
    }

    out("- Database setup completed!\n", 'success');

    // Autofill host in robots.txt
    $robots_txt_file = BB_ROOT . 'robots.txt';
    if (isset($TP_HOST) && is_file($robots_txt_file)) {
        $content = file_get_contents($robots_txt_file);
        $content = str_replace('example.com', $TP_HOST, $content);
        file_put_contents($robots_txt_file, $content);
    }

    if (isset($APP_ENV) && $APP_ENV === 'local') {
        if (!is_file(BB_ROOT . 'library/config.local.php')) {
            if (copy(BB_ROOT . 'library/config.php', BB_ROOT . 'library/config.local.php')) {
                out('- Local configuration file created!', 'success');
            } else {
                out('- Cannot create library/config.local.php file. You can create it manually, just copy config.php and rename it to config.local.php', 'warning');
            }
        }
    } else {
        if (rename(__FILE__, __FILE__ . '_' . hash('xxh128', time()))) {
            out("- Installation file renamed!", 'success');
        } else {
            out('- Cannot rename installation file (' . __FILE__ . '). Please, rename it manually for security reasons', 'warning');
        }
    }

    // Cleanup...
    if (is_file(BB_ROOT . '_cleanup.php')) {
        out("\n--- Finishing installation (Cleanup) ---\n", 'info');
        out('The cleanup process will remove:');
        out('- Development documentation (README, CHANGELOG)', 'debug');
        out('- Git configuration files', 'debug');
        out('- CI/CD pipelines and code analysis tools', 'debug');
        out('- Translation and contribution guidelines', 'debug');
        echo 'Do you want to delete these files permanently? [y/N]: ';
        if (str_starts_with(mb_strtolower(trim(readline())), 'y')) {
            out("\n- Cleanup...", 'info');
            require_once BB_ROOT . '_cleanup.php';
            unlink(BB_ROOT . '_cleanup.php');
        } else {
            out('- Skipping...', 'info');
        }
    }

    out("\n- Voila! Good luck & have fun!", 'success');
}
