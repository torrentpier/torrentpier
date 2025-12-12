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
        'DB_PORT' => ['default' => '3306', 'description' => 'Database port'],
    ];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Environment Check');

        $errors = [];
        $warnings = [];

        // Check .env file exists
        $this->section('.env File');
        $envFile = BB_ROOT . '.env';

        if (file_exists($envFile)) {
            $this->line('  <info>✓</info> .env file exists');

            // Check permissions
            $perms = substr(sprintf('%o', fileperms($envFile)), -4);
            if ($perms === '0644' || $perms === '0640' || $perms === '0600') {
                $this->line("  <info>✓</info> Permissions are secure ({$perms})");
            } else {
                $warnings[] = ".env has insecure permissions ({$perms}), recommended: 0644";
                $this->line("  <comment>!</comment> Permissions: {$perms} (recommended: 0644)");
            }
        } else {
            $errors[] = '.env file not found';
            $this->line('  <error>✗</error> .env file not found');
        }

        // Check required variables
        $this->section('Required Variables');

        foreach (self::REQUIRED_VARS as $var => $description) {
            $value = env($var);

            if (empty($value)) {
                $errors[] = "{$var} is not set";
                $this->line("  <error>✗</error> {$var} - <comment>{$description}</comment>");
            } else {
                $displayValue = $this->maskSensitive($var, $value);
                $this->line("  <info>✓</info> {$var} = {$displayValue}");
            }
        }

        // Check recommended variables
        $this->section('Optional Variables');

        foreach (self::RECOMMENDED_VARS as $var => $config) {
            $value = env($var);
            $default = $config['default'];

            if (empty($value)) {
                $this->line("  <comment>-</comment> {$var} not set (using default: {$default})");
            } else {
                $this->line("  <info>✓</info> {$var} = {$value}");
            }
        }

        // Check APP_ENV value
        $this->section('Environment Mode');

        $appEnv = env('APP_ENV', 'production');
        if ($appEnv === 'production') {
            $this->line('  <info>✓</info> Running in <info>production</info> mode');
        } elseif ($appEnv === 'development') {
            $this->line('  <comment>!</comment> Running in <comment>development</comment> mode');
            $warnings[] = 'Development mode is enabled - not recommended for production';
        } else {
            $this->line("  <error>!</error> Unknown environment: {$appEnv}");
            $warnings[] = "Unknown APP_ENV value: {$appEnv}";
        }

        // Database connection test
        $this->section('Database Connection');

        try {
            // Test connection using ORM
            $count = DB()->table(BB_CONFIG)->count();
            if ($count >= 0) {
                $this->line('  <info>✓</info> Database connection successful');

                // Show database info using raw query for VERSION()
                $version = DB()->fetch_row("SELECT VERSION() as version");
                if ($version) {
                    $this->line("  <info>✓</info> MySQL version: {$version['version']}");
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
        $this->section('Directory Permissions');

        $dirs = [
            'internal_data/cache' => CACHE_DIR,
            'internal_data/log' => LOG_DIR,
            'internal_data/triggers' => TRIGGERS_DIR,
            'data/avatars' => DATA_DIR . '/avatars',
            'data/uploads' => DATA_DIR . '/uploads',
            'sitemap' => SITEMAP_DIR,
        ];

        foreach ($dirs as $name => $path) {
            if (!is_dir($path)) {
                $warnings[] = "Directory not found: {$name}";
                $this->line("  <comment>-</comment> {$name} (not found)");
            } elseif (!is_writable($path)) {
                $errors[] = "Directory not writable: {$name}";
                $this->line("  <error>✗</error> {$name} (not writable)");
            } else {
                $this->line("  <info>✓</info> {$name}");
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
            $this->error(count($errors) . ' error(s) found:');
            foreach ($errors as $error) {
                $this->line("  • {$error}");
            }
        }

        if (!empty($warnings)) {
            $this->line();
            $this->warning(count($warnings) . ' warning(s):');
            foreach ($warnings as $warning) {
                $this->line("  • {$warning}");
            }
        }

        return empty($errors) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Mask sensitive values for display
     */
    private function maskSensitive(string $var, string $value): string
    {
        $sensitive = ['PASSWORD', 'SECRET', 'KEY', 'TOKEN'];

        foreach ($sensitive as $keyword) {
            if (stripos($var, $keyword) !== false) {
                if (strlen($value) <= 4) {
                    return '****';
                }
                return substr($value, 0, 2) . str_repeat('*', strlen($value) - 4) . substr($value, -2);
            }
        }

        return $value;
    }
}

