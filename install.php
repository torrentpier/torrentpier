<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('ROOT', __DIR__ . '/');

// Check CLI mode
if (php_sapi_name() !== 'cli') {
    die('Please run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php ' . basename(__FILE__) . '</code> in CLI mode');
}

// Check if already installed
if (is_file(ROOT . '.env')) {
    exit;
}

// Check readline extension
if (!extension_loaded('readline')) {
    out('- ext-readline not found. Check out PHP instance', 'error');
    exit;
}

/**
 * Colored console output
 *
 * @param string $str
 * @param string $type
 * @return void
 */
function out(string $str, string $type): void
{
    echo match ($type) {
        'error' => "\033[31m$str \033[0m\n",
        'success' => "\033[32m$str \033[0m\n",
        'warning' => "\033[33m$str \033[0m\n",
        'info' => "\033[36m$str \033[0m\n",
        'debug' => "\033[90m$str \033[0m\n",
        default => $str,
    };
}

/**
 * Run process with realtime output
 *
 * @param string $cmd
 * @param string|null $input
 * @return void
 */
function runProcess(string $cmd, string $input = null): void
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($cmd, $descriptorSpec, $pipes);

    if (!is_resource($process)) {
        out('- Could not start subprocess', 'error');
        return;
    }

    // Write input if provided
    if ($input !== null) {
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
    }

    // Read and print output in real-time
    while (!feof($pipes[1])) {
        echo stream_get_contents($pipes[1], 1);
        flush(); // Flush output buffer for immediate display
    }

    // Read and print error output
    while (!feof($pipes[2])) {
        echo stream_get_contents($pipes[2], 1);
        flush();
    }

    fclose($pipes[1]);
    fclose($pipes[2]);

    proc_close($process);
}

// Welcoming message
out("- TorrentPier Installer\n", 'info');

// Check composer installation
if (!is_file(ROOT . 'vendor/autoload.php')) {
    out('- Hmm, it seems there are no Composer dependencies', 'info');

    // Downloading composer
    if (!is_file(ROOT . 'composer.phar')) {
        out('- Downloading Composer...', 'info');
        if (copy('https://getcomposer.org/installer', ROOT . 'composer-setup.php')) {
            out("- Composer successfully downloaded!\n", 'success');
            runProcess('php ' . ROOT . 'composer-setup.php');
        } else {
            out('- Cannot download Composer', 'error');
            exit;
        }
        if (is_file(ROOT . 'composer-setup.php')) {
            if (unlink(ROOT . 'composer-setup.php')) {
                out("- Composer installation file successfully removed!\n", 'success');
            } else {
                out('- Cannot remove Composer installation file. Delete it manually', 'warning');
            }
        }
    }

    // Installing dependencies
    if (is_file(ROOT . 'composer.phar')) {
        out('- Installing dependencies...', 'info');
        runProcess('php ' . ROOT . 'composer.phar install --no-interaction --no-ansi');
        out("- Completed!\n", 'success');
    } else {
        out('- composer.phar not found', 'error');
        exit;
    }
}

// Preparing ENV
if (is_file(ROOT . '.env.example') && !is_file(ROOT . '.env')) {
    if (copy(ROOT . '.env.example', ROOT . '.env')) {
        out("- Environment file created!\n", 'success');
    } else {
        out('- Cannot create environment file', 'error');
        exit;
    }
}

// Editing ENV file
$DB_HOST = '';
$DB_PORT = 3306;
$DB_DATABASE = '';
$DB_USERNAME = '';
$DB_PASSWORD = '';

if (is_file(ROOT . '.env')) {
    out("--- Configuring TorrentPier ---\n", 'info');

    $envFile = ROOT . '.env';
    $envContent = file_get_contents($envFile);
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

            out("Current value of $key: $value", 'debug');
            out("Enter a new value for $key (or leave empty to not change): ", 'default');
            $newValue = readline();

            if (!empty($newValue)) {
                $line = "$key=$newValue";
                // Configuring database connection
                if (in_array($key, ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'])) {
                    $$key = $newValue;
                }
            }

            $editedLines[] = $line;
        }
    }

    $newEnvContent = implode("\n", $editedLines);
    if (file_put_contents($envFile, $newEnvContent)) {
        out("- TorrentPier successfully configured!\n", 'success');
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
    if ($conn->query("CREATE DATABASE IF NOT EXISTS $DB_DATABASE")) {
        out('- Database created successfully!', 'success');
    } else {
        out("- Cannot create database: $DB_DATABASE", 'error');
        exit;
    }
    $conn->select_db($DB_DATABASE);

    // Checking SQL dump
    $dumpPath = ROOT . 'install/sql/mysql.sql';
    if (is_file($dumpPath) && is_readable($dumpPath)) {
        out('- SQL dump file found and readable!', 'success');
    } else {
        out('- SQL dump file not found / not readable', 'error');
        exit;
    }

    // Inserting SQL dump
    out('- Start importing SQL dump...', 'info');
    $tempLine = '';
    foreach (file($dumpPath) as $line) {
        if (str_starts_with($line, '--') || $line == '') {
            continue;
        }

        $tempLine .= $line;
        if (str_ends_with(trim($line), ';')) {
            if (!$conn->query($tempLine)) {
                out("- Error performing query: $tempLine", 'error');
                exit;
            }
            $tempLine = '';
        }
    }

    $conn->close();
    out("- Importing SQL dump completed!\n", 'success');
    out("- Voila! Good luck & have fun!", 'success');
}
