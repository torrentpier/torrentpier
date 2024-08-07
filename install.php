<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('ROOT', __DIR__ . '/');

// Get arguments
if (isset($argv[1])) {
    parse_str($argv[1], $arg);
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
        copy('https://getcomposer.org/installer', ROOT . 'composer-setup.php');
        out("- Composer successfully downloaded!\n", 'success');
        runProcess('php ' . ROOT . 'composer-setup.php');
        if (is_file(ROOT . 'composer-setup.php')) {
            unlink(ROOT . 'composer-setup.php');
            out("- Composer installation file successfully removed!\n", 'success');
        }
    }
    // Installing dependencies
    if (!is_file(ROOT . 'composer.phar')) {
        out('- composer.phar not found', 'error');
        exit;
    }
    out('- Installing dependencies...', 'info');
    runProcess('php ' . ROOT . 'composer.phar install --no-interaction --no-ansi');
    out("- Completed!\n", 'success');
}

// Preparing ENV
if (is_file(ROOT . '.env.example') && !is_file(ROOT . '.env')) {
    if (copy(ROOT . '.env.example', ROOT . '.env')) {
        out("- Environment file created!\n", 'success');
    } else {
        out('- Cannot copy environment file', 'error');
        exit;
    }
}

// Editing ENV file
$dbHost = '';
$dbUser = '';
$dbPassword = '';
$dbName = '';

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

            out("Current value of $key: $value", 'debug');
            out("Enter a new value for $key (or leave empty to not change): ", 'default');
            $newValue = readline();

            if (!empty($newValue)) {
                $line = "$key=$newValue";
                if ($key === 'DB_HOST') {
                    $dbHost = $newValue;
                } elseif ($key === 'DB_USER') {
                    $dbUser = $newValue;
                } elseif ($key === 'DB_PASSWORD') {
                    $dbPassword = $newValue;
                } elseif ($key === 'DB_NAME') {
                    $dbName = $newValue;
                }
            }

            $editedLines[] = $line;
        }
    }

    $newEnvContent = implode("\n", $editedLines);
    if (file_put_contents($envFile, $newEnvContent)) {
        out('- TorrentPier successfully configured!', 'success');
    }
} else {
    out('- Environment file not found', 'error');
    exit;
}
