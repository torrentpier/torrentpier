<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Clear Cache Command
 *
 * Clears application cache files
 */
class ClearCacheCommand extends Command
{
    /**
     * The command signature
     */
    protected string $signature = 'cache:clear';

    /**
     * The command description
     */
    protected string $description = 'Clear application cache';

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force clearing cache without confirmation'
        );
    }

    /**
     * Handle the command
     */
    public function handle(): int
    {
        $force = $this->option('force');

        if (!$force && !$this->confirm('Are you sure you want to clear all cache?', true)) {
            $this->info('Cache clear cancelled.');
            return self::SUCCESS;
        }

        $this->info('Clearing application cache...');

        $cleared = 0;

        // Clear file cache
        $cacheDir = $this->app->make('path.base') . '/storage/framework/cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared++;
                }
            }
            $this->line("✓ File cache cleared ({$cleared} files)");
        }

        // Clear view cache
        $viewCacheDir = $this->app->make('path.base') . '/storage/framework/views';
        if (is_dir($viewCacheDir)) {
            $viewFiles = glob($viewCacheDir . '/*');
            $viewCleared = 0;
            foreach ($viewFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $viewCleared++;
                }
            }
            $this->line("✓ View cache cleared ({$viewCleared} files)");
            $cleared += $viewCleared;
        }

        // Clear legacy cache directories
        $legacyCacheDir = $this->app->make('path.base') . '/internal_data/cache';
        if (is_dir($legacyCacheDir)) {
            $legacyCleared = $this->clearDirectoryRecursive($legacyCacheDir);
            $this->line("✓ Legacy cache cleared ({$legacyCleared} files)");
            $cleared += $legacyCleared;
        }

        $this->success("Cache cleared successfully! Total files removed: {$cleared}");

        return self::SUCCESS;
    }

    /**
     * Recursively clear a directory
     */
    private function clearDirectoryRecursive(string $dir): int
    {
        $cleared = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                unlink($file->getRealPath());
                $cleared++;
            }
        }

        return $cleared;
    }
}
