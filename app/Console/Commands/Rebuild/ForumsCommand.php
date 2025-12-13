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
use TorrentPier\Application;
use TorrentPier\Database\Database;
use TorrentPier\Legacy\Admin\Common;

/**
 * Rebuild forum counters
 *
 * Recalculates forum_posts, forum_topics, and forum_last_post_id for all forums.
 */
#[AsCommand(
    name: 'rebuild:forums',
    description: 'Rebuild forum post and topic counters',
)]
class ForumsCommand extends AbstractRebuildCommand
{
    public function __construct(
        private readonly Database $database,
        ?Application              $app = null,
    ) {
        parent::__construct($app);
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'forum-id',
                'f',
                InputOption::VALUE_REQUIRED,
                'Rebuild only specific forum ID (comma-separated for multiple)',
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command recalculates forum counters.

                    This rebuilds the following fields for each forum:
                      - forum_posts (total post count)
                      - forum_topics (total topic count)
                      - forum_last_post_id (last post reference)

                    <comment>Rebuild all forums:</comment>
                      <info>php %command.full_name%</info>

                    <comment>Rebuild specific forum(s):</comment>
                      <info>php %command.full_name% --forum-id=5</info>
                      <info>php %command.full_name% --forum-id=1,2,3</info>

                    <comment>Preview what would be done:</comment>
                      <info>php %command.full_name% --dry-run</info>
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Rebuild Forum Counters');

        $forumId = $input->getOption('forum-id');
        $isDryRun = $this->isDryRun();

        if ($isDryRun) {
            $this->displayDryRunNotice();
        }

        // Get forums to process
        $forumIds = [];
        if ($forumId !== null) {
            $forumIds = array_map('intval', explode(',', $forumId));
            $forumIds = array_filter($forumIds, fn ($id) => $id > 0);

            if (empty($forumIds)) {
                $this->error('Invalid forum ID(s) specified.');

                return self::FAILURE;
            }

            $forums = $this->getForumsByIds($forumIds);
        } else {
            $forums = $this->getAllForums();
        }

        if (empty($forums)) {
            $this->info('No forums found to process.');

            return self::SUCCESS;
        }

        // Display configuration
        $this->section('Configuration');
        $this->definitionList(
            ['Forums to process' => \count($forums)],
            ['Mode' => $forumId !== null ? 'Specific forums' : 'All forums'],
        );

        // Show forums list in verbose mode
        if ($this->isVerbose()) {
            $this->line();
            $this->section('Forums');
            $rows = [];
            foreach ($forums as $forum) {
                $rows[] = [
                    $forum['forum_id'],
                    $forum['forum_name'],
                    $forum['forum_posts'],
                    $forum['forum_topics'],
                ];
            }
            $this->table(['ID', 'Name', 'Posts', 'Topics'], $rows);
        }

        $this->line();
        $this->section('Processing');

        if ($isDryRun) {
            $this->comment('Would recalculate counters for ' . \count($forums) . ' forum(s)');
            $this->displayStatistics('forums');

            return self::SUCCESS;
        }

        // Start progress bar
        $this->startProgressBar(\count($forums));

        if ($forumId !== null) {
            // Sync specific forums
            foreach ($forums as $forum) {
                if ($this->shouldStop()) {
                    $this->finishProgressBar();
                    $this->warning('Processing interrupted.');

                    return self::FAILURE;
                }

                Common::sync('forum', [$forum['forum_id']]);
                $this->advanceProgressBar(1, "Forum: {$forum['forum_name']}");
            }
        } else {
            // Use optimized batch sync for all forums
            $this->advanceProgressBar(0, 'Running batch sync for all forums...');
            Common::sync_all_forums();
            $this->advanceProgressBar(\count($forums), 'All forums synchronized');
        }

        $this->finishProgressBar();

        // Fetch updated stats
        $this->line();
        $this->section('Results');

        $updatedForums = $forumId !== null
            ? $this->getForumsByIds($forumIds)
            : $this->getAllForums();

        $totalPosts = 0;
        $totalTopics = 0;

        foreach ($updatedForums as $forum) {
            $totalPosts += $forum['forum_posts'];
            $totalTopics += $forum['forum_topics'];
        }

        $this->definitionList(
            ['Total posts' => number_format($totalPosts)],
            ['Total topics' => number_format($totalTopics)],
        );

        $this->processedCount = \count($forums);
        $this->displayStatistics('forums');

        $this->success('Forum counters rebuilt successfully!');

        return self::SUCCESS;
    }

    /**
     * Get all forums
     */
    private function getAllForums(): array
    {
        return $this->database->table(BB_FORUMS)
            ->select('forum_id, forum_name, forum_posts, forum_topics')
            ->order('forum_id')
            ->fetchAll();
    }

    /**
     * Get specific forums by IDs
     */
    private function getForumsByIds(array $ids): array
    {
        return $this->database->table(BB_FORUMS)
            ->select('forum_id, forum_name, forum_posts, forum_topics')
            ->where('forum_id', $ids)
            ->order('forum_id')
            ->fetchAll();
    }
}
