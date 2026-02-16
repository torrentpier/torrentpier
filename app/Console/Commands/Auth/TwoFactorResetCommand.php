<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Auth;

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Application;
use TorrentPier\Console\Commands\Command;

/**
 * Reset two-factor authentication for a user or all users
 */
#[AsCommand(
    name: '2fa:reset',
    description: 'Reset two-factor authentication for a user',
)]
class TwoFactorResetCommand extends Command
{
    /**
     * @throws BindingResolutionException
     */
    public function __construct(?Application $app = null)
    {
        parent::__construct($app);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username to reset 2FA for')
            ->addOption('user-id', null, InputOption::VALUE_REQUIRED, 'User ID to reset 2FA for')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Reset 2FA for ALL users')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation prompt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $userId = $input->getOption('user-id');
        $all = $input->getOption('all');
        $force = $input->getOption('force');

        if ($all) {
            return $this->resetAll($force);
        }

        if (!$username && !$userId) {
            $this->error('Provide a username, --user-id, or --all.');
            $this->line('  Usage: bull 2fa:reset <username>');
            $this->line('         bull 2fa:reset --user-id=42');
            $this->line('         bull 2fa:reset --all');

            return self::FAILURE;
        }

        return $this->resetUser($username, $userId, $force);
    }

    private function resetUser(?string $username, ?string $userId, bool $force): int
    {
        $query = eloquent()->table('users');

        if ($userId) {
            $user = $query->where('user_id', (int)$userId)->first(['user_id', 'username', 'totp_enabled']);
        } else {
            $user = $query->where('username', $username)->first(['user_id', 'username', 'totp_enabled']);
        }

        if (!$user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if (!$user->totp_enabled) {
            $this->warning("User '{$user->username}' (ID: {$user->user_id}) does not have 2FA enabled.");

            return self::SUCCESS;
        }

        if (!$force && !$this->confirm("Reset 2FA for user '{$user->username}' (ID: {$user->user_id})?")) {
            $this->comment('Cancelled.');

            return self::SUCCESS;
        }

        $this->doReset($user->user_id);
        $this->success("2FA reset for user '{$user->username}' (ID: {$user->user_id}).");

        return self::SUCCESS;
    }

    private function resetAll(bool $force): int
    {
        $count = eloquent()->table('users')->where('totp_enabled', 1)->count();

        if ($count === 0) {
            $this->info('No users have 2FA enabled.');

            return self::SUCCESS;
        }

        if (!$force && !$this->confirm("Reset 2FA for {$count} user(s)? This cannot be undone.")) {
            $this->comment('Cancelled.');

            return self::SUCCESS;
        }

        eloquent()->table('users')
            ->where('totp_enabled', 1)
            ->update([
                'totp_secret' => '',
                'totp_enabled' => 0,
                'totp_recovery_codes' => null,
                'totp_enabled_at' => 0,
            ]);

        $this->success("2FA reset for {$count} user(s).");

        return self::SUCCESS;
    }

    private function doReset(int $userId): void
    {
        eloquent()->table('users')
            ->where('user_id', $userId)
            ->update([
                'totp_secret' => '',
                'totp_enabled' => 0,
                'totp_recovery_codes' => null,
                'totp_enabled_at' => 0,
            ]);
    }
}
