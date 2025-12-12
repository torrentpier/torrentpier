<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Maintenance;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;

/**
 * Put the application into maintenance mode
 */
#[AsCommand(
    name: 'maintenance:down',
    description: 'Put the application into maintenance mode',
    aliases: ['down']
)]
class DownCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Custom maintenance message')
            ->addOption('retry', 'r', InputOption::VALUE_OPTIONAL, 'Retry-After header value (seconds)', 60);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Maintenance Mode');

        // Check if already in maintenance
        if ($this->isInMaintenance()) {
            $this->warning('Application is already in maintenance mode.');
            return self::SUCCESS;
        }

        // Create maintenance file
        $maintenanceFile = BB_DISABLED;
        $maintenanceDir = dirname($maintenanceFile);

        if (!is_dir($maintenanceDir)) {
            @mkdir($maintenanceDir, 0755, true);
        }

        $data = [
            'time' => time(),
            'message' => $input->getOption('message') ?? 'Site is under maintenance',
            'retry' => (int)$input->getOption('retry'),
        ];

        if (file_put_contents($maintenanceFile, json_encode($data, JSON_PRETTY_PRINT)) === false) {
            $this->error('Failed to create maintenance file.');
            return self::FAILURE;
        }

        $this->success('Application is now in maintenance mode.');
        $this->line('');
        $this->comment('Users will see a maintenance message.');
        $this->comment('Administrators can still access the admin panel.');
        $this->line('');
        $this->line('To exit maintenance mode, run:');
        $this->line('  <info>php bull maintenance:up</info>');

        return self::SUCCESS;
    }

    /**
     * Check if application is in maintenance mode
     */
    private function isInMaintenance(): bool
    {
        return is_file(BB_DISABLED) || config()->get('board_disable');
    }
}


