<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Make;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Console\Command\Command;
use TorrentPier\Console\Helpers\PhinxManager;

/**
 * Create a new database migration
 */
#[AsCommand(
    name: 'make:migration',
    description: 'Create a new database migration file'
)]
class MakeMigrationCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Migration name in CamelCase (e.g., CreateUsersTable, AddEmailColumn)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $this->title('Create Migration');

        // Validate name - require CamelCase
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name)) {
            $this->error('Invalid migration name. Use CamelCase starting with uppercase.');
            $this->line('Example: CreateUsersTable, AddEmailColumn, DropLegacyFields');
            return self::FAILURE;
        }

        try {
            $phinx = new PhinxManager($input, $output);
            $filePath = $phinx->createMigration($name);

            $this->success('Migration created successfully!');
            $this->line();
            $this->definitionList(
                ['File' => $filePath],
            );

            $this->line();
            $this->comment('Edit the migration file and run "php bull migrate" to apply it.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to create migration: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return self::FAILURE;
        }
    }
}
