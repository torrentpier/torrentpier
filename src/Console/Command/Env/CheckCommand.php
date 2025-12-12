<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Env;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Console\Command\Command;
use TorrentPier\Console\Helpers\OutputHelper;

/**
 * Check environment configuration
 */
#[AsCommand(
    name: 'env:check',
    description: 'Verify environment configuration and show issues'
)]
class CheckCommand extends Command
{
    /**
     * Required environment variables
     */
    private const array REQUIRED_VARS = [
        'APP_ENV' => 'Application environment (development/production)',
        'DB_HOST' => 'Database host',
        'DB_DATABASE' => 'Database name',
        'DB_USERNAME' => 'Database username',
        'TP_HOST' => 'Site hostname',
    ];

    /**
     * Optional but recommended variables
     */
    private const array RECOMMENDED_VARS = [
        'APP_DEBUG_MODE' => ['default' => false, 'description' => 'Enable debug mode'],
        'APP_DEMO_MODE' => ['default' => false, 'description' => 'Enable demo mode'],
        'DB_PORT' => ['default' => 3306, 'description' => 'Database port'],
        'TP_PORT' => ['default' => 443, 'description' => 'Site port'],
    ];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Environment Check');

        $errors = [];
        $warnings = [];

        // Check .env file (optional - env vars can come from system environment)
        $this->section('.env File');
        $envFile = BB_ROOT . '.env';

        if (file_exists($envFile)) {
            $this->line('  <info>✓</info> .env file exists');

            // Check permissions
            $perms = substr(sprintf('%o', fileperms($envFile)), -4);
            if ($perms === '0644' || $perms === '0640' || $perms === '0600') {
                $this->line("  <info>✓</info> Permissions are secure ($perms)");
            } else {
                $warnings[] = ".env has insecure permissions ($perms), recommended: 0644";
                $this->line("  <comment>!</comment> Permissions: $perms (recommended: 0644)");
            }
        } else {
            $this->line('  <comment>-</comment> .env file is not found (using system environment)');
        }

        // Check required variables
        $this->line();
        $this->section('Required Variables');
        foreach (self::REQUIRED_VARS as $var => $description) {
            $value = env($var);

            if ($value === null || $value === '') {
                $errors[] = "$var is not set";
                $this->line("  <error>✗</error> $var - <comment>$description</comment>");
            } else {
                $displayValue = OutputHelper::maskSensitive($var, $value);
                $this->line("  <info>✓</info> $var = $displayValue");
            }
        }

        // Check recommended variables
        $this->line();
        $this->section('Optional Variables');
        foreach (self::RECOMMENDED_VARS as $var => $config) {
            $value = env($var);
            $default = $config['default'];

            if ($value === null) {
                $defaultDisplay = is_bool($default) ? ($default ? 'true' : 'false') : $default;
                $this->line("  <comment>-</comment> $var isn't set (using default: $defaultDisplay)");
            } else {
                $valueDisplay = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                $this->line("  <info>✓</info> $var = $valueDisplay");
            }
        }

        // Check APP_ENV value
        $this->line();
        $this->section('Environment Mode');
        $appEnv = env('APP_ENV');
        if ($appEnv === 'production') {
            $this->line('  <info>✓</info> Running in <info>production</info> mode');
        } elseif ($appEnv === 'development') {
            $this->line('  <comment>!</comment> Running in <comment>development</comment> mode');
            $warnings[] = 'Development mode is enabled - not recommended for production';
        } else {
            $this->line("  <error>!</error> Unknown environment: $appEnv");
            $warnings[] = "Unknown APP_ENV value: $appEnv";
        }

        // Database connection test
        $this->line();
        $this->section('Database Connection');
        try {
            // Test connection using ORM
            $count = DB()->table(BB_CONFIG)->count();
            if ($count >= 0) {
                $this->line('  <info>✓</info> Database connection successful');

                // Show database version
                $version = DB()->fetch_row("SELECT VERSION() as version");
                if ($version) {
                    $this->line("  <info>✓</info> Server version: {$version['version']}");
                }
            }
        } catch (Throwable $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
            $this->line('  <error>✗</error> Database connection failed');
            if ($this->isVerbose()) {
                $this->line("    <error>{$e->getMessage()}</error>");
            }
        }

        // Check writable directories
        $this->line();
        $this->section('Directory Permissions');
        $dirs = [
            'storage/framework/cache' => CACHE_DIR,
            'storage/framework/templates' => TEMPLATES_CACHE_DIR,
            'storage/framework/triggers' => TRIGGERS_DIR,
            'storage/logs' => LOG_DIR,
            'storage/public/avatars' => AVATARS_DIR,
            'storage/public/sitemap' => SITEMAP_DIR,
            'storage/private/uploads' => UPLOADS_DIR,
        ];

        foreach ($dirs as $name => $path) {
            if (!is_dir($path)) {
                $warnings[] = "Directory not found: $name";
                $this->line("  <comment>-</comment> $name (not found)");
            } elseif (!is_writable($path)) {
                $errors[] = "Directory not writable: $name";
                $this->line("  <error>✗</error> $name (not writable)");
            } else {
                $this->line("  <info>✓</info> $name");
            }
        }

        // Summary
        $this->line();
        $this->section('Summary');
        if (empty($errors) && empty($warnings)) {
            $this->success('All checks passed!');
            return self::SUCCESS;
        }

        if (!empty($errors)) {
            $this->error([
                count($errors) . ' error(s):',
                ...array_map(fn($e) => "• $e", $errors),
            ]);
        }

        if (!empty($warnings)) {
            $this->warning([
                count($warnings) . ' warning(s):',
                ...array_map(fn($w) => "• $w", $warnings),
            ]);
        }

        return empty($errors) ? self::SUCCESS : self::FAILURE;
    }
}
