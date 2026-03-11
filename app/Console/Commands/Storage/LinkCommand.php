<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Storage;

use Illuminate\Contracts\Container\BindingResolutionException;
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
    description: 'Create a symbolic link from "public/storage" to "storage/app/public"',
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
                'Overwrite existing symlink',
            )
            ->addOption(
                'relative',
                'r',
                InputOption::VALUE_NONE,
                'Create a relative symlink (default behavior)',
            )
            ->addOption(
                'absolute',
                'a',
                InputOption::VALUE_NONE,
                'Create an absolute symlink instead of relative',
            );
    }

    /**
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Storage Link');

        $target = STORAGE_PUBLIC_DIR;  // storage/app/public
        $link = PUBLIC_DIR . '/storage';  // public/storage
        $relativePath = '../storage/app/public';

        // Determine whether to use relative or absolute paths
        // Default is relative unless --absolute is specified
        $useRelative = !$input->getOption('absolute');

        // Check if the target directory exists
        if (!files()->isDirectory($target)) {
            $this->error("Target directory does not exist: {$target}");
            $this->comment('Run the application installation first.');

            return self::FAILURE;
        }

        // Check current state
        if (is_link($link)) {
            $currentTarget = @readlink($link);

            // Handle case when readlink fails (e.g., on Windows with invalid symlinks)
            if ($currentTarget === false) {
                if (!$input->getOption('force')) {
                    $this->warning('Symlink exists but cannot be read (possibly corrupted).');
                    $this->comment('Use --force to overwrite.');

                    return self::FAILURE;
                }

                if (!$this->deleteSymlink($link)) {
                    $this->error('Failed to remove existing symlink.');

                    return self::FAILURE;
                }

                // Verify deletion on Windows
                if (PHP_OS_FAMILY === 'Windows' && file_exists($link)) {
                    $this->error('Symlink still exists after deletion attempt.');
                    $this->comment('Try running as Administrator or manually delete: ' . $link);

                    return self::FAILURE;
                }

                $this->comment('Existing invalid symlink removed.');
            } else {
                // Compare paths: check if it's the same relative path or resolves to the same absolute path
                $currentRealPath = @realpath($currentTarget);
                $targetRealPath = @realpath($target);
                $isCorrectTarget = $currentTarget === $relativePath ||
                                   ($currentRealPath !== false && $targetRealPath !== false && $currentRealPath === $targetRealPath);

                if (!$input->getOption('force')) {
                    if ($isCorrectTarget) {
                        $this->info('Symlink already exists and points to the correct location.');
                        $this->definitionList(
                            ['Link' => $link],
                            ['Target' => $currentTarget],
                        );

                        return self::SUCCESS;
                    }

                    $this->warning('Symlink exists but points to a different location: ' . $currentTarget);
                    $this->comment('Use --force to overwrite.');

                    return self::FAILURE;
                }

                if (!$this->deleteSymlink($link)) {
                    $this->error('Failed to remove existing symlink.');

                    return self::FAILURE;
                }

                // Verify deletion on Windows
                if (PHP_OS_FAMILY === 'Windows' && file_exists($link)) {
                    $this->error('Symlink still exists after deletion attempt.');
                    $this->comment('Try running as Administrator or manually delete: ' . $link);

                    return self::FAILURE;
                }

                $this->comment($isCorrectTarget
                    ? 'Existing symlink removed.'
                    : 'Existing symlink removed (was pointing to: ' . $currentTarget . ')');
            }
        } elseif (files()->exists($link)) {
            $this->error("Path exists and is not a symlink: {$link}");
            $this->comment('Remove or rename this file/directory manually before creating the symlink.');

            return self::FAILURE;
        }

        // Create the symlink
        if (PHP_OS_FAMILY === 'Windows') {
            // On Windows, use mklink command for proper directory symlink creation
            $linkPath = str_replace('/', DIRECTORY_SEPARATOR, $link);

            // Use relative or absolute path based on configuration
            if ($useRelative) {
                // Convert relative path to Windows format
                $targetPath = str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            } else {
                $targetPath = str_replace('/', DIRECTORY_SEPARATOR, $target);
            }

            // Use mklink /D for directory symbolic link
            $command = \sprintf('mklink /D "%s" "%s"', $linkPath, $targetPath);
            $execOutput = [];
            exec($command, $execOutput, $returnCode);

            $success = $returnCode === 0;

            // If failed, provide detailed error information
            if (!$success && !empty($execOutput)) {
                $this->error('Failed to create symbolic link.');
                $this->comment('mklink output: ' . implode("\n", $execOutput));
                $this->comment('On Windows, you may need to run as Administrator or enable Developer Mode.');
                return self::FAILURE;
            }
        } else {
            // On Unix-like systems
            $originalDir = getcwd();
            chdir(PUBLIC_DIR);

            // Use relative or absolute path based on configuration
            $symlinkTarget = $useRelative ? $relativePath : $target;
            $success = symlink($symlinkTarget, 'storage');

            chdir($originalDir);
        }

        if ($success) {
            $this->success('Symbolic link created successfully!');
            $this->definitionList(
                ['Link' => $link],
                ['Target' => $useRelative ? $relativePath : $target],
            );

            return self::SUCCESS;
        }

        // Symlink creation failed (Unix or Windows without detailed error)
        $this->error('Failed to create symbolic link.');

        if (PHP_OS_FAMILY === 'Windows') {
            $this->comment('On Windows, you may need to run as Administrator or enable Developer Mode.');
        }

        return self::FAILURE;
    }

    /**
     * Delete a symbolic link (Windows-compatible)
     */
    private function deleteSymlink(string $link): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // On Windows, try rmdir first (for directory symlinks), then unlink
            if (@rmdir($link)) {
                return true;
            }

            return @unlink($link);
        }

        return files()->delete($link);
    }
}
