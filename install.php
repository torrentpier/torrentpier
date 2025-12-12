<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Installation Bootstrap
 * This script ensures Composer dependencies are installed, then delegates
 * to the Bull CLI installation wizard for the actual setup.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

// ==============================================================================
// BOOTSTRAP - Runs BEFORE Composer is available
// ==============================================================================

define('BB_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('BB_PATH', BB_ROOT);

// Check CLI mode
if (PHP_SAPI !== 'cli') {
    die('Please run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">php ' . basename(__FILE__) . '</code> in CLI mode');
}

/**
 * Colored console output (standalone, no dependencies)
 */
function out(string $message, string $type = ''): void
{
    $colors = [
        'error' => "\033[31m",
        'success' => "\033[32m",
        'warning' => "\033[33m",
        'info' => "\033[36m",
        'debug' => "\033[90m",
    ];
    $reset = "\033[0m";

    $prefix = isset($colors[$type]) ? $colors[$type] : '';
    echo $prefix . $message . ($prefix ? $reset : '') . PHP_EOL;
}

/**
 * Run a shell command with output
 */
function runCommand(string $cmd): int
{
    passthru($cmd, $exitCode);
    return $exitCode;
}

// ==============================================================================
// HEADER
// ==============================================================================

echo PHP_EOL;
out('╔══════════════════════════════════════════════════════════╗', 'info');
out('║                                                          ║', 'info');
out('║           TorrentPier Installation Bootstrap             ║', 'info');
out('║                                                          ║', 'info');
out('╚══════════════════════════════════════════════════════════╝', 'info');
echo PHP_EOL;

// ==============================================================================
// STEP 1: Check PHP Version
// ==============================================================================

const PHP_MIN_VERSION = '8.4.0';

out('Step 1: Checking PHP version...', 'info');

if (!version_compare(PHP_VERSION, PHP_MIN_VERSION, '>=')) {
    out("  ✗ PHP " . PHP_MIN_VERSION . "+ required, you have " . PHP_VERSION, 'error');
    exit(1);
}

out("  ✓ PHP " . PHP_VERSION, 'success');
echo PHP_EOL;

// ==============================================================================
// STEP 2: Check Critical Extensions
// ==============================================================================

out('Step 2: Checking critical extensions...', 'info');

$criticalExtensions = ['json', 'curl', 'mbstring', 'pdo'];
$missing = [];

foreach ($criticalExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing[] = $ext;
        out("  ✗ ext-{$ext} not installed", 'error');
    } else {
        out("  ✓ ext-{$ext}");
    }
}

if (!empty($missing)) {
    out(PHP_EOL . 'Please install missing extensions and try again.', 'error');
    exit(1);
}

echo PHP_EOL;

// ==============================================================================
// STEP 3: Ensure Composer Dependencies
// ==============================================================================

out('Step 3: Checking Composer dependencies...', 'info');

if (file_exists(BB_ROOT . 'vendor/autoload.php')) {
    out('  ✓ Dependencies already installed', 'success');
} else {
    out('  ! Dependencies not found, installing...', 'warning');
    echo PHP_EOL;

    // Check for composer.phar or global composer
    $composerCmd = null;

    if (file_exists(BB_ROOT . 'composer.phar')) {
        $composerCmd = 'php ' . BB_ROOT . 'composer.phar';
        out('  Using local composer.phar');
    } else {
        // Try global composer
        exec('composer --version 2>&1', $output, $returnCode);
        if ($returnCode === 0) {
            $composerCmd = 'composer';
            out('  Using global Composer');
        }
    }

    if ($composerCmd === null) {
        // Download composer
        out('  Downloading Composer...', 'info');

        $installerPath = BB_ROOT . 'composer-setup.php';
        $pharPath = BB_ROOT . 'composer.phar';

        if (!@copy('https://getcomposer.org/installer', $installerPath)) {
            out('  ✗ Failed to download Composer installer', 'error');
            out('  Please install Composer manually: https://getcomposer.org', 'info');
            exit(1);
        }

        $result = runCommand('php ' . $installerPath . ' --install-dir=' . BB_ROOT);

        // Cleanup installer
        @unlink($installerPath);

        if ($result !== 0 || !file_exists($pharPath)) {
            out('  ✗ Failed to install Composer', 'error');
            exit(1);
        }

        out('  ✓ Composer installed', 'success');
        $composerCmd = 'php ' . $pharPath;
    }

    // Install dependencies
    echo PHP_EOL;
    out('  Installing dependencies (this may take a few minutes)...', 'info');
    echo PHP_EOL;

    $result = runCommand($composerCmd . ' install --no-interaction --prefer-dist --optimize-autoloader');

    if ($result !== 0) {
        out('  ✗ Failed to install dependencies', 'error');
        exit(1);
    }

    echo PHP_EOL;
    out('  ✓ Dependencies installed successfully!', 'success');
}

// Verify autoload exists
if (!file_exists(BB_ROOT . 'vendor/autoload.php')) {
    out('  ✗ vendor/autoload.php not found after installation', 'error');
    exit(1);
}

echo PHP_EOL;

// ==============================================================================
// STEP 4: Hand off to Bull CLI
// ==============================================================================

out('Step 4: Starting installation wizard...', 'info');
echo PHP_EOL;

// Now we can use Bull CLI for the actual installation
$bullPath = BB_ROOT . 'bull';

if (!file_exists($bullPath)) {
    out('  ✗ Bull CLI not found at: ' . $bullPath, 'error');
    exit(1);
}

// Execute Bull CLI install command
$exitCode = runCommand('php ' . $bullPath . ' app:install --skip-composer');

exit($exitCode);
