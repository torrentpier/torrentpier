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

/**
 * Rebuild the search index
 *
 * This is the most resource-intensive rebuild operation.
 * It processes all posts and extracts searchable keywords.
 */
#[AsCommand(
    name: 'rebuild:search',
    description: 'Rebuild the search index from all posts'
)]
class SearchCommand extends AbstractRebuildCommand
{
    /**
     * Default batch size for search rebuild (smaller due to text processing)
     */
    protected const int DEFAULT_BATCH_SIZE = 50;

    /**
     * Last processed post ID (for resume capability)
     */
    private int $lastPostId = 0;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'clear-index',
                'c',
                InputOption::VALUE_NONE,
                'Clear the search index before rebuilding (TRUNCATE)'
            )
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command rebuilds the full-text search index.

This is a heavy operation that processes all posts in the database,
extracts keywords, and stores them in the search index table.

<comment>Basic usage:</comment>
  <info>php %command.full_name%</info>

<comment>Clear and rebuild from scratch:</comment>
  <info>php %command.full_name% --clear-index</info>

<comment>Custom batch size (default: 50):</comment>
  <info>php %command.full_name% --batch-size=100</info>

<comment>Resume from a specific post ID:</comment>
  <info>php %command.full_name% --start-from=12345</info>

<comment>Preview what would be done:</comment>
  <info>php %command.full_name% --dry-run</info>

<comment>Disable progress bar (for cron/scripts):</comment>
  <info>php %command.full_name% --no-progress</info>

Note: This command can be safely interrupted with Ctrl+C.
It will display the last processed ID so you can resume later.
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Rebuild Search Index');

        // Include bbcode functions for search word extraction
        require_once INC_DIR . '/bbcode.php';

        $batchSize = $this->getBatchSize();
        $startFrom = $this->getStartFrom();
        $clearIndex = $input->getOption('clear-index');
        $isDryRun = $this->isDryRun();

        if ($isDryRun) {
            $this->displayDryRunNotice();
        }

        // Get total posts count
        $totalPosts = $this->getTotalPosts($startFrom);

        if ($totalPosts === 0) {
            $this->info('No posts to process.');
            return self::SUCCESS;
        }

        // Display configuration
        $this->section('Configuration');
        $this->definitionList(
            ['Total posts' => number_format($totalPosts)],
            ['Batch size' => $batchSize],
            ['Start from ID' => $startFrom > 0 ? $startFrom : 'beginning'],
            ['Clear index' => $clearIndex ? 'Yes' : 'No'],
        );

        // Clear index if requested
        if ($clearIndex && $startFrom === 0) {
            if ($isDryRun) {
                $this->comment('Would TRUNCATE ' . BB_POSTS_SEARCH);
            } else {
                $this->clearSearchIndex();
                $this->info('Search index cleared.');
            }
        }

        $this->line();
        $this->section('Processing');

        // Start progress bar
        $this->startProgressBar($totalPosts);

        // Process posts in batches
        $currentId = $startFrom;
        $interrupted = false;

        while (true) {
            // Check for a shutdown signal
            if ($this->shouldStop()) {
                $interrupted = true;
                break;
            }

            // Fetch batch
            $posts = $this->fetchPostsBatch($currentId, $batchSize);

            if (empty($posts)) {
                break;
            }

            // Process batch
            if (!$isDryRun) {
                $this->processBatch($posts);
            }

            // Update progress
            $processedInBatch = count($posts);
            $this->lastPostId = (int) $posts[array_key_last($posts)]['post_id'];
            $currentId = $this->lastPostId + 1;

            $this->advanceProgressBar(
                $processedInBatch,
                sprintf('Processing post ID %d...', $this->lastPostId)
            );

            // Allow garbage collection
            unset($posts);
        }

        $this->finishProgressBar();

        // Handle interruption
        if ($interrupted) {
            $this->displayInterruptedNotice($this->lastPostId);
            return self::FAILURE;
        }

        // Optimize tables
        if (!$isDryRun && $this->processedCount > 0) {
            $this->line();
            $this->section('Optimization');
            $this->optimizeSearchTables();
        }

        // Display statistics
        $this->displayStatistics('posts');

        $this->success('Search index rebuild completed!');
        return self::SUCCESS;
    }

    /**
     * Get total posts count from the starting ID
     */
    private function getTotalPosts(int $startFrom): int
    {
        $sql = "
            SELECT COUNT(*) as cnt
            FROM " . BB_POSTS_TEXT . " pt
            INNER JOIN " . BB_POSTS . " p ON p.post_id = pt.post_id
            WHERE p.poster_id NOT IN(" . BOT_UID . ")
            " . ($startFrom > 0 ? "AND pt.post_id >= $startFrom" : '') . "
        ";

        $row = DB()->fetch_row($sql);
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Fetch a batch of posts for processing
     */
    private function fetchPostsBatch(int $startId, int $limit): array
    {
        $sql = "
            SELECT
                pt.post_id,
                pt.post_text,
                IF(p.post_id = t.topic_first_post_id, t.topic_title, '') AS post_subject
            FROM " . BB_POSTS_TEXT . " pt
            INNER JOIN " . BB_POSTS . " p ON p.post_id = pt.post_id
            INNER JOIN " . BB_TOPICS . " t ON t.topic_id = p.topic_id
            WHERE p.poster_id NOT IN(" . BOT_UID . ")
                AND pt.post_id >= $startId
            ORDER BY pt.post_id
            LIMIT $limit
        ";

        return DB()->fetch_rowset($sql);
    }

    /**
     * Process a batch of posts
     */
    private function processBatch(array $posts): void
    {
        $wordsSql = [];

        foreach ($posts as $row) {
            $postText = str_replace('\n', "\n", $row['post_text']);
            $postSubject = str_replace('\n', "\n", $row['post_subject']);

            // Extract search words using the existing function
            $searchWords = add_search_words(
                $row['post_id'],
                stripslashes($postText),
                stripslashes($postSubject),
                true // only_return_words
            );

            $wordsSql[] = [
                'post_id' => (int) $row['post_id'],
                'search_words' => $searchWords,
            ];
        }

        // Bulk insert
        if (!empty($wordsSql)) {
            DB()->query('REPLACE INTO ' . BB_POSTS_SEARCH . DB()->build_array('MULTI_INSERT', $wordsSql));
        }
    }

    /**
     * Clear the search index
     */
    private function clearSearchIndex(): void
    {
        DB()->query('TRUNCATE TABLE ' . BB_POSTS_SEARCH);
    }

    /**
     * Optimize search tables after rebuild
     */
    private function optimizeSearchTables(): void
    {
        $table = BB_POSTS_SEARCH;

        $this->line("  Analyzing $table...");
        DB()->query("ANALYZE TABLE $table");

        $this->line("  Optimizing $table...");
        DB()->query("OPTIMIZE TABLE $table");

        $this->info('Tables optimized.');
    }
}
