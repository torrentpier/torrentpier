<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Optimize the application for production
 */
#[AsCommand(
    name: 'optimize',
    description: 'Optimize the application (clear caches, optimize autoloader)'
)]
class OptimizeCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('no-cache', null, InputOption::VALUE_NONE, 'Skip cache clearing')
            ->addOption('no-autoload', null, InputOption::VALUE_NONE, 'Skip autoloader optimization');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Optimize Application');

        $skipCache = $input->getOption('no-cache');
        $skipAutoload = $input->getOption('no-autoload');

        $steps = [];

        // Step 1: Clear cache
        if (!$skipCache) {
            $this->section('Clearing Cache');
            $steps['cache'] = $this->clearCache();
        }

        // Step 2: Optimize autoloader
        if (!$skipAutoload) {
            $this->section('Optimizing Autoloader');
            $steps['autoload'] = $this->optimizeAutoloader();
        }

        // Step 3: Clear OPCache if available
        $this->section('OPCache');
        $steps['opcache'] = $this->clearOpcache();

        // Summary
        $this->line('');
        $this->section('Summary');

        $allSuccess = true;
        foreach ($steps as $step => $success) {
            $status = $success ? '<info>✓</info>' : '<error>✗</error>';
            $this->line("  {$status} " . ucfirst($step));
            if (!$success) {
                $allSuccess = false;
            }
        }

        $this->line('');

        if ($allSuccess) {
            $this->success('Application optimized successfully!');
        } else {
            $this->warning('Optimization completed with some issues.');
        }

        return $allSuccess ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Clear application cache
     */
    private function clearCache(): bool
    {
        $cacheDir = CACHE_DIR;

        if (!is_dir($cacheDir)) {
            $this->line('  <comment>-</comment> Cache directory not found');
            return true;
        }

        try {
            $count = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    @rmdir($item->getPathname());
                } else {
                    if (@unlink($item->getPathname())) {
                        $count++;
                    }
                }
            }

            $this->line("  <info>✓</info> Cleared {$count} cache file(s)");

            // Clear runtime cache
            if (function_exists('CACHE')) {
                try {
                    CACHE('bb_cache')->clean();
                    $this->line('  <info>✓</info> Cleared runtime cache');
                } catch (\Throwable) {
                    // Ignore
                }
            }

            return true;
        } catch (\Throwable $e) {
            $this->line('  <error>✗</error> Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimize Composer autoloader
     */
    private function optimizeAutoloader(): bool
    {
        $composerPaths = [
            BB_ROOT . 'composer.phar',
            'composer', // Global composer
        ];

        $composerCmd = null;
        foreach ($composerPaths as $path) {
            if ($path === 'composer') {
                exec('composer --version 2>&1', $output, $code);
                if ($code === 0) {
                    $composerCmd = 'composer';
                    break;
                }
            } elseif (file_exists($path)) {
                $composerCmd = 'php ' . escapeshellarg($path);
                break;
            }
        }

        if (!$composerCmd) {
            $this->line('  <comment>-</comment> Composer not found, skipping');
            return true;
        }

        $cmd = $composerCmd . ' dump-autoload --optimize --no-interaction 2>&1';

        exec($cmd, $output, $exitCode);

        if ($exitCode === 0) {
            $this->line('  <info>✓</info> Autoloader optimized');
            return true;
        }

        $this->line('  <error>✗</error> Failed to optimize autoloader');
        if ($this->isVerbose()) {
            foreach ($output as $line) {
                $this->line("    {$line}");
            }
        }

        return false;
    }

    /**
     * Clear OPCache
     */
    private function clearOpcache(): bool
    {
        if (!function_exists('opcache_reset')) {
            $this->line('  <comment>-</comment> OPCache not available');
            return true;
        }

        if (!ini_get('opcache.enable_cli')) {
            $this->line('  <comment>-</comment> OPCache disabled for CLI');
            return true;
        }

        if (@opcache_reset()) {
            $this->line('  <info>✓</info> OPCache cleared');
            return true;
        }

        $this->line('  <comment>-</comment> Could not clear OPCache');
        return true; // Not a critical failure
    }
}

