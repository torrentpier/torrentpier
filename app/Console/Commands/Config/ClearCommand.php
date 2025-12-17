<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Console\Commands\Config;

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'config:clear',
    description: 'Remove the configuration cache file',
)]
class ClearCommand extends Command
{
    /**
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cachedConfigPath = app()->getCachedConfigPath();

        if (files()->exists($cachedConfigPath)) {
            files()->delete($cachedConfigPath);
            $output->writeln('<info>Configuration cache cleared successfully.</info>');
        } else {
            $output->writeln('<comment>Configuration cache file does not exist.</comment>');
        }

        return Command::SUCCESS;
    }
}
