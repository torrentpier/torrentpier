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

/**
 * Reset user password from CLI
 */
#[AsCommand(
    name: 'user:password',
    description: 'Reset a user\'s password'
)]
class PasswordCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username or email')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'New password (will prompt if not provided)')
            ->addOption('generate', 'g', InputOption::VALUE_NONE, 'Generate random password')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Reset Password');

        // Get username/email
        $identifier = $input->getArgument('username');
        if (empty($identifier)) {
            $identifier = $this->ask('Username or email');
        }

        if (empty($identifier)) {
            $this->error('Username or email is required.');
            return self::FAILURE;
        }

        // Find user using ORM
        $user = $this->findUser($identifier);
        if (!$user) {
            $this->error("User '{$identifier}' not found.");
            return self::FAILURE;
        }

        $this->section('User Found');
        $this->definitionList(
            ['User ID' => $user['user_id']],
            ['Username' => $user['username']],
            ['Email' => $user['user_email']],
            ['Status' => $user['user_active'] ? '<info>Active</info>' : '<comment>Inactive</comment>'],
        );

        // Get new password
        $password = null;
        $generated = false;

        if ($input->getOption('generate')) {
            $password = $this->generatePassword();
            $generated = true;
            $this->line('');
            $this->info("Generated password: <comment>{$password}</comment>");
        } else {
            $password = $input->getOption('password');
            if (empty($password)) {
                $password = $this->io->askHidden('New password (hidden)');
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
        }

        // Validate password
        if (strlen($password) < 6) {
            $this->error('Password must be at least 6 characters.');
            return self::FAILURE;
        }

        // Confirm
        if (!$input->getOption('force')) {
            $this->line('');
            if (!$this->confirm("Reset password for '{$user['username']}'?", true)) {
                $this->comment('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        // Update password using ORM
        try {
            $this->updatePassword($user['user_id'], $password);

            $this->line('');
            $this->success("Password updated for '{$user['username']}'!");

            if ($generated) {
                $this->line('');
                $this->warning('Make sure to save the generated password:');
                $this->line("  <comment>{$password}</comment>");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to update password: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Find user by username or email using ORM
     */
    private function findUser(string $identifier): ?array
    {
        $user = DB()->table(BB_USERS)
            ->select('user_id, username, user_email, user_active, user_level')
            ->where('username = ? OR user_email = ?', $identifier, $identifier)
            ->fetch();

        return $user ? $user->toArray() : null;
    }

    /**
     * Update user password using ORM
     */
    private function updatePassword(int $userId, string $password): void
    {
        $affected = DB()->table(BB_USERS)
            ->where('user_id', $userId)
            ->update([
                'user_password' => $this->hashPassword($password),
                'user_newpasswd' => '',
                'user_actkey' => '',
            ]);

        if ($affected === 0) {
            throw new \RuntimeException('No rows updated');
        }
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
     * Generate random password
     */
    private function generatePassword(int $length = 16): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }
}
