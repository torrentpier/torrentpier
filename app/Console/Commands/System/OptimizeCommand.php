<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\System;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Commands\Command;

/**
 * Optimize the application for production
 */
#[AsCommand(
    name: 'optimize',
    description: 'Optimize the application (autoloader, OPCache)',
)]
class OptimizeCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Optimize Application');

        $steps = [];

        // Step 1: Optimize autoloader
        $this->section('Optimizing Autoloader');
        $steps['autoload'] = $this->optimizeAutoloader();

        // Step 2: Clear OPCache if available
        $this->section('OPCache');
        $steps['opcache'] = $this->clearOpcache();

        // Summary
        $this->line();
        $this->section('Summary');

        $allSuccess = true;
        foreach ($steps as $step => $success) {
            $status = $success ? '<info>✓</info>' : '<error>✗</error>';
            $this->line("  {$status} " . ucfirst($step));
            if (!$success) {
                $allSuccess = false;
            }
        }

        $this->line();

        if ($allSuccess) {
            $this->success('Application optimized successfully!');
        } else {
            $this->warning('Optimization completed with some issues.');
        }

        return $allSuccess ? self::SUCCESS : self::FAILURE;
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
        if (!\function_exists('opcache_reset')) {
            $this->line('  <comment>-</comment> OPCache not available');

            return true;
        }

        if (!\ini_get('opcache.enable_cli')) {
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
