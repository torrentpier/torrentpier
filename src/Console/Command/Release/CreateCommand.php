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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;

/**
 * Create a new TorrentPier release
 */
#[AsCommand(
    name: 'release:create',
    description: 'Create a new TorrentPier release (update version, changelog, git tag)'
)]
class CreateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'Version number (e.g., v3.0.0 or 3.0.0)'
            )
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Release date (DD-MM-YYYY), defaults to today'
            )
            ->addOption(
                'emoji',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Version emoji for commit message'
            )
            ->addOption(
                'no-git',
                null,
                InputOption::VALUE_NONE,
                'Skip git operations (commit, tag, push)'
            )
            ->addOption(
                'no-changelog',
                null,
                InputOption::VALUE_NONE,
                'Skip changelog generation'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be done without making changes'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Create Release');

        $dryRun = $input->getOption('dry-run');
        $noGit = $input->getOption('no-git');
        $noChangelog = $input->getOption('no-changelog');

        // Get version
        $version = $input->getArgument('version');
        if (empty($version)) {
            $version = $this->ask('Version number (e.g., v3.0.0)');
            if (empty($version)) {
                $this->error('Version is required.');
                return self::FAILURE;
            }
        }

        // Normalize version
        if (!str_starts_with($version, 'v')) {
            $version = 'v' . $version;
        }

        // Validate semver format
        if (!preg_match('/^v\d+\.\d+\.\d+(-[\w.]+)?$/', $version)) {
            $this->error('Invalid version format. Use semantic versioning (e.g., v3.0.0, v3.0.0-beta.1)');
            return self::FAILURE;
        }

        // Get release date
        $date = $input->getOption('date');
        if (empty($date)) {
            $date = date('d-m-Y');
        } else {
            // Validate date
            $dateObj = \DateTime::createFromFormat('d-m-Y', $date);
            if (!$dateObj || $dateObj->format('d-m-Y') !== $date) {
                $this->error('Invalid date format. Use DD-MM-YYYY');
                return self::FAILURE;
            }
        }

        // Get emoji
        $emoji = $input->getOption('emoji') ?? '';

        // Show summary
        $this->section('Release Configuration');
        $this->definitionList(
            ['Version' => "<info>{$version}</info>"],
            ['Release Date' => $date],
            ['Emoji' => $emoji ?: '<comment>(none)</comment>'],
            ['Generate Changelog' => $noChangelog ? '<comment>No</comment>' : '<info>Yes</info>'],
            ['Git Operations' => $noGit ? '<comment>Skipped</comment>' : '<info>Commit, Tag, Push</info>'],
        );

        if ($dryRun) {
            $this->warning('Dry run mode - no changes will be made.');
            return self::SUCCESS;
        }

        // Confirm
        if (!$this->confirm('Create this release?', true)) {
            $this->comment('Release cancelled.');
            return self::SUCCESS;
        }

        // Step 1: Update config.php
        $this->section('Updating Configuration');
        if (!$this->updateConfig($version, $date)) {
            return self::FAILURE;
        }
        $this->line('  <info>✓</info> Updated library/config.php');

        // Step 2: Generate changelog
        if (!$noChangelog) {
            $this->section('Generating Changelog');
            $this->generateChangelog($version);
            $this->line('  <info>✓</info> Updated CHANGELOG.md');
        }

        // Step 3: Git operations
        if (!$noGit) {
            $this->section('Git Operations');

            // Commit
            $commitMsg = "release: {$version}" . ($emoji ? " {$emoji}" : '');
            $this->runGitCommand('git add -A');
            $this->runGitCommand('git commit -m ' . escapeshellarg($commitMsg));
            $this->line('  <info>✓</info> Created commit');

            // Tag
            $this->runGitCommand("git tag -a \"{$version}\" -m \"Release {$version}\"");
            $this->line("  <info>✓</info> Created tag {$version}");

            // Push
            $this->line('  Pushing to origin...');
            $this->runGitCommand('git push origin master');
            $this->runGitCommand("git push origin {$version}");
            $this->line('  <info>✓</info> Pushed to origin');
        }

        $this->line('');
        $this->success("Release {$version} created successfully!");

        if (!$noGit) {
            $this->line('');
            $this->comment('GitHub Actions will now:');
            $this->listing([
                'Generate release notes',
                'Create release archive',
                'Publish to GitHub Releases',
            ]);
        }

        return self::SUCCESS;
    }

    /**
     * Update version and date in config.php
     */
    private function updateConfig(string $version, string $date): bool
    {
        $configFile = BB_ROOT . 'library/config.php';

        if (!is_file($configFile)) {
            $this->error('Config file not found: library/config.php');
            return false;
        }

        if (!is_writable($configFile)) {
            $this->error('Config file is not writable');
            return false;
        }

        $content = file_get_contents($configFile);

        // Update version
        $content = preg_replace(
            "/(\\\$bb_cfg\['tp_version'\]\s*=\s*')[^']*';/",
            "\${1}{$version}';",
            $content
        );

        // Update release date
        $content = preg_replace(
            "/(\\\$bb_cfg\['tp_release_date'\]\s*=\s*')[^']*';/",
            "\${1}{$date}';",
            $content
        );

        if (file_put_contents($configFile, $content) === false) {
            $this->error('Failed to write config file');
            return false;
        }

        return true;
    }

    /**
     * Generate changelog using git-cliff
     */
    private function generateChangelog(string $version): void
    {
        $cliffConfig = BB_ROOT . 'install/release_scripts/cliff.toml';

        if (!file_exists($cliffConfig)) {
            $this->warning('cliff.toml not found, skipping changelog generation');
            return;
        }

        $command = sprintf(
            'npx git-cliff --config %s --tag %s > %s 2>/dev/null',
            escapeshellarg($cliffConfig),
            escapeshellarg($version),
            escapeshellarg(BB_ROOT . 'CHANGELOG.md')
        );

        @exec($command);
    }

    /**
     * Run a git command
     */
    private function runGitCommand(string $command): int
    {
        if ($this->isVerbose()) {
            $this->line("  <comment>\$ {$command}</comment>");
        }

        exec($command . ' 2>&1', $output, $exitCode);

        if ($this->isVeryVerbose() && !empty($output)) {
            foreach ($output as $line) {
                $this->line("    {$line}");
            }
        }

        return $exitCode;
    }
}
