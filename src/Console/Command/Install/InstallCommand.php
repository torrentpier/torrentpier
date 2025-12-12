<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Command\Install;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use TorrentPier\Console\Command\Command;
use TorrentPier\Console\Helpers\PhinxManager;

/**
 * Interactive TorrentPier installation wizard
 */
#[AsCommand(
    name: 'app:install',
    description: 'Interactive TorrentPier installation wizard'
)]
class InstallCommand extends Command
{
    /**
     * System requirements
     */
    private const PHP_MIN_VERSION = '8.4.0';
    private const REQUIRED_EXTENSIONS = [
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
     * Writable directories
     */
    private const WRITABLE_DIRS = [
        'data',
        'internal_data',
        'sitemap',
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
     */
    private function isInstalled(): bool
    {
        return file_exists(BB_ROOT . '.env');
    }

    /**
     * Handle reinstallation prompt
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
        if (file_exists(BB_ROOT . '.env')) {
            unlink(BB_ROOT . '.env');
            $this->line('  <info>✓</info> Removed .env file');
        }

        // Remove composer.phar if exists
        if (file_exists(BB_ROOT . 'composer.phar')) {
            unlink(BB_ROOT . 'composer.phar');
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
        $this->line(sprintf(
            '  %s PHP Version: %s (required: %s+)',
            $phpOk ? '<info>✓</info>' : '<error>✗</error>',
            PHP_VERSION,
            self::PHP_MIN_VERSION
        ));

        if (!$phpOk) {
            $allPassed = false;
        }

        // Check extensions
        $this->line('');
        $this->line('  <comment>Extensions:</comment>');

        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $loaded = extension_loaded($ext);
            $this->line(sprintf(
                '    %s %s',
                $loaded ? '<info>✓</info>' : '<error>✗</error>',
                $ext
            ));

            if (!$loaded) {
                $allPassed = false;
            }
        }

        $this->line('');

        if ($allPassed) {
            $this->success('All requirements satisfied!');
        } else {
            $this->error('Some requirements are not met. Please install missing components.');
        }

        return $allPassed;
    }

    /**
     * Fix directory permissions
     */
    private function fixPermissions(): void
    {
        $this->section('Setting Directory Permissions');

        foreach (self::WRITABLE_DIRS as $dir) {
            $path = BB_ROOT . $dir;
            if (is_dir($path)) {
                $this->setPermissionsRecursively($path, 0755, 0644);
                $this->line(sprintf('  <info>✓</info> %s', $dir));
            } else {
                $this->line(sprintf('  <comment>-</comment> %s (not found)', $dir));
            }
        }

        $this->line('');
    }

    /**
     * Set permissions recursively
     */
    private function setPermissionsRecursively(string $dir, int $dirPerm, int $filePerm): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        @chmod($dir, $dirPerm);

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @chmod($item->getPathname(), $dirPerm);
            } else {
                @chmod($item->getPathname(), $filePerm);
            }
        }
    }

    /**
     * Check Composer dependencies
     */
    private function checkComposer(): bool
    {
        $this->section('Checking Composer Dependencies');

        if (file_exists(BB_ROOT . 'vendor/autoload.php')) {
            $this->line('  <info>✓</info> Dependencies installed');
            return true;
        }

        $this->warning('Composer dependencies not found.');
        $this->line('');
        $this->line('Please run one of the following commands:');
        $this->line('  <comment>composer install</comment>');
        $this->line('  <comment>php composer.phar install</comment>');
        $this->line('');

        return false;
    }

    /**
     * Configure environment (.env file)
     */
    private function configureEnvironment(): bool
    {
        $this->section('Environment Configuration');

        // Create .env from example if needed
        if (!file_exists(BB_ROOT . '.env')) {
            if (!file_exists(BB_ROOT . '.env.example')) {
                $this->error('.env.example file not found!');
                return false;
            }

            copy(BB_ROOT . '.env.example', BB_ROOT . '.env');
            $this->line('  <info>✓</info> Created .env file from template');
        }

        $this->line('');
        $this->line('  <comment>Please configure the following settings:</comment>');
        $this->line('');

        // Application settings
        $this->config['APP_ENV'] = $this->choice(
            'Application environment',
            ['production', 'development'],
            'production'
        );

        $this->config['TP_HOST'] = $this->askWithValidation(
            'Site hostname (e.g., tracker.example.com)',
            fn($v) => !empty($v),
            'Hostname cannot be empty'
        );

        // Clean up hostname
        $host = $this->config['TP_HOST'];
        if (preg_match('/^https?:\/\//', $host)) {
            $host = parse_url($host, PHP_URL_HOST);
        }
        $this->config['TP_HOST'] = $host;

        $this->line('');
        $this->line('  <comment>Database configuration:</comment>');
        $this->line('');

        // Database settings
        $this->config['DB_HOST'] = $this->ask('Database host', 'localhost');
        $this->config['DB_PORT'] = $this->ask('Database port', '3306');
        $this->config['DB_DATABASE'] = $this->askWithValidation(
            'Database name',
            fn($v) => !empty($v) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $v),
            'Invalid database name'
        );
        $this->config['DB_USERNAME'] = $this->askWithValidation(
            'Database username',
            fn($v) => !empty($v),
            'Username cannot be empty'
        );
        $this->config['DB_PASSWORD'] = $this->io->askHidden('Database password (hidden input)') ?? '';

        // Write to .env
        $this->writeEnvFile();

        $this->line('');
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
            $this->line("  <error>$errorMessage</error>");
        }
    }

    /**
     * Write configuration to .env file
     */
    private function writeEnvFile(): void
    {
        $envPath = BB_ROOT . '.env';
        $content = file_get_contents($envPath);

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

        file_put_contents($envPath, $content);
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

        // Test connection
        $this->line('  Connecting to MySQL server...');

        try {
            $conn = new \mysqli($host, $username, $password, port: $port);
        } catch (\mysqli_sql_exception $e) {
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

            $this->line(sprintf('  Running %d migration(s)...', $status['pending']));
            $this->line('');

            $phinx->migrate();

            $this->line('');
            $this->success('Migrations completed!');

            return true;
        } catch (\Throwable $e) {
            $this->error('Migration failed: ' . $e->getMessage());

            if ($this->isVerbose()) {
                $this->line('<error>' . $e->getTraceAsString() . '</error>');
            }

            return false;
        }
    }

    /**
     * Post-installation tasks
     */
    private function postInstallTasks(): void
    {
        $this->section('Post-Installation Tasks');

        // Update robots.txt
        $robotsFile = BB_ROOT . 'robots.txt';
        if (file_exists($robotsFile) && is_writable($robotsFile)) {
            $content = file_get_contents($robotsFile);
            $content = str_replace('example.com', $this->config['TP_HOST'], $content);
            file_put_contents($robotsFile, $content);
            $this->line('  <info>✓</info> Updated robots.txt');
        }

        // Create local config for development
        if ($this->config['APP_ENV'] === 'development') {
            $localConfig = BB_ROOT . 'library/config.local.php';
            if (!file_exists($localConfig)) {
                copy(BB_ROOT . 'library/config.php', $localConfig);
                $this->line('  <info>✓</info> Created config.local.php for development');
            }
        }

        $this->line('');
    }

    /**
     * Show web server configuration suggestions
     */
    private function showWebServerConfig(): void
    {
        $this->section('Web Server Configuration');

        $webserver = $this->choice(
            'Which web server are you using?',
            ['nginx', 'caddy', 'apache', 'other'],
            'nginx'
        );

        $configFiles = [
            'nginx' => 'install/nginx.conf',
            'caddy' => 'install/Caddyfile',
        ];

        if (isset($configFiles[$webserver])) {
            $configPath = BB_ROOT . $configFiles[$webserver];
            if (file_exists($configPath)) {
                $this->line('');
                $this->line("  <info>Configuration template:</info> {$configPath}");
                $this->line('');
                $this->comment('  Copy and adapt this configuration to your web server.');
                $this->comment('  It includes URL rewriting, security headers, and PHP settings.');
            }
        } elseif ($webserver === 'apache') {
            $this->line('');
            $this->comment('  For Apache, ensure mod_rewrite is enabled.');
            $this->comment('  Use the provided .htaccess file in the project root.');
        }

        $this->line('');
    }

    /**
     * Cleanup development files
     */
    private function cleanup(): void
    {
        if ($this->config['APP_ENV'] === 'development') {
            return; // Skip cleanup in development
        }

        $cleanupScript = BB_ROOT . 'install/release_scripts/_cleanup.php';
        if (!file_exists($cleanupScript)) {
            return;
        }

        $this->section('Cleanup');

        $this->line('  The following files can be removed:');
        $this->line('  - Development documentation (README, CHANGELOG)');
        $this->line('  - Git configuration files');
        $this->line('  - CI/CD pipelines');
        $this->line('');

        if ($this->confirm('Remove development files?', false)) {
            require_once $cleanupScript;

            // Remove release scripts directory
            $releaseDir = BB_ROOT . 'install/release_scripts';
            if (is_dir($releaseDir)) {
                $this->removeDirectory($releaseDir);
            }

            $this->line('  <info>✓</info> Cleanup completed');
        } else {
            $this->comment('  Skipped cleanup');
        }

        $this->line('');
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($dir);
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

        $this->line('');
        $this->comment('Next steps:');
        $this->listing([
            'Configure your web server using the provided templates',
            'Login to admin panel and change the default password',
            'Configure site settings in the admin panel',
            'Setup cron job: <comment>* * * * * php ' . BB_ROOT . 'cron.php</comment>',
        ]);

        $this->line('<fg=cyan>Good luck & have fun! 🚀</>');
        $this->line('');
    }
}

