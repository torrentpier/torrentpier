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

use App\Bootstrap\LoadConfiguration;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'config:cache',
    description: 'Create a cache file for faster configuration loading',
)]
class CacheCommand extends Command
{
    /**
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cachedConfigPath = app()->getCachedConfigPath();

        files()->delete($cachedConfigPath);
        files()->ensureDirectoryExists(\dirname($cachedConfigPath));

        $config = LoadConfiguration::getFreshConfiguration();
        $content = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;

        files()->put($cachedConfigPath, $content);

        $output->writeln('<info>Configuration cached successfully.</info>');

        return Command::SUCCESS;
    }
}
