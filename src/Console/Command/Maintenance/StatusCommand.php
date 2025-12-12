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
 * Show maintenance mode status
 */
#[AsCommand(
    name: 'maintenance:status',
    description: 'Show maintenance mode status'
)]
class StatusCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('Maintenance Status');

        $fileLock = is_file(BB_DISABLED);
        $configLock = (bool)config()->get('board_disable');
        $isDown = $fileLock || $configLock;

        // Overall status
        if ($isDown) {
            $this->line('  Status: <error>🔴 MAINTENANCE MODE</error>');
        } else {
            $this->line('  Status: <info>🟢 LIVE</info>');
        }

        $this->line('');

        // Detailed status
        $this->section('Lock Status');

        $rows = [];

        // Trigger file
        $rows[] = [
            'Trigger File',
            $fileLock ? '<error>Locked</error>' : '<info>Unlocked</info>',
            BB_DISABLED,
        ];

        // Config
        $rows[] = [
            'Config (board_disable)',
            $configLock ? '<error>Locked</error>' : '<info>Unlocked</info>',
            'Database config',
        ];

        $this->table(['Source', 'Status', 'Location'], $rows);

        // Show maintenance info if file exists
        if ($fileLock && is_readable(BB_DISABLED)) {
            $content = file_get_contents(BB_DISABLED);
            $data = json_decode($content, true);

            if ($data) {
                $this->section('Maintenance Info');
                $this->definitionList(
                    ['Started' => isset($data['time']) ? date('Y-m-d H:i:s', $data['time']) : 'Unknown'],
                    ['Message' => $data['message'] ?? 'N/A'],
                    ['Retry After' => isset($data['retry']) ? $data['retry'] . ' seconds' : 'N/A'],
                );
            }
        }

        // Actions hint
        $this->line('');
        if ($isDown) {
            $this->comment('To bring the application up, run:');
            $this->line('  <info>php bull maintenance:up</info>');
        } else {
            $this->comment('To enable maintenance mode, run:');
            $this->line('  <info>php bull maintenance:down</info>');
        }

        return self::SUCCESS;
    }
}


