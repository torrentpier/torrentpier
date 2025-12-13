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
 * Rebuild user post counters
 *
 * Recalculates user_posts for all or specific users.
 */
#[AsCommand(
    name: 'rebuild:users',
    description: 'Rebuild user post counters',
)]
class UsersCommand extends AbstractRebuildCommand
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
                'user-id',
                'u',
                InputOption::VALUE_REQUIRED,
                'Rebuild only specific user ID (comma-separated for multiple)',
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command recalculates user post counters.

                    This rebuilds the <comment>user_posts</comment> field for each user by counting
                    their actual posts in the database.

                    <comment>Rebuild all users:</comment>
                      <info>php %command.full_name%</info>

                    <comment>Rebuild specific user(s):</comment>
                      <info>php %command.full_name% --user-id=1</info>
                      <info>php %command.full_name% --user-id=1,2,3</info>

                    <comment>Preview what would be done:</comment>
                      <info>php %command.full_name% --dry-run</info>
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Rebuild User Post Counters');

        $userId = $input->getOption('user-id');
        $isDryRun = $this->isDryRun();

        if ($isDryRun) {
            $this->displayDryRunNotice();
        }

        // Get users to the process
        if ($userId !== null) {
            $userIds = array_map('intval', explode(',', $userId));
            $userIds = array_filter($userIds, fn ($id) => $id > 0);

            if (empty($userIds)) {
                $this->error('Invalid user ID(s) specified.');

                return self::FAILURE;
            }

            $userCount = \count($userIds);
            $mode = 'specific';
        } else {
            $userCount = $this->getTotalUsers();
            $userIds = 'all';
            $mode = 'all';
        }

        if ($userCount === 0) {
            $this->info('No users found to process.');

            return self::SUCCESS;
        }

        // Display configuration
        $this->section('Configuration');
        $this->definitionList(
            ['Users to process' => number_format($userCount)],
            ['Mode' => $mode === 'all' ? 'All users' : 'Specific users'],
        );

        // Show users in verbose mode for specific IDs
        if ($this->isVerbose() && $mode === 'specific') {
            $users = $this->getUsersByIds($userIds);
            $this->line();
            $this->section('Users');
            $rows = [];
            foreach ($users as $user) {
                $rows[] = [
                    $user['user_id'],
                    $user['username'],
                    $user['user_posts'],
                ];
            }
            $this->table(['ID', 'Username', 'Current Posts'], $rows);
        }

        $this->line();
        $this->section('Processing');

        if ($isDryRun) {
            $this->comment('Would recalculate post counts for ' . number_format($userCount) . ' user(s)');
            $this->processedCount = $userCount;
            $this->displayStatistics('users');

            return self::SUCCESS;
        }

        // Start processing
        $this->startProgressBar(1); // Single operation for sync

        $this->advanceProgressBar(0, 'Synchronizing user post counts...');
        Common::sync('user_posts', $userIds);
        $this->advanceProgressBar(1, 'User post counts synchronized');

        $this->finishProgressBar();

        // Display results
        $this->line();
        $this->section('Results');

        $totalPosts = $this->getTotalPosts();

        $this->definitionList(
            ['Total users processed' => number_format($userCount)],
            ['Total posts in database' => number_format($totalPosts)],
        );

        // Show updated counts in verbose mode for specific IDs
        if ($this->isVerbose() && $mode === 'specific') {
            $users = $this->getUsersByIds($userIds);
            $this->line();
            $this->section('Updated Users');
            $rows = [];
            foreach ($users as $user) {
                $rows[] = [
                    $user['user_id'],
                    $user['username'],
                    $user['user_posts'],
                ];
            }
            $this->table(['ID', 'Username', 'Updated Posts'], $rows);
        }

        $this->processedCount = $userCount;
        $this->displayStatistics('users');

        $this->success('User post counters rebuilt successfully!');

        return self::SUCCESS;
    }

    /**
     * Get total users count (excluding guest)
     */
    private function getTotalUsers(): int
    {
        return $this->database->table(BB_USERS)
            ->where('user_id != ?', GUEST_UID)
            ->count('*');
    }

    /**
     * Get total posts count
     */
    private function getTotalPosts(): int
    {
        return $this->database->table(BB_POSTS)->count('*');
    }

    /**
     * Get specific users by IDs
     */
    private function getUsersByIds(array $ids): array
    {
        return $this->database->table(BB_USERS)
            ->select('user_id, username, user_posts')
            ->where('user_id', $ids)
            ->order('user_id')
            ->fetchAll();
    }
}
