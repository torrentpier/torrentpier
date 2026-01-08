<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Commands\Install;

use Illuminate\Contracts\Container\BindingResolutionException;
use mysqli;
use mysqli_sql_exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use TorrentPier\Console\Commands\Command;
use TorrentPier\Console\Helpers\FileSystemHelper;
use TorrentPier\Console\Helpers\PhinxManager;

/**
 * Interactive TorrentPier installation wizard
 */
#[AsCommand(
    name: 'app:install',
    description: 'Interactive TorrentPier installation wizard',
)]
class InstallCommand extends Command
{
    /**
     * System requirements
     */
    private const string PHP_MIN_VERSION = '8.4.0';
    private const array REQUIRED_EXTENSIONS = [
        'json',
        'curl',
        'readline',
        'mysqli',
        'pdo',
        'pdo_mysql',
        'bcmath',
        'mbstring',
        'intl',
        'xml',
        'xmlwriter',
        'zip',
        'gd',
    ];

    /**
     * Writable directories (storage structure)
     */
    private const array WRITABLE_DIRS = [
        'storage/app/public/avatars',
        'storage/app/public/sitemap',
        'storage/app/private/uploads',
        'storage/logs',
        'storage/framework/cache',
        'storage/framework/templates',
        'storage/framework/triggers',
    ];

    /**
     * Collected configuration
     */
    private array $config = [];

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force reinstallation')
            ->addOption('skip-composer', null, InputOption::VALUE_NONE, 'Skip Composer check (dependencies already installed)')
            ->addOption('skip-cleanup', null, InputOption::VALUE_NONE, 'Skip cleanup step');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->showWelcome();

        $force = $input->getOption('force');
        $skipComposer = $input->getOption('skip-composer');
        $skipCleanup = $input->getOption('skip-cleanup');

        // Step 1: Check if already installed
        if ($this->isInstalled() && !$force) {
            return $this->handleReinstall();
        }

        // Step 2: Check system requirements
        if (!$this->checkRequirements()) {
            return self::FAILURE;
        }

        // Step 3: Check/fix permissions
        $this->fixPermissions();

        // Step 4: Check Composer dependencies
        if (!$skipComposer && !$this->checkComposer()) {
            return self::FAILURE;
        }

        // Step 5: Configure environment
        if (!$this->configureEnvironment()) {
            return self::FAILURE;
        }

        // Step 6: Setup database
        if (!$this->setupDatabase()) {
            return self::FAILURE;
        }

        // Step 7: Run migrations
        if (!$this->runMigrations()) {
            return self::FAILURE;
        }

        // Step 8: Post-installation tasks
        $this->postInstallTasks();

        // Step 9: Web server configuration
        $this->showWebServerConfig();

        // Step 10: Cleanup
        if (!$skipCleanup) {
            $this->cleanup();
        }

        // Done!
        $this->showSuccess();

