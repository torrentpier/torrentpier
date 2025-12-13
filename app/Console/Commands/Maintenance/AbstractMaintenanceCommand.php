<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Maintenance;

use Symfony\Component\Console\Input\InputOption;
use TorrentPier\Console\Commands\Command;

/**
 * Abstract base class for maintenance commands
 *
 * Provides common functionality for maintenance operations:
 * - Force mode (skip confirmation)
 * - Dry-run mode
 * - Confirmation prompts for destructive operations
 */
abstract class AbstractMaintenanceCommand extends Command
{
    /**
     * Configure common options for all maintenance commands
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Skip confirmation prompts',
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be done without making changes',
            );
    }

    /**
     * Check if force mode is enabled
     */
    protected function isForce(): bool
    {
        return (bool)$this->input->getOption('force');
    }

    /**
     * Check if dry-run mode is enabled
     */
    protected function isDryRun(): bool
    {
        return (bool)$this->input->getOption('dry-run');
    }

    /**
     * Display dry-run notice
     */
    protected function displayDryRunNotice(): void
    {
        $this->warning([
            'DRY-RUN MODE',
            'No changes will be made to the database.',
        ]);
        $this->line();
    }

    /**
     * Confirm a destructive operation
     *
     * @param string $message Warning message to display
     * @param string $question Confirmation question
     * @return bool True if confirmed, false otherwise
     */
    protected function confirmDestructive(string $message, string $question = 'Continue?'): bool
    {
        if ($this->isForce()) {
            return true;
        }

        $this->warning($message);
        $this->line();

        return $this->confirm($question);
    }

    /**
     * Display a danger warning block
     */
    protected function danger(string|array $message): void
    {
        $messages = (array)$message;
        $this->line();
        $this->line('<error>  ' . str_repeat(' ', max(array_map('strlen', $messages))) . '  </error>');
        foreach ($messages as $msg) {
            $this->line('<error>  ' . str_pad($msg, max(array_map('strlen', $messages))) . '  </error>');
        }
        $this->line('<error>  ' . str_repeat(' ', max(array_map('strlen', $messages))) . '  </error>');
        $this->line();
    }
}
