<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Rebuild;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;

/**
 * Rebuild datastore cache
 *
 * Regenerates cached data for forums, stats, moderators, etc.
 */
#[AsCommand(
    name: 'rebuild:datastore',
    description: 'Rebuild datastore cache entries',
)]
class DatastoreCommand extends Command
{
    /**
     * Available datastore items with descriptions
     */
    private const array ITEMS = [
        'stats' => 'Site statistics (users, posts, topics)',
        'cat_forums' => 'Categories and forums structure',
        'moderators' => 'Forum moderators list',
        'ranks' => 'User ranks configuration',
        'ban_list' => 'Banned users and IPs',
        'smile_replacements' => 'Smilie/emoji replacements',
        'censor' => 'Word censor patterns',
        'jumpbox' => 'Forum jumpbox data',
        'viewtopic_forum_select' => 'Forum select for topic view',
        'latest_news' => 'Latest news items',
        'network_news' => 'Network news items',
        'ads' => 'Advertisements data',
        'check_updates' => 'Update check cache',
    ];

    protected function configure(): void
    {
        $this
            ->addOption(
                'key',
                'k',
                InputOption::VALUE_REQUIRED,
                'Rebuild specific key(s), comma-separated (e.g., stats,cat_forums,moderators)',
            )
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'List all available datastore keys',
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command rebuilds datastore cache entries.

                    Datastore is a caching layer for frequently accessed data like
                    forum structure, statistics, moderator lists, etc.

                    <comment>Rebuild all datastore items:</comment>
                      <info>php %command.full_name%</info>

                    <comment>Rebuild specific item(s):</comment>
                      <info>php %command.full_name% --key=stats</info>
                      <info>php %command.full_name% --key=cat_forums,moderators</info>

                    <comment>List available keys:</comment>
                      <info>php %command.full_name% --list</info>

                    Available keys: stats, cat_forums, moderators, ranks, ban_list,
                    smile_replacements, censor, jumpbox, viewtopic_forum_select,
                    latest_news, network_news, ads, check_updates
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Rebuild Datastore');

        // List mode
        if ($input->getOption('list')) {
            return $this->listKeys();
        }

        // Determine which keys to rebuild
        $keyOption = $input->getOption('key');
        if ($keyOption !== null) {
            $keys = array_map('trim', explode(',', $keyOption));
            $keys = array_filter($keys);

            // Validate keys
            $invalidKeys = array_diff($keys, array_keys(self::ITEMS));
            if (!empty($invalidKeys)) {
                $this->error('Unknown datastore key(s): ' . implode(', ', $invalidKeys));
                $this->comment('Use --list to see available keys.');

                return self::FAILURE;
            }
        } else {
            // Rebuild all unique keys
            $keys = array_keys(self::ITEMS);
        }

        // Display configuration
        $this->section('Configuration');
        $this->definitionList(
            ['Keys to rebuild' => \count($keys)],
            ['Mode' => $keyOption !== null ? 'Specific keys' : 'All keys'],
        );

        // Show keys in verbose mode
        if ($this->isVerbose()) {
            $this->line();
            $this->section('Keys');
            $rows = [];
            foreach ($keys as $key) {
                $rows[] = [$key, self::ITEMS[$key] ?? '-'];
            }
            $this->table(['Key', 'Description'], $rows);
        }

        $this->line();
        $this->section('Processing');

        $startTime = microtime(true);
        $success = 0;
        $errors = [];

        // Create a progress bar
        $progressBar = $this->createProgressBar(\count($keys));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | %message%');
        $progressBar->setMessage('Starting...');
        $progressBar->start();

        foreach ($keys as $key) {
            $progressBar->setMessage("Rebuilding: {$key}");

            try {
                datastore()->update($key);
                $success++;
            } catch (Throwable $e) {
                $errors[$key] = $e->getMessage();
            }

            $progressBar->advance();
        }

        $progressBar->setMessage('Done');
        $progressBar->finish();
        $this->line();

        $elapsed = round(microtime(true) - $startTime, 2);

        // Display results
        $this->line();
        $this->section('Results');

        $this->definitionList(
            ['Successful' => $success . '/' . \count($keys)],
            ['Time elapsed' => $elapsed . 's'],
            ['Memory used' => FileSystemHelper::formatBytes(memory_get_peak_usage(true))],
        );

        // Show errors if any
        if (!empty($errors)) {
            $this->line();
            $this->error('Errors occurred:');
            foreach ($errors as $key => $message) {
                $this->line("  <error>{$key}</error>: {$message}");
            }

            return self::FAILURE;
        }

        $this->success('Datastore rebuilt successfully!');

        return self::SUCCESS;
    }

    /**
     * List available datastore keys
     */
    private function listKeys(): int
    {
        $this->section('Available Datastore Keys');

        $rows = [];
        foreach (self::ITEMS as $key => $description) {
            $rows[] = [$key, $description];
        }

        $this->table(['Key', 'Description'], $rows);

        $this->line();
        $this->comment('Use: php bull rebuild:datastore --key=stats,cat_forums');

        return self::SUCCESS;
    }
}
