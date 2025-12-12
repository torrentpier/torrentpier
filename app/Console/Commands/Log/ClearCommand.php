<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Log;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;

/**
 * Clear application logs
 */
#[AsCommand(
    name: 'log:clear',
    description: 'Clear application log files'
)]
class ClearCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation')
            ->addOption('older-than', 'o', InputOption::VALUE_OPTIONAL, 'Only clear logs older than X days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Clear Logs');

        $logDir = LOG_DIR;
        $force = $input->getOption('force');
        $olderThan = $input->getOption('older-than');

        if (!is_dir($logDir)) {
            $this->warning('Log directory does not exist: ' . $logDir);
            return self::SUCCESS;
        }

        // Find log files
        $files = $this->findLogFiles($logDir, $olderThan);

        if (empty($files)) {
            $this->success('No log files to clear.');
            return self::SUCCESS;
        }

        // Show files
        $this->section('Log Files Found');

        $totalSize = 0;
        $rows = [];
        foreach ($files as $file) {
            $size = filesize($file);
            $totalSize += $size;
            $rows[] = [
                basename($file),
                FileSystemHelper::formatBytes($size),
                date('Y-m-d H:i', filemtime($file)),
            ];
        }

        $this->table(['File', 'Size', 'Modified'], $rows);
        $this->line();
        $this->line(sprintf('  <comment>Total: %d file(s), %s</comment>', count($files), FileSystemHelper::formatBytes($totalSize)));
        $this->line();

        // Confirm
        if (!$force && !$this->confirm('Delete these log files?')) {
            $this->comment('Operation cancelled.');
            return self::SUCCESS;
        }

        // Delete files
        $deleted = 0;
        $failed = 0;

        foreach ($files as $file) {
            if (@unlink($file)) {
                $deleted++;
            } else {
                $failed++;
            }
        }

        $this->line();
        if ($failed === 0) {
            $this->success("Cleared $deleted log file(s)!");
        } else {
            $this->warning("Cleared $deleted file(s), failed to delete $failed file(s).");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Find log files in the directory
     */
    private function findLogFiles(string $dir, ?string $olderThan): array
    {
        $files = [];
        $cutoffTime = null;

        if ($olderThan !== null) {
            $days = (int) $olderThan;
            $cutoffTime = strtotime("-$days days");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            // Only log files
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['log', 'txt'])) {
                continue;
            }

            // Check age if specified
            if ($cutoffTime !== null && $file->getMTime() > $cutoffTime) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }
}
