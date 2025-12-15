<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\System;

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Application;
use TorrentPier\Config;
use TorrentPier\Console\Commands\Command;

/**
 * Displays information about the TorrentPier installation
 */
#[AsCommand(
    name: 'about',
    description: 'Display information about TorrentPier',
)]
class AboutCommand extends Command
{
    /**
     * Create a new about command
     *
     * @param Config $config The configuration instance
     * @param Application|null $app The application container (optional)
     * @throws BindingResolutionException
     */
    public function __construct(
        private readonly Config $config,
        ?Application $app = null,
    ) {
        parent::__construct($app);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->title('TorrentPier - Bull-powered BitTorrent tracker engine');

        $rootPath = \defined('BB_ROOT') ? BB_ROOT : $this->app->basePath();
        $version = 'v' . $this->app->version();

        $this->definitionList(
            ['Version' => $version],
            ['PHP Version' => PHP_VERSION],
            ['PHP SAPI' => PHP_SAPI],
            ['Operating System' => PHP_OS],
            ['TorrentPier Root' => $rootPath],
        );

        $this->section('Environment');

        $envInfo = [
            ['Display Errors', \ini_get('display_errors') ? 'On' : 'Off'],
            ['Memory Limit', \ini_get('memory_limit')],
            ['Max Execution Time', \ini_get('max_execution_time') . 's'],
            ['Upload Max Filesize', \ini_get('upload_max_filesize')],
            ['Post Max Size', \ini_get('post_max_size')],
        ];

        $this->table(['Setting', 'Value'], $envInfo);

        $this->section('Loaded Extensions');

        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'gd', 'xml'];
        $extensionStatus = [];

        foreach ($requiredExtensions as $ext) {
            $loaded = \extension_loaded($ext);
            $extensionStatus[] = [
                $ext,
                $loaded ? '<info>✓ Loaded</info>' : '<error>✗ Not loaded</error>',
            ];
        }

        $this->table(['Extension', 'Status'], $extensionStatus);

        $this->section('Documentation');
        $this->line('  Website:  <comment>https://torrentpier.com</comment>');
        $this->line('  Docs:     <comment>https://docs.torrentpier.com</comment>');
        $this->line('  GitHub:   <comment>https://github.com/torrentpier/torrentpier</comment>');
        $this->line();

        return self::SUCCESS;
    }
}
