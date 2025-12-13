<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Maintenance;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Application;
use TorrentPier\Config;
use TorrentPier\Database\Database;

/**
 * Prune old data
 *
 * Deletes old logs, private messages, sessions, and search results.
 * This is a DESTRUCTIVE operation - use with caution.
 */
#[AsCommand(
    name: 'maintenance:prune',
    description: 'Delete old logs, private messages, sessions, and search results',
)]
class PruneCommand extends AbstractMaintenanceCommand
{
    public function __construct(
        private readonly Database $database,
        private readonly Config   $config,
        ?Application              $app = null,
    ) {
        parent::__construct($app);
    }

    /**
     * Prune types with descriptions
     */
    private const array PRUNE_TYPES = [
        'logs' => 'Action and moderation logs',
        'pm' => 'Private messages',
        'sessions' => 'Expired user sessions',
        'search' => 'Search result cache',
    ];

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'Type to prune: logs, pm, sessions, search',
            )
            ->addOption(
                'days',
                'd',
                InputOption::VALUE_REQUIRED,
                'Delete records older than N days (for logs, pm)',
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command deletes old data from the database.

                    <error>WARNING: This is a DESTRUCTIVE operation. Data cannot be recovered!</error>

                    <comment>Without parameters - shows information only:</comment>
                      <info>php %command.full_name%</info>

                    <comment>Prune specific type:</comment>
                      <info>php %command.full_name% --type=logs --days=30</info>
                      <info>php %command.full_name% --type=pm --days=90</info>
                      <info>php %command.full_name% --type=sessions</info>
                      <info>php %command.full_name% --type=search</info>

                    <comment>Skip confirmation (dangerous!):</comment>
                      <info>php %command.full_name% --type=logs --days=30 --force</info>

                    <comment>Preview without changes:</comment>
                      <info>php %command.full_name% --type=logs --days=30 --dry-run</info>

                    Available prune types:
                      <info>logs</info>     - Action and moderation logs (requires --days)
                      <info>pm</info>       - Private messages (requires --days)
                      <info>sessions</info> - Expired user sessions (automatic threshold)
                      <info>search</info>   - Search result cache (older than 3 hours)
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Maintenance: Prune Old Data');

        $type = $input->getOption('type');
        $days = $input->getOption('days');
        $isDryRun = $this->isDryRun();
        $isForce = $this->isForce();

        // No type specified - show information only
        if ($type === null) {
            return $this->showInfo();
        }

        // Validate type
        if (!\array_key_exists($type, self::PRUNE_TYPES)) {
            $this->error("Unknown prune type: {$type}");
            $this->comment('Available types: ' . implode(', ', array_keys(self::PRUNE_TYPES)));

            return self::FAILURE;
        }

        // Validate days for types that require it
        if (\in_array($type, ['logs', 'pm']) && $days === null) {
            $this->error("The --days option is required for '{$type}' type.");
            $this->comment("Example: php bull maintenance:prune --type={$type} --days=30");

            return self::FAILURE;
        }

        if ($days !== null) {
            $days = (int)$days;
            if ($days <= 0) {
                $this->error('Days must be a positive number.');

                return self::FAILURE;
            }
        }

        // Get count of records to delete
        $count = $this->getRecordCount($type, $days);

        if ($count === 0) {
            $this->info("No records to prune for type '{$type}'.");

            return self::SUCCESS;
        }

        // Display what will be deleted
        $this->section('Prune Target');

        $description = self::PRUNE_TYPES[$type];
        $threshold = $days !== null ? "{$days} days old" : $this->getThresholdDescription($type);

        $this->definitionList(
            ['Type' => $type],
            ['Description' => $description],
            ['Threshold' => $threshold],
            ['Records to delete' => number_format($count)],
        );

        // Show warning
        $this->line();
        $this->danger([
            'WARNING: This operation is DESTRUCTIVE!',
            number_format($count) . ' record(s) will be permanently deleted.',
            'This action cannot be undone.',
        ]);

        if ($isDryRun) {
            $this->displayDryRunNotice();

            return self::SUCCESS;
        }

        // Require confirmation unless --force
        if (!$isForce) {
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->comment('Operation cancelled.');

                return self::SUCCESS;
            }
        }

        // Execute prune
        $this->line();
        $this->section('Processing');

        $startTime = microtime(true);
        $deleted = $this->executePrune($type, $days);
        $elapsed = round(microtime(true) - $startTime, 2);

        $this->line();
        $this->section('Results');
        $this->definitionList(
            ['Records deleted' => number_format($deleted)],
            ['Time elapsed' => $elapsed . 's'],
        );

        $this->success('Prune completed!');

        return self::SUCCESS;
    }

    /**
     * Show information about pruneable data
     */
    private function showInfo(): int
    {
        $this->section('Pruneable Data');

        $rows = [];
        foreach (self::PRUNE_TYPES as $type => $description) {
            $count = $this->getInfoCount($type);
            $config = $this->getConfigInfo($type);
            $rows[] = [$type, $description, number_format($count), $config];
        }

        $this->table(['Type', 'Description', 'Total Records', 'Configuration'], $rows);

        $this->line();
        $this->warning([
            'To delete data, specify --type and --days (where applicable):',
            'php bull maintenance:prune --type=logs --days=30',
        ]);

        return self::SUCCESS;
    }

    /**
     * Get total record count for info display
     */
    private function getInfoCount(string $type): int
    {
        return match ($type) {
            'logs' => $this->database->table(BB_LOG)->count('*'),
            'pm' => $this->database->table(BB_PRIVMSGS)->count('*'),
            'sessions' => $this->database->table(BB_SESSIONS)->count('*'),
            'search' => $this->database->table(BB_SEARCH)->count('*'),
            default => 0,
        };
    }

    /**
     * Get configuration info for display
     */
    private function getConfigInfo(string $type): string
    {
        return match ($type) {
            'logs' => 'log_days_keep: ' . ($this->config->get('log_days_keep') ?: 'not set'),
            'pm' => 'pm_days_keep: ' . ($this->config->get('pm_days_keep') ?: 'not set'),
            'sessions' => 'user_session_duration: ' . $this->config->get('user_session_duration') . 's',
            'search' => 'expires after 3 hours',
            default => '-',
        };
    }

    /**
     * Get count of records that would be deleted
     */
    private function getRecordCount(string $type, ?int $days): int
    {
        return match ($type) {
            'logs' => $this->getLogsCount($days),
            'pm' => $this->getPmCount($days),
            'sessions' => $this->getSessionsCount(),
            'search' => $this->getSearchCount(),
            default => 0,
        };
    }

    /**
     * Get a threshold description for types without --days
     */
    private function getThresholdDescription(string $type): string
    {
        return match ($type) {
            'sessions' => 'Expired sessions (based on session duration config)',
            'search' => 'Older than 3 hours',
            default => '-',
        };
    }

    /**
     * Get logs count for given days
     */
    private function getLogsCount(int $days): int
    {
        $cutoff = TIMENOW - 86400 * $days;

        return $this->database->table(BB_LOG)
            ->where('log_time < ?', $cutoff)
            ->count('*');
    }

    /**
     * Get PM count for given days
     */
    private function getPmCount(int $days): int
    {
        $cutoff = TIMENOW - 86400 * $days;

        return $this->database->table(BB_PRIVMSGS)
            ->where('privmsgs_date < ?', $cutoff)
            ->count('*');
    }

    /**
     * Get expired sessions count
     * Note: Complex OR conditions - using raw SQL
     */
    private function getSessionsCount(): int
    {
        $userExpire = TIMENOW - (int)$this->config->get('user_session_duration');
        $adminExpire = TIMENOW - (int)$this->config->get('admin_session_duration');
        $gcTime = $userExpire - (int)$this->config->get('user_session_gc_ttl');

        $row = $this->database->fetch_row('
            SELECT COUNT(*) as cnt FROM ' . BB_SESSIONS . "
            WHERE (session_time < {$gcTime} AND session_admin = 0)
               OR (session_time < {$adminExpire} AND session_admin != 0)
        ");

        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Get expired search results count
     */
    private function getSearchCount(): int
    {
        $expire = TIMENOW - 3 * 3600;

        return $this->database->table(BB_SEARCH)
            ->where('search_time < ?', $expire)
            ->count('*');
    }

    /**
     * Execute the prune operation
     */
    private function executePrune(string $type, ?int $days): int
    {
        return match ($type) {
            'logs' => $this->pruneLogs($days),
            'pm' => $this->prunePm($days),
            'sessions' => $this->pruneSessions(),
            'search' => $this->pruneSearch(),
            default => 0,
        };
    }

    /**
     * Prune logs
     */
    private function pruneLogs(int $days): int
    {
        $cutoff = TIMENOW - 86400 * $days;
        $this->database->query('DELETE FROM ' . BB_LOG . " WHERE log_time < {$cutoff}");

        return $this->database->affected_rows();
    }

    /**
     * Prune private messages
     */
    private function prunePm(int $days): int
    {
        $cutoff = TIMENOW - 86400 * $days;
        $perCycle = 20000;

        $row = $this->database->fetch_row('SELECT MIN(privmsgs_id) AS start_id, MAX(privmsgs_id) AS finish_id FROM ' . BB_PRIVMSGS);
        $startId = (int)($row['start_id'] ?? 0);
        $finishId = (int)($row['finish_id'] ?? 0);

        if ($startId === 0) {
            return 0;
        }

        $totalDeleted = 0;

        while (true) {
            $endId = $startId + $perCycle - 1;

            $this->database->query('
                DELETE pm, pmt
                FROM ' . BB_PRIVMSGS . ' pm
                LEFT JOIN ' . BB_PRIVMSGS_TEXT . " pmt ON(pmt.privmsgs_text_id = pm.privmsgs_id)
                WHERE pm.privmsgs_id BETWEEN {$startId} AND {$endId}
                    AND pm.privmsgs_date < {$cutoff}
            ");

            $totalDeleted += $this->database->affected_rows();

            if ($endId >= $finishId) {
                break;
            }

            $startId += $perCycle;
        }

        return $totalDeleted;
    }

    /**
     * Prune sessions
     */
    private function pruneSessions(): int
    {
        $userExpire = TIMENOW - (int)$this->config->get('user_session_duration');
        $adminExpire = TIMENOW - (int)$this->config->get('admin_session_duration');
        $gcTime = $userExpire - (int)$this->config->get('user_session_gc_ttl');

        // Update user session times before deleting
        $this->database->lock([
            BB_USERS . ' u',
            BB_SESSIONS . ' s',
        ]);

        $this->database->query('
            UPDATE ' . BB_USERS . ' u, ' . BB_SESSIONS . ' s
            SET u.user_session_time = IF(u.user_session_time < s.session_time, s.session_time, u.user_session_time)
            WHERE u.user_id = s.session_user_id
                AND s.session_user_id != ' . GUEST_UID . "
                AND (
                    (s.session_time < {$userExpire} AND s.session_admin = 0)
                    OR (s.session_time < {$adminExpire} AND s.session_admin != 0)
                )
        ");

        $this->database->unlock();

        // Delete sessions
        $this->database->query('
            DELETE FROM ' . BB_SESSIONS . "
            WHERE (session_time < {$gcTime} AND session_admin = 0)
               OR (session_time < {$adminExpire} AND session_admin != 0)
        ");

        return $this->database->affected_rows();
    }

    /**
     * Prune search results
     */
    private function pruneSearch(): int
    {
        $expire = TIMENOW - 3 * 3600;
        $this->database->query('DELETE FROM ' . BB_SEARCH . " WHERE search_time < {$expire}");

        return $this->database->affected_rows();
    }
}