        return self::SUCCESS;
    }

    /**
     * Show welcome banner
     */
    private function showWelcome(): void
    {
        $this->io->writeln('');
        $this->io->writeln('<fg=cyan>  ╔══════════════════════════════════════════════════════════╗</>');
        $this->io->writeln('<fg=cyan>  ║</>                                                          <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>   <fg=white;options=bold>████████╗ ██████╗ ██████╗ ██████╗ ███████╗███╗   ██╗████████╗</>  <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>   <fg=white>╚══██╔══╝██╔═══██╗██╔══██╗██╔══██╗██╔════╝████╗  ██║╚══██╔══╝</>  <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>   <fg=white>   ██║   ██║   ██║██████╔╝██████╔╝█████╗  ██╔██╗ ██║   ██║   </>  <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>   <fg=white>   ██║   ██║   ██║██╔══██╗██╔══██╗██╔══╝  ██║╚██╗██║   ██║   </>  <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>   <fg=white>   ██║   ╚██████╔╝██║  ██║██║  ██║███████╗██║ ╚████║   ██║   </>  <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>   <fg=white>   ╚═╝    ╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═══╝   ╚═╝   </>  <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>                                                          <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>          <fg=yellow>Bull-powered BitTorrent Tracker Engine</>           <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ║</>                                                          <fg=cyan>║</>');
        $this->io->writeln('<fg=cyan>  ╚══════════════════════════════════════════════════════════╝</>');
        $this->io->writeln('');

        $this->title('Installation Wizard');
    }

    /**
     * Check if TorrentPier is already installed
     * @throws BindingResolutionException
     */
    private function isInstalled(): bool
    {
        return files()->exists(BB_ROOT . '.env');
    }

    /**
     * Handle reinstallation prompt
     * @throws BindingResolutionException
     */
    private function handleReinstall(): int
    {
        $this->warning('TorrentPier is already installed!');

        if (!$this->confirm('Do you want to reinstall? This will reset your configuration.', false)) {
            $this->comment('Installation cancelled.');

            return self::SUCCESS;
        }

        $this->section('Cleaning Previous Installation');

        // Remove .env
        if (files()->exists(BB_ROOT . '.env')) {
            files()->delete(BB_ROOT . '.env');
            $this->line('  <info>✓</info> Removed .env file');
        }

        // Remove composer.phar if exists
        if (files()->exists(BB_ROOT . 'composer.phar')) {
            files()->delete(BB_ROOT . 'composer.phar');
            $this->line('  <info>✓</info> Removed composer.phar');
        }

        $this->success('Previous installation cleaned. Rerun the command to continue.');

        return self::SUCCESS;
    }

    /**
     * Check system requirements
     */
    private function checkRequirements(): bool
    {
        $this->section('Checking System Requirements');

        $allPassed = true;

        // Check PHP version
        $phpOk = version_compare(PHP_VERSION, self::PHP_MIN_VERSION, '>=');
        $this->line(\sprintf(
            '  %s PHP Version: %s (required: %s+)',
            $phpOk ? '<info>✓</info>' : '<error>✗</error>',
            PHP_VERSION,
            self::PHP_MIN_VERSION,
        ));

        if (!$phpOk) {
            $allPassed = false;
        }

        // Check extensions
        $this->line();
        $this->line('  <comment>Extensions:</comment>');

        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $loaded = \extension_loaded($ext);
            $this->line(\sprintf(
                '    %s %s',
                $loaded ? '<info>✓</info>' : '<error>✗</error>',
                $ext,
            ));

            if (!$loaded) {
                $allPassed = false;
            }
        }

        $this->line();

        if ($allPassed) {
            $this->success('All requirements satisfied!');
        } else {
            $this->error('Some requirements are not met. Please install missing components.');
        }

        return $allPassed;
    }

    /**
     * Fix directory permissions
     * @throws BindingResolutionException
     */
    private function fixPermissions(): void
    {
        $this->section('Setting Directory Permissions');

        foreach (self::WRITABLE_DIRS as $dir) {
            $path = BB_ROOT . $dir;
            if (files()->isDirectory($path)) {
                $this->setPermissionsRecursively($path, 0755, 0644);
                $this->line(\sprintf('  <info>✓</info> %s', $dir));
            } else {
                $this->line(\sprintf('  <comment>-</comment> %s (not found)', $dir));
            }
        }

        $this->line();
    }

    /**
     * Set permissions recursively
     * @throws BindingResolutionException
     */
    private function setPermissionsRecursively(string $dir, int $dirPerm, int $filePerm): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        files()->chmod($dir, $dirPerm);

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                files()->chmod($item->getPathname(), $dirPerm);
            } else {
                files()->chmod($item->getPathname(), $filePerm);
            }
        }
    }

    /**
     * Check Composer dependencies
     * @throws BindingResolutionException
     */
    private function checkComposer(): bool
    {
        $this->section('Checking Composer Dependencies');

        if (files()->exists(BB_ROOT . 'vendor/autoload.php')) {
            $this->line('  <info>✓</info> Dependencies installed');

            return true;
        }

        $this->warning('Composer dependencies not found.');
        $this->line();
        $this->line('Please run one of the following commands:');
        $this->line('  <comment>composer install</comment>');
        $this->line('  <comment>php composer.phar install</comment>');
        $this->line();

        return false;
    }

    /**
     * Configure environment (.env file)
     * @throws BindingResolutionException
     */
    private function configureEnvironment(): bool
    {
        $this->section('Environment Configuration');

        // Create .env from example if needed
        if (!files()->exists(BB_ROOT . '.env')) {
            if (!files()->exists(BB_ROOT . '.env.example')) {
                $this->error('.env.example file not found!');

                return false;
            }

            files()->copy(BB_ROOT . '.env.example', BB_ROOT . '.env');
            $this->line('  <info>✓</info> Created .env file from template');
        }

        $this->line();
        $this->line('  <comment>Please configure the following settings:</comment>');
        $this->line();

        // Application settings
        $this->config['APP_ENV'] = $this->choice(
            'Application environment',
            ['production', 'local'],
            'production',
        );

        $this->config['TP_HOST'] = $this->askWithValidation(
            'Site hostname (e.g., tracker.example.com)',
            fn ($v) => !empty($v),
            'Hostname cannot be empty',
        );

        // Clean up hostname
        $host = $this->config['TP_HOST'];
        if (preg_match('/^https?:\/\//', $host)) {
            $host = parse_url($host, PHP_URL_HOST);
        }
        $this->config['TP_HOST'] = $host;

        $this->line();
        $this->line('  <comment>Database configuration:</comment>');
        $this->line();

        // Database settings
        $this->config['DB_HOST'] = $this->ask('Database host', 'localhost');
        $this->config['DB_PORT'] = $this->ask('Database port', '3306');
        $this->config['DB_DATABASE'] = $this->askWithValidation(
            'Database name',
            fn ($v) => !empty($v) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $v),
            'Invalid database name',
        );
        $this->config['DB_USERNAME'] = $this->askWithValidation(
            'Database username',
            fn ($v) => !empty($v),
            'Username cannot be empty',
        );
        $this->config['DB_PASSWORD'] = $this->io->askHidden('Database password (hidden input)') ?? '';

        $this->line();
        $this->line('  <comment>Cron configuration:</comment>');
        $this->line();

        // Cron settings
        $cronChoice = $this->choice(
            'How do you want to handle scheduled tasks?',
            [
                'internal' => 'TorrentPier cron manager (built-in, no external setup needed)',
                'external' => 'External cron (system crontab)',
            ],
            'internal',
        );

        $this->config['APP_CRON_ENABLED'] = $cronChoice === 'internal' ? 'true' : 'false';

        // Write to .env
        $this->writeEnvFile();

        $this->line();
        $this->success('Environment configured!');

        return true;
    }

    /**
     * Ask with validation
     */
    private function askWithValidation(string $question, callable $validator, string $errorMessage): string
    {
        while (true) {
            $value = $this->ask($question);
            if ($validator($value)) {
                return $value;
            }
            $this->line("  <error>{$errorMessage}</error>");
        }
    }

    /**
     * Write configuration to .env file
     * @throws BindingResolutionException
     */
    private function writeEnvFile(): void
    {
        $envPath = BB_ROOT . '.env';
        $content = files()->get($envPath);

        foreach ($this->config as $key => $value) {
            // Replace existing or append
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$replacement}";
            }
        }

        files()->put($envPath, $content);
    }

    /**
     * Setup database
     */
    private function setupDatabase(): bool
    {
        $this->section('Database Setup');

        $host = $this->config['DB_HOST'];
        $port = (int)$this->config['DB_PORT'];
        $database = $this->config['DB_DATABASE'];
        $username = $this->config['DB_USERNAME'];
        $password = $this->config['DB_PASSWORD'];

        // Validate database name (whitelist: alphanumeric and underscore only)
        // DDL statements like CREATE DATABASE don't support prepared statements
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            $this->error('Invalid database name. Only alphanumeric characters and underscores are allowed.');

            return false;
        }

        // Test connection
        $this->line('  Connecting to MySQL server...');

        try {
            $conn = new mysqli($host, $username, $password, port: $port);
        } catch (mysqli_sql_exception $e) {
            $this->error('Connection failed: ' . $e->getMessage());

            return false;
        }

        $this->line('  <info>✓</info> Connected successfully');

        // Check if database exists
        $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$conn->real_escape_string($database)}'");

        if ($result && $result->num_rows > 0) {
            $this->warning("Database '{$database}' already exists!");

            if (!$this->confirm('Drop existing database and create new?', false)) {
                $this->error('Cannot proceed without database reset.');
                $conn->close();

                return false;
            }

            $conn->query("DROP DATABASE `{$database}`");
            $this->line('  <info>✓</info> Dropped existing database');
        }

        // Create database
        $sql = "CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!$conn->query($sql)) {
            $this->error('Failed to create database: ' . $conn->error);
            $conn->close();

            return false;
        }

        $this->line('  <info>✓</info> Database created');
        $conn->close();

        return true;
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): bool
    {
        $this->section('Running Database Migrations');

        try {
            // Reload environment for migrations
            $dotenv = \Dotenv\Dotenv::createMutable(BB_ROOT);
            $dotenv->load();

            $phinx = new PhinxManager($this->input, $this->output);
            $status = $phinx->getStatus();

            if ($status['pending'] === 0) {
                $this->line('  <info>✓</info> Database already up to date');

                return true;
            }

            $this->line(\sprintf('  Running %d migration(s)...', $status['pending']));
            $this->line();

            $phinx->migrate();

            $this->line();
            $this->success('Migrations completed!');

            return true;
        } catch (Throwable $e) {
            $this->error('Migration failed: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return false;
        }
    }

    /**
     * Post-installation tasks
     * @throws BindingResolutionException
     */
    private function postInstallTasks(): void
    {
        $this->section('Post-Installation Tasks');

        // Create storage symlink
        $this->createStorageLink();

        // Update robots.txt
        $robotsFile = BB_ROOT . 'robots.txt';
        if (files()->exists($robotsFile) && files()->isWritable($robotsFile)) {
            $content = files()->get($robotsFile);
            $content = str_replace('example.com', $this->config['TP_HOST'], $content);
            files()->put($robotsFile, $content);
            $this->line('  <info>✓</info> Updated robots.txt');
        }

        // Create local config for development
        if ($this->config['APP_ENV'] === 'local') {
            $localConfig = BB_ROOT . 'config/config.local.php';
            if (!files()->exists($localConfig)) {
                files()->copy(BB_ROOT . 'config/config.php', $localConfig);
                $this->line('  <info>✓</info> Created config.local.php for development');
            }
        }

        $this->line();
    }

    /**
     * Create storage symlink (public/storage -> ../storage/app/public)
     */
    private function createStorageLink(): void
    {
        $command = $this->getApplication()->find('storage:link');
        $arguments = new ArrayInput([]);

        try {
            $command->run($arguments, $this->output);
        } catch (Throwable $e) {
            $this->warning('Could not create storage symlink: ' . $e->getMessage());
            $this->comment('  Run manually: php bull storage:link');
        }
    }

    /**
     * Show web server configuration suggestions
     * @throws BindingResolutionException
     */
    private function showWebServerConfig(): void
    {
        $this->section('Web Server Configuration');

        $webserver = $this->choice(
            'Which web server are you using?',
            ['nginx', 'caddy', 'apache', 'other'],
            'nginx',
        );

        $configFiles = [
            'nginx' => 'install/nginx.conf',
            'caddy' => 'install/Caddyfile',
        ];

        if (isset($configFiles[$webserver])) {
            $configPath = BB_ROOT . $configFiles[$webserver];
            if (files()->exists($configPath)) {
                $this->line();
                $this->line("  <info>Configuration template:</info> {$configPath}");
                $this->line();
                $this->comment('  Copy and adapt this configuration to your web server.');
                $this->comment('  It includes URL rewriting, security headers, and PHP settings.');
            }
        } elseif ($webserver === 'apache') {
            $this->line();
            $this->comment('  For Apache, ensure mod_rewrite is enabled.');
            $this->comment('  Use the provided .htaccess file in the project root.');
        }

        $this->line();
    }

    /**
     * Cleanup development files
     * @throws BindingResolutionException
     */
    private function cleanup(): void
    {
        if ($this->config['APP_ENV'] === 'local') {
            return; // Skip cleanup in development
        }

        $cleanupScript = BB_ROOT . 'install/release_scripts/_cleanup.php';
        if (!files()->exists($cleanupScript)) {
            return;
        }

        $this->section('Cleanup');

        $this->line('  The following files can be removed:');
        $this->line('  - Development documentation (README, CHANGELOG)');
        $this->line('  - Git configuration files');
        $this->line('  - CI/CD pipelines');
        $this->line();

        if ($this->confirm('Remove development files?', false)) {
            require_once $cleanupScript;

            // Remove release scripts directory
            $releaseDir = BB_ROOT . 'install/release_scripts';
            if (files()->isDirectory($releaseDir)) {
                FileSystemHelper::removeDirectory($releaseDir);
            }

            $this->line('  <info>✓</info> Cleanup completed');
        } else {
            $this->comment('  Skipped cleanup');
        }

        $this->line();
    }

    /**
     * Show success message
     */
    private function showSuccess(): void
    {
        $this->io->writeln('');
        $this->io->writeln('<fg=green>  ╔══════════════════════════════════════════════════════════╗</>');
        $this->io->writeln('<fg=green>  ║</>                                                          <fg=green>║</>');
        $this->io->writeln('<fg=green>  ║</>      <fg=white;options=bold>🎉 TorrentPier installed successfully! 🎉</><fg=green>          ║</>');
        $this->io->writeln('<fg=green>  ║</>                                                          <fg=green>║</>');
        $this->io->writeln('<fg=green>  ╚══════════════════════════════════════════════════════════╝</>');
        $this->io->writeln('');

        $this->definitionList(
            ['Site URL' => 'https://' . $this->config['TP_HOST']],
            ['Admin Panel' => 'https://' . $this->config['TP_HOST'] . '/admin/'],
            ['Default Admin' => 'admin / admin (change immediately!)'],
        );

        $this->line();
        $this->comment('Next steps:');

        $nextSteps = [
            'Configure your web server using the provided templates',
            'Login to admin panel and change the default password',
            'Configure site settings in the admin panel',
        ];

        // Add cron setup instruction only if external cron is selected
        if (isset($this->config['APP_CRON_ENABLED']) && $this->config['APP_CRON_ENABLED'] === 'false') {
            $nextSteps[] = 'Setup cron job: <comment>*/10 * * * * cd ' . BB_ROOT . ' && php bull cron:run</comment>';
        }

        $this->listing($nextSteps);

        $this->line('<fg=cyan>Good luck & have fun! 🚀</>');
        $this->line();
    }
}
