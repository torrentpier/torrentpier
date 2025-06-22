<?php

declare(strict_types=1);

namespace App\Console\Commands;

/**
 * Info Command
 *
 * Display TorrentPier system information
 */
class InfoCommand extends Command
{
    /**
     * The command signature
     */
    protected string $signature = 'info';

    /**
     * The command description
     */
    protected string $description = 'Display system information';

    /**
     * Handle the command
     */
    public function handle(): int
    {
        $basePath = $this->app->make('path.base');

        $this->line('');
        $this->line('<fg=cyan>TorrentPier System Information</>');
        $this->line('<fg=cyan>==============================</>');
        $this->line('');

        // Application info
        $this->line('<fg=yellow>Application:</>');
        $this->line('  Name: TorrentPier');
        $this->line('  Version: 3.0-dev');
        $this->line('  Environment: ' . ($_ENV['APP_ENV'] ?? 'production'));
        $this->line('  Debug Mode: ' . (($_ENV['APP_DEBUG'] ?? false) ? 'enabled' : 'disabled'));
        $this->line('');

        // Paths
        $this->line('<fg=yellow>Paths:</>');
        $this->line('  Base: ' . $basePath);
        $this->line('  App: ' . $this->app->make('path.app'));
        $this->line('  Config: ' . $this->app->make('path.config'));
        $this->line('  Storage: ' . $this->app->make('path.storage'));
        $this->line('  Public: ' . $this->app->make('path.public'));
        $this->line('');

        // PHP info
        $this->line('<fg=yellow>PHP:</>');
        $this->line('  Version: ' . PHP_VERSION);
        $this->line('  SAPI: ' . PHP_SAPI);
        $this->line('  Memory Limit: ' . ini_get('memory_limit'));
        $this->line('  Max Execution Time: ' . ini_get('max_execution_time') . 's');
        $this->line('');

        // Extensions
        $requiredExtensions = ['pdo', 'curl', 'gd', 'mbstring', 'openssl', 'zip'];
        $this->line('<fg=yellow>Required Extensions:</>');
        foreach ($requiredExtensions as $ext) {
            $status = extension_loaded($ext) ? '<fg=green>✓</>' : '<fg=red>✗</>';
            $this->line("  {$status} {$ext}");
        }
        $this->line('');

        // File permissions
        $this->line('<fg=yellow>File Permissions:</>');
        $writablePaths = [
            'storage',
            'storage/app',
            'storage/framework',
            'storage/logs',
            'internal_data/cache',
            'data/uploads'
        ];

        foreach ($writablePaths as $path) {
            $fullPath = $basePath . '/' . $path;
            if (file_exists($fullPath)) {
                $writable = is_writable($fullPath);
                $status = $writable ? '<fg=green>✓</>' : '<fg=red>✗</>';
                $this->line("  {$status} {$path}");
            } else {
                $this->line("  <fg=yellow>?</> {$path} (not found)");
            }
        }
        $this->line('');

        // Database
        try {
            if ($this->app->bound('config')) {
                $config = $this->app->make('config');
                $dbConfig = $config->get('database', []);

                if (!empty($dbConfig)) {
                    $this->line('<fg=yellow>Database:</>');
                    $this->line('  Host: ' . ($dbConfig['host'] ?? 'not configured'));
                    $this->line('  Database: ' . ($dbConfig['dbname'] ?? 'not configured'));
                    $this->line('  Driver: ' . ($dbConfig['driver'] ?? 'not configured'));
                }
            }
        } catch (\Exception $e) {
            $this->line('<fg=yellow>Database:</> Configuration error');
        }

        $this->line('');
        return self::SUCCESS;
    }
}
