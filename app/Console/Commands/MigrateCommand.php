<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Migrate Command
 *
 * Run database migrations using Phinx
 */
class MigrateCommand extends Command
{
    /**
     * The command signature
     */
    protected string $signature = 'migrate';

    /**
     * The command description
     */
    protected string $description = 'Run database migrations';

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this->addOption(
            'fake',
            null,
            InputOption::VALUE_NONE,
            'Mark migrations as run without actually running them'
        )
            ->addOption(
                'target',
                't',
                InputOption::VALUE_REQUIRED,
                'Target migration version'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force running migrations in production'
            );
    }

    /**
     * Handle the command
     */
    public function handle(): int
    {
        $basePath = $this->app->make('path.base');
        $phinxConfig = $basePath . '/phinx.php';

        if (!file_exists($phinxConfig)) {
            $this->error('Phinx configuration file not found at: ' . $phinxConfig);
            return self::FAILURE;
        }

        $this->info('Running database migrations...');

        // Build phinx command
        $command = 'cd ' . escapeshellarg($basePath) . ' && ';
        $command .= 'vendor/bin/phinx migrate';
        $command .= ' --configuration=' . escapeshellarg($phinxConfig);

        if ($this->option('fake')) {
            $command .= ' --fake';
        }

        if ($this->option('target')) {
            $command .= ' --target=' . escapeshellarg($this->option('target'));
        }

        if ($this->option('force')) {
            $command .= ' --no-interaction';
        }

        // Execute the command
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        // Display output
        foreach ($output as $line) {
            $this->line($line);
        }

        if ($returnCode === 0) {
            $this->success('Migrations completed successfully!');
        } else {
            $this->error('Migration failed with exit code: ' . $returnCode);
        }

        return $returnCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}
