<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\User;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;
use TorrentPier\Torrent\Passkey;

/**
 * Create a new user from CLI
 */
#[AsCommand(
    name: 'user:create',
    description: 'Create a new user account'
)]
class CreateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username')
            ->addOption('email', 'e', InputOption::VALUE_OPTIONAL, 'Email address')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Password (will prompt if not provided)')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Create as administrator')
            ->addOption('mod', 'm', InputOption::VALUE_NONE, 'Create as moderator')
            ->addOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Non-interactive mode (requires all options)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Create User');

        // Get username
        $username = $input->getArgument('username');
        if (empty($username)) {
            $username = $this->ask('Username');
        }

        if (empty($username)) {
            $this->error('Username is required.');
            return self::FAILURE;
        }

        // Validate username
        $username = $this->cleanUsername($username);
        if (strlen($username) < 2 || strlen($username) > 25) {
            $this->error('Username must be between 2 and 25 characters.');
            return self::FAILURE;
        }

        // Check if username exists using ORM
        if ($this->usernameExists($username)) {
            $this->error("Username '{$username}' already exists.");
            return self::FAILURE;
        }

        // Get email
        $email = $input->getOption('email');
        if (empty($email)) {
            $email = $this->ask('Email address');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Valid email address is required.');
            return self::FAILURE;
        }

        // Check if email exists using ORM
        if ($this->emailExists($email)) {
            $this->error("Email '{$email}' already registered.");
            return self::FAILURE;
        }

        // Get password
        $password = $input->getOption('password');
        if (empty($password)) {
            $password = $this->io->askHidden('Password (hidden)');
            if (empty($password)) {
                $this->error('Password is required.');
                return self::FAILURE;
            }

            $confirmPassword = $this->io->askHidden('Confirm password');
            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match.');
                return self::FAILURE;
            }
        }

        // Validate password
        if (strlen($password) < 6) {
            $this->error('Password must be at least 6 characters.');
            return self::FAILURE;
        }

        // Determine user level
        $level = USER;
        $levelName = 'User';

        if ($input->getOption('admin')) {
            $level = ADMIN;
            $levelName = 'Administrator';
        } elseif ($input->getOption('mod')) {
            $level = MOD;
            $levelName = 'Moderator';
        }

        // Show summary
        $this->section('User Details');
        $this->definitionList(
            ['Username' => $username],
            ['Email' => $email],
            ['Role' => "<info>{$levelName}</info>"],
        );

        if (!$input->getOption('no-interaction') && !$this->confirm('Create this user?', true)) {
            $this->comment('Operation cancelled.');
            return self::SUCCESS;
        }

        // Create user
        try {
            $userId = $this->createUser($username, $email, $password, $level);

            if (!$userId) {
                $this->error('Failed to create user.');
                return self::FAILURE;
            }

            // Generate passkey
            $this->generatePasskey($userId);

            $this->line('');
            $this->success("User '{$username}' created successfully!");

            $this->definitionList(
                ['User ID' => $userId],
                ['Username' => $username],
                ['Email' => $email],
                ['Role' => $levelName],
            );

            if ($level === ADMIN) {
                $this->line('');
                $this->warning('This user has administrator privileges!');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to create user: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }

    /**
     * Clean username
     */
    private function cleanUsername(string $username): string
    {
        $username = preg_replace('#\s+#u', ' ', $username);
        return trim($username);
    }

    /**
     * Check if username exists using ORM
     */
    private function usernameExists(string $username): bool
    {
        return DB()->table(BB_USERS)
                ->where('username', $username)
                ->count() > 0;
    }

    /**
     * Check if email exists using ORM
     */
    private function emailExists(string $email): bool
    {
        return DB()->table(BB_USERS)
                ->where('user_email', $email)
                ->count() > 0;
    }

    /**
     * Create user in database using ORM
     */
    private function createUser(string $username, string $email, string $password, int $level): int|false
    {
        $row = DB()->table(BB_USERS)->insert([
            'username' => $username,
            'user_password' => $this->hashPassword($password),
            'user_email' => $email,
            'user_level' => $level,
            'user_active' => 1,
            'user_regdate' => TIMENOW,
            'user_reg_ip' => '127.0.0.1',
            'user_lang' => config()->get('default_lang', 'en'),
            'tpl_name' => config()->get('tpl_name', 'default'),
        ]);

        return $row ? $row->user_id : false;
    }

    /**
     * Hash password
     */
    private function hashPassword(string $password): string
    {
        $algo = config()->get('password_hash_options.algo', PASSWORD_DEFAULT);
        $options = config()->get('password_hash_options.options', []);

        return password_hash($password, $algo, $options);
    }

    /**
     * Generate passkey for user
     */
    private function generatePasskey(int $userId): void
    {
        try {
            Passkey::generate($userId, true);
        } catch (\Throwable $e) {
            $this->warning('Could not generate passkey: ' . $e->getMessage());
        }
    }
}
