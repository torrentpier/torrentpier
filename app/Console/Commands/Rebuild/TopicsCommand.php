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
use TorrentPier\Legacy\Admin\Common;

/**
 * Rebuild topic counters
 *
 * Recalculates topic_replies, topic_first_post_id, topic_last_post_id
 * for all or specific topics.
 */
#[AsCommand(
    name: 'rebuild:topics',
    description: 'Rebuild topic reply counters and post references',
)]
class TopicsCommand extends AbstractRebuildCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'topic-id',
                't',
                InputOption::VALUE_REQUIRED,
                'Rebuild only specific topic ID (comma-separated for multiple)',
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command recalculates topic counters.

                    This rebuilds the following fields for each topic:
                      - topic_replies (reply count)
                      - topic_first_post_id (first post reference)
                      - topic_last_post_id (last post reference)
                      - topic_last_post_time (last post timestamp)

                    It also removes orphan topics (topics with no posts).

                    <comment>Rebuild all topics:</comment>
                      <info>php %command.full_name%</info>

                    <comment>Rebuild specific topic(s):</comment>
                      <info>php %command.full_name% --topic-id=123</info>
                      <info>php %command.full_name% --topic-id=100,200,300</info>

                    <comment>Preview what would be done:</comment>
                      <info>php %command.full_name% --dry-run</info>
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Rebuild Topic Counters');

        $topicId = $input->getOption('topic-id');
        $isDryRun = $this->isDryRun();

        if ($isDryRun) {
            $this->displayDryRunNotice();
        }

        // Get topics to process
        if ($topicId !== null) {
            $topicIds = array_map('intval', explode(',', $topicId));
            $topicIds = array_filter($topicIds, fn ($id) => $id > 0);

            if (empty($topicIds)) {
                $this->error('Invalid topic ID(s) specified.');

                return self::FAILURE;
            }

            $topicCount = \count($topicIds);
            $mode = 'specific';
        } else {
            $topicCount = $this->getTotalTopics();
            $topicIds = 'all';
            $mode = 'all';
        }

        if ($topicCount === 0) {
            $this->info('No topics found to process.');

            return self::SUCCESS;
        }

        // Display configuration
        $this->section('Configuration');
        $this->definitionList(
            ['Topics to process' => number_format($topicCount)],
            ['Mode' => $mode === 'all' ? 'All topics' : 'Specific topics'],
        );

        // Show topics in verbose mode for specific IDs
        if ($this->isVerbose() && $mode === 'specific') {
            $topics = $this->getTopicsByIds($topicIds);
            $this->line();
            $this->section('Topics');
            $rows = [];
            foreach ($topics as $topic) {
                $rows[] = [
                    $topic['topic_id'],
                    mb_substr($topic['topic_title'], 0, 50) . (mb_strlen($topic['topic_title']) > 50 ? '...' : ''),
                    $topic['topic_replies'],
                ];
            }
            $this->table(['ID', 'Title', 'Replies'], $rows);
        }

        $this->line();
        $this->section('Processing');

        if ($isDryRun) {
            $this->comment('Would recalculate counters for ' . number_format($topicCount) . ' topic(s)');

            // Check for orphan topics
            $orphanCount = $this->getOrphanTopicCount();
            if ($orphanCount > 0) {
                $this->comment("Would remove {$orphanCount} orphan topic(s) (topics with no posts)");
            }

            $this->processedCount = $topicCount;
            $this->displayStatistics('topics');

            return self::SUCCESS;
        }

        // Start processing
        $this->startProgressBar(1); // Single operation for sync

        $this->advanceProgressBar(0, 'Synchronizing topics...');
        Common::sync('topic', $topicIds);
        $this->advanceProgressBar(1, 'Topics synchronized');

        $this->finishProgressBar();

        // Display results
        $this->line();
        $this->section('Results');

        $finalCount = $this->getTotalTopics();
        $deletedOrphans = $topicCount - $finalCount;

        $this->definitionList(
            ['Total topics' => number_format($finalCount)],
            ['Orphan topics removed' => $deletedOrphans > 0 ? $deletedOrphans : 'None'],
        );

        $this->processedCount = $topicCount;
        $this->displayStatistics('topics');

        $this->success('Topic counters rebuilt successfully!');

        return self::SUCCESS;
    }

    /**
     * Get total topics count
     */
    private function getTotalTopics(): int
    {
        return DB()->table(BB_TOPICS)
            ->where('topic_status != ?', TOPIC_MOVED)
            ->count('*');
    }

    /**
     * Get orphan topic count (topics with no posts)
     * Note: LEFT JOIN query - using raw SQL for complex join
     */
    private function getOrphanTopicCount(): int
    {
        $row = DB()->fetch_row('
            SELECT COUNT(*) as cnt
            FROM ' . BB_TOPICS . ' t
            LEFT JOIN ' . BB_POSTS . ' p ON p.topic_id = t.topic_id
            WHERE t.topic_status != ' . TOPIC_MOVED . '
                AND p.post_id IS NULL
        ');

        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Get specific topics by IDs
     */
    private function getTopicsByIds(array $ids): array
    {
        return DB()->table(BB_TOPICS)
            ->select('topic_id, topic_title, topic_replies')
            ->where('topic_id', $ids)
            ->order('topic_id')
            ->fetchAll();
    }
}
