<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Rebuild;

use function function_exists;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;
use TorrentPier\Console\Helpers\OutputHelper;

/**
 * Abstract base class for rebuild commands
 *
 * Provides common functionality for batch processing operations:
 * - Progress bar with ETA
 * - Batch size configuration
 * - Resume capability (start-from)
 * - Dry-run mode
 * - Graceful shutdown on SIGINT
 */
abstract class AbstractRebuildCommand extends Command
{
    /**
     * Default batch size for processing
     */
    protected const int DEFAULT_BATCH_SIZE = 500;

    /**
     * Flag indicating if shutdown was requested
     */
    protected bool $shutdownRequested = false;

    /**
     * Progress bar instance
     */
    protected ?ProgressBar $progressBar = null;

    /**
     * Start time for statistics
     */
    protected int $startTime = 0;

    /**
     * Total items processed
     */
    protected int $processedCount = 0;

    /**
     * Configure common options for all rebuild commands
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_REQUIRED,
                'Number of items to process per batch',
                static::DEFAULT_BATCH_SIZE
            )
            ->addOption(
                'start-from',
                's',
                InputOption::VALUE_REQUIRED,
                'Start processing from this ID (for resuming)',
                0
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be done without making changes'
            )
            ->addOption(
                'no-progress',
                null,
                InputOption::VALUE_NONE,
                'Disable progress bar output'
            );
    }

    /**
     * Initialize the command
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->startTime = time();
        $this->processedCount = 0;
        $this->shutdownRequested = false;

        // Register signal handlers for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGINT, [$this, 'handleShutdown']);
            pcntl_signal(SIGTERM, [$this, 'handleShutdown']);
        }
    }

    /**
     * Handle shutdown signal
     */
    public function handleShutdown(): void
    {
        $this->shutdownRequested = true;
        $this->line();
        $this->warning('Shutdown requested. Finishing current batch...');
    }

    /**
     * Check if shutdown was requested
     */
    protected function shouldStop(): bool
    {
        // Process pending signals
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }

        return $this->shutdownRequested;
    }

    /**
     * Get batch size from input
     */
    protected function getBatchSize(): int
    {
        return max(1, (int) $this->input->getOption('batch-size'));
    }

    /**
     * Get start ID from input
     */
    protected function getStartFrom(): int
    {
        return max(0, (int) $this->input->getOption('start-from'));
    }

    /**
     * Check if dry-run mode is enabled
     */
    protected function isDryRun(): bool
    {
        return (bool) $this->input->getOption('dry-run');
    }

    /**
     * Check if the progress bar should be shown
     */
    protected function shouldShowProgress(): bool
    {
        return !$this->input->getOption('no-progress') && !$this->output->isQuiet();
    }

    /**
     * Create and configure a progress bar
     */
    protected function startProgressBar(int $max, string $format = 'rebuild'): void
    {
        if (!$this->shouldShowProgress()) {
            return;
        }

        // Define custom format
        ProgressBar::setFormatDefinition(
            'rebuild',
            " %current%/%max% [%bar%] %percent:3s%% | %elapsed:6s% / %estimated:-6s% | %memory:6s%\n %message%"
        );

        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->setFormat($format);
        $this->progressBar->setMessage('Starting...');
        $this->progressBar->start();
    }

    /**
     * Advance the progress bar
     */
    protected function advanceProgressBar(int $step = 1, string $message = ''): void
    {
        $this->processedCount += $step;

        if ($this->progressBar !== null) {
            if ($message !== '') {
                $this->progressBar->setMessage($message);
            }
            $this->progressBar->advance($step);
        }
    }

    /**
     * Finish the progress bar
     */
    protected function finishProgressBar(): void
    {
        if ($this->progressBar !== null) {
            $this->progressBar->finish();
            $this->line();
        }
    }

    /**
     * Display rebuild statistics
     */
    protected function displayStatistics(string $itemType = 'items'): void
    {
        $elapsed = time() - $this->startTime;
        $rate = $elapsed > 0 ? round($this->processedCount / $elapsed, 2) : $this->processedCount;

        $this->line();
        $this->section('Statistics');
        $this->definitionList(
            ['Processed' => number_format($this->processedCount) . ' ' . $itemType],
            ['Time elapsed' => OutputHelper::formatDuration($elapsed)],
            ['Processing rate' => $rate . ' ' . $itemType . '/sec'],
            ['Memory peak' => FileSystemHelper::formatBytes(memory_get_peak_usage(true))],
        );
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
     * Display interrupted notice with resume hint
     */
    protected function displayInterruptedNotice(int $lastId): void
    {
        $this->line();
        $this->warning([
            'Processing interrupted!',
            'To resume, run with: --start-from=' . ($lastId + 1),
        ]);
    }
}
