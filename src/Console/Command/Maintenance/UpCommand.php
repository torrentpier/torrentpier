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
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;

/**
 * Bring the application out of maintenance mode
 */
#[AsCommand(
    name: 'maintenance:up',
    description: 'Bring the application out of maintenance mode',
    aliases: ['up']
)]
class UpCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Maintenance Mode');

        $fileRemoved = false;
        $configUpdated = false;

        // Remove maintenance file if exists
        if (is_file(BB_DISABLED)) {
            if (@unlink(BB_DISABLED)) {
                $this->line('  <info>✓</info> Removed maintenance trigger file');
                $fileRemoved = true;
            } else {
                $this->error('Failed to remove maintenance file: ' . BB_DISABLED);
                return self::FAILURE;
            }
        }

        // Disable board_disable config if enabled
        if (config()->get('board_disable')) {
            try {
                DB()->table(BB_CONFIG)
                    ->where('config_name', 'board_disable')
                    ->update(['config_value' => '0']);

                $this->line('  <info>✓</info> Disabled board_disable in config');
                $configUpdated = true;

                // Update cache
                if (function_exists('CACHE')) {
                    CACHE('bb_config')->rm('config_' . BB_CONFIG);
                }
            } catch (\Throwable $e) {
                $this->warning('Could not update config: ' . $e->getMessage());
            }
        }

        if (!$fileRemoved && !$configUpdated) {
            $this->info('Application is already live.');
        } else {
            $this->line('');
            $this->success('Application is now live!');
        }

        return self::SUCCESS;
    }
}

