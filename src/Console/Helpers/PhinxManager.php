<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Helpers;

use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class for working with Phinx migrations programmatically
 *
 * This class creates Phinx configuration dynamically from environment variables,
 * eliminating the need for a separate phinx.php configuration file.
 */
class PhinxManager
{
    private Config $config;
    private Manager $manager;
    private string $environment;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $configArray = $this->buildConfiguration();
        $this->config = new Config($configArray);

        $this->environment = $this->config->getDefaultEnvironment();
        $this->manager = new Manager($this->config, $input, $output);
    }

    /**
     * Build Phinx configuration array from environment variables
     */
    private function buildConfiguration(): array
    {
        $environment = env('APP_ENV', 'production');

        // Database configuration from environment
        $dbConfig = [
            'adapter' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => (int) env('DB_PORT', 3306),
            'name' => env('DB_DATABASE'),
            'user' => env('DB_USERNAME'),
            'pass' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'table_options' => [
                'ENGINE' => 'InnoDB',
                'DEFAULT CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_unicode_ci',
            ],
        ];

        return [
            'paths' => [
                'migrations' => BB_ROOT . 'migrations',
            ],
            'environments' => [
                'default_migration_table' => defined('BB_MIGRATIONS') ? BB_MIGRATIONS : 'bb_migrations',
                'default_environment' => $environment,
                'production' => $dbConfig,
                'development' => $dbConfig,
            ],
            'version_order' => 'creation',
        ];
    }

    /**
     * Get current environment name
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get the Phinx Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get the Phinx Manager
     */
    public function getManager(): Manager
    {
        return $this->manager;
    }

    /**
     * Run all pending migrations
     */
    public function migrate(?int $target = null, bool $fake = false): void
    {
        $this->manager->migrate($this->environment, $target, $fake);
    }

    /**
     * Rollback migrations
     *
     * @param int|null $target Target version to rollback to (null = rollback last)
     */
    public function rollback(?int $target = null): void
    {
        $this->manager->rollback($this->environment, $target);
    }

    /**
     * Get migration status information
     *
     * @return array{pending: int, ran: int, missing: int, migrations: array}
     */
    public function getStatus(): array
    {
        $migrations = $this->manager->getMigrations($this->environment);
        $env = $this->manager->getEnvironment($this->environment);
        $versions = $env->getVersionLog();

        $pending = 0;
        $ran = 0;
        $missing = 0;
        $migrationList = [];

        // Find missing migrations (in version log but file doesn't exist)
        $missingVersions = array_diff_key($versions, $migrations);
        $missing = count($missingVersions);

        foreach ($missingVersions as $version => $versionInfo) {
            $migrationList[] = [
                'version' => $version,
                'name' => $versionInfo['migration_name'] ?? 'Unknown',
                'status' => 'missing',
                'ran_at' => $versionInfo['start_time'] ?? null,
            ];
        }

        // Process actual migrations
        foreach ($migrations as $version => $migration) {
            $migrationName = $migration->getName();
            $isRan = isset($versions[$version]);

            if ($isRan) {
                $ran++;
                $migrationList[] = [
                    'version' => $version,
                    'name' => $migrationName,
                    'status' => 'up',
                    'ran_at' => $versions[$version]['start_time'] ?? null,
                ];
            } else {
                $pending++;
                $migrationList[] = [
                    'version' => $version,
                    'name' => $migrationName,
                    'status' => 'down',
                    'ran_at' => null,
                ];
            }
        }

        // Sort by version
        usort($migrationList, fn($a, $b) => $a['version'] <=> $b['version']);

        return [
            'pending' => $pending,
            'ran' => $ran,
            'missing' => $missing,
            'migrations' => $migrationList,
        ];
    }

    /**
     * Get migrations directory path
     */
    public function getMigrationsPath(): string
    {
        $paths = $this->config->getMigrationPaths();
        return reset($paths);
    }

    /**
     * Generate a new migration file
     */
    public function createMigration(string $name): string
    {
        $className = $this->camelize($name);
        $timestamp = date('YmdHis');
        $fileName = $timestamp . '_' . $this->underscore($name) . '.php';
        $filePath = $this->getMigrationsPath() . DIRECTORY_SEPARATOR . $fileName;

        $template = $this->getMigrationTemplate($className);
        file_put_contents($filePath, $template);

        return $filePath;
    }

    /**
     * Get migration template
     */
    private function getMigrationTemplate(string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class {$className} extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     */
    public function change(): void
    {
        // Example: Create a table
        // \$table = \$this->table('example_table');
        // \$table->addColumn('name', 'string', ['limit' => 255])
        //     ->addColumn('created_at', 'datetime')
        //     ->addIndex(['name'])
        //     ->create();

        // Example: Add column to existing table
        // \$this->table('existing_table')
        //     ->addColumn('new_column', 'string', ['limit' => 100, 'null' => true])
        //     ->update();

        // Example: Remove column
        // \$this->table('existing_table')
        //     ->removeColumn('old_column')
        //     ->update();
    }
}

PHP;
    }

    /**
     * Convert string to CamelCase
     */
    private function camelize(string $string): string
    {
        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        return str_replace(' ', '', $string);
    }

    /**
     * Convert string to snake_case
     */
    private function underscore(string $string): string
    {
        $string = preg_replace('/([A-Z])/', '_$1', $string);
        $string = strtolower(trim($string, '_'));
        return str_replace([' ', '-'], '_', $string);
    }
}
