<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Storage;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Commands\Command;

/**
 * Creates a symbolic link from public/storage to storage/app/public
 */
#[AsCommand(
    name: 'storage:link',
    description: 'Create a symbolic link from "public/storage" to "storage/app/public"'
)]
class LinkCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Overwrite existing symlink'
            )
            ->addOption(
                'relative',
                'r',
                InputOption::VALUE_NONE,
                'Create a relative symlink (default behavior)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Storage Link');

        $target = STORAGE_PUBLIC_DIR;  // storage/app/public
        $link = PUBLIC_DIR . '/storage';  // public/storage
        $relativePath = '../storage/app/public';

        // Check if the target directory exists
        if (!is_dir($target)) {
            $this->error("Target directory does not exist: $target");
            $this->comment('Run the application installation first.');
            return self::FAILURE;
        }

        // Check current state
        if (is_link($link)) {
            $currentTarget = readlink($link);
            $isCorrectTarget = $currentTarget === $relativePath || realpath($currentTarget) === realpath($target);

            if (!$input->getOption('force')) {
                if ($isCorrectTarget) {
                    $this->info('Symlink already exists and points to the correct location.');
                    $this->definitionList(
                        ['Link' => $link],
                        ['Target' => $relativePath],
                    );
                    return self::SUCCESS;
                }

                $this->warning('Symlink exists but points to a different location: ' . $currentTarget);
                $this->comment('Use --force to overwrite.');
                return self::FAILURE;
            }

            unlink($link);
            $this->comment($isCorrectTarget
                ? 'Existing symlink removed.'
                : 'Existing symlink removed (was pointing to: ' . $currentTarget . ')');
        } elseif (file_exists($link)) {
            $this->error("Path exists and is not a symlink: $link");
            $this->comment('Remove or rename this file/directory manually before creating the symlink.');
            return self::FAILURE;
        }

        // Create the symlink
        // We need to change to the public directory to create a relative symlink
        $originalDir = getcwd();
        chdir(PUBLIC_DIR);
        $success = symlink($relativePath, 'storage');
        chdir($originalDir);

        if ($success) {
            $this->success('Symbolic link created successfully!');
            $this->definitionList(
                ['Link' => $link],
                ['Target' => $relativePath],
            );
            return self::SUCCESS;
        }

        // Symlink creation failed
        $this->error('Failed to create symbolic link.');

        if (PHP_OS_FAMILY === 'Windows') {
            $this->comment('On Windows, you may need to run as Administrator or enable Developer Mode.');
        }

        return self::FAILURE;
    }
}
