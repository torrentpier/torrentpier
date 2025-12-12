<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Release;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;

/**
 * Remove development files for production release
 */
#[AsCommand(
    name: 'release:cleanup',
    description: 'Remove development files for production release'
)]
class CleanupCommand extends Command
{
    /**
     * Files and directories to remove in production
     */
    private const CLEANUP_ITEMS = [
        // Git
        '.git',
        '.github',
        '.gitattributes',
        '.gitignore',

        // Editor configs
        '.editorconfig',
        '.idea',
        '.vscode',

        // Code quality tools
        '.php-cs-fixer.php',
        '.styleci.yml',
        'phpunit.xml',
        'phpstan.neon',

        // Documentation
        'CHANGELOG.md',
        'CLAUDE.md',
        'README.md',
        'UPGRADE_GUIDE.md',
        'CONTRIBUTING.md',

        // Tests
        'tests',

        // Release scripts (self-cleanup)
        'install/release_scripts',
    ];

    protected function configure(): void
    {
        $this
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be deleted without actually deleting'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Skip confirmation prompt'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Release Cleanup');

        $dryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');

        // Find items that exist
        $toDelete = [];
        foreach (self::CLEANUP_ITEMS as $item) {
            $path = BB_ROOT . $item;
            if (file_exists($path) || is_dir($path)) {
                $toDelete[] = $item;
            }
        }

        if (empty($toDelete)) {
            $this->success('Nothing to clean up. Already clean!');
            return self::SUCCESS;
        }

        // Show what will be deleted
        $this->section('Files to be removed');
        foreach ($toDelete as $item) {
            $path = BB_ROOT . $item;
            $type = is_dir($path) ? '<comment>dir</comment> ' : '<info>file</info>';
            $this->line("  {$type} {$item}");
        }

        $this->line('');
        $this->line(sprintf('  <comment>Total: %d item(s)</comment>', count($toDelete)));
        $this->line('');

        if ($dryRun) {
            $this->warning('Dry run mode - no files were deleted.');
            return self::SUCCESS;
        }

        // Confirm
        if (!$force && !$this->confirm('Delete these files permanently?', false)) {
            $this->comment('Operation cancelled.');
            return self::SUCCESS;
        }

        // Delete
        $this->section('Removing files');
        $deleted = 0;
        $failed = 0;

        foreach ($toDelete as $item) {
            $path = BB_ROOT . $item;

            try {
                if (is_dir($path)) {
                    FileSystemHelper::removeDirectory($path);
                } else {
                    @unlink($path);
                }

                if (!file_exists($path)) {
                    $this->line("  <info>✓</info> {$item}");
                    $deleted++;
                } else {
                    $this->line("  <error>✗</error> {$item} (failed)");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->line("  <error>✗</error> {$item} ({$e->getMessage()})");
                $failed++;
            }
        }

        $this->line('');

        if ($failed === 0) {
            $this->success("Cleanup completed! Removed {$deleted} item(s).");
        } else {
            $this->warning("Cleanup completed with errors. Removed: {$deleted}, Failed: {$failed}");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
