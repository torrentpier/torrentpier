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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;

/**
 * Create a new console command
 */
#[AsCommand(
    name: 'make:command',
    description: 'Create a new console command'
)]
class MakeCommandCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The command name (e.g., "cache:warmup" or "user:create")'
            )
            ->addOption(
                'description',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Command description',
                'Command description'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $description = $input->getOption('description');

        $this->title('Create Command');

        // Parse command name to determine class name and directory
        $parts = explode(':', $name);
        $className = $this->generateClassName($parts);
        $subDir = count($parts) > 1 ? ucfirst($parts[0]) : '';

        // Determine file path
        $baseDir = BB_ROOT . 'src/Console/Command';
        if ($subDir) {
            $dir = $baseDir . '/' . $subDir;
        } else {
            $dir = $baseDir;
        }

        // Ensure directory exists
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $this->error("Failed to create directory: $dir");
                return self::FAILURE;
            }
        }

        $filePath = $dir . '/' . $className . '.php';

        // Check if file already exists
        if (file_exists($filePath)) {
            $this->error("Command file already exists: $filePath");
            return self::FAILURE;
        }

        // Generate namespace
        $namespace = 'TorrentPier\\Console\\Command';
        if ($subDir) {
            $namespace .= '\\' . $subDir;
        }

        // Generate template
        $template = $this->getCommandTemplate($namespace, $className, $name, $description);

        // Write file
        if (file_put_contents($filePath, $template) === false) {
            $this->error("Failed to write file: $filePath");
            return self::FAILURE;
        }

        $this->success('Command created successfully!');
        $this->line('');
        $this->definitionList(
            ['Command' => $name],
            ['Class' => $namespace . '\\' . $className],
            ['File' => $filePath],
        );

        $this->line('');
        $this->comment('The command is automatically registered and ready to use.');
        $this->line('Run "php bull ' . $name . '" to test it.');

        return self::SUCCESS;
    }

    /**
     * Generate class name from command name parts
     */
    private function generateClassName(array $parts): string
    {
        // Take the last part for the class name
        $baseName = end($parts);

        // Convert to PascalCase and add Command suffix
        $className = str_replace(['-', '_'], ' ', $baseName);
        $className = ucwords($className);
        $className = str_replace(' ', '', $className);

        if (!str_ends_with($className, 'Command')) {
            $className .= 'Command';
        }

        return $className;
    }

    /**
     * Get command template
     */
    private function getCommandTemplate(
        string $namespace,
        string $className,
        string $commandName,
        string $description
    ): string {
        return <<<PHP
<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace {$namespace};

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentPier\Console\Command\Command;

/**
 * {$description}
 */
#[AsCommand(
    name: '{$commandName}',
    description: '{$description}'
)]
class {$className} extends Command
{
    protected function configure(): void
    {
        // Add arguments and options here
        // \$this->addArgument('name', InputArgument::REQUIRED, 'Description');
        // \$this->addOption('option', 'o', InputOption::VALUE_NONE, 'Description');
    }

    protected function execute(InputInterface \$input, OutputInterface \$output): int
    {
        \$this->title('{$description}');

        // Your command logic here
        \$this->info('Command executed successfully!');

        return self::SUCCESS;
    }
}

PHP;
    }
}
