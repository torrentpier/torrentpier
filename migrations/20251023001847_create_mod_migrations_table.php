<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateModMigrationsTable extends AbstractMigration
{
    /**
     * Create table for tracking mod migrations
     *
     * Each mod has its own migrations in mods/{mod_id}/migrations/
     * This table tracks which migrations have been executed for each mod
     */
    public function change(): void
    {
        $table = $this->table('bb_mod_migrations', [
            'id' => false,
            'primary_key' => ['mod_id', 'version']
        ]);

        $table
            ->addColumn('mod_id', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'Mod identifier (e.g., karma-system)'
            ])
            ->addColumn('version', 'biginteger', [
                'null' => false,
                'comment' => 'Migration version number (timestamp)'
            ])
            ->addColumn('migration_name', 'string', [
                'limit' => 100,
                'null' => true,
                'comment' => 'Human-readable migration name'
            ])
            ->addColumn('start_time', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'When migration started'
            ])
            ->addColumn('end_time', 'timestamp', [
                'null' => true,
                'comment' => 'When migration completed'
            ])
            ->addColumn('breakpoint', 'boolean', [
                'default' => false,
                'comment' => 'Whether migration has a breakpoint'
            ])
            ->addIndex(['mod_id'])
            ->addIndex(['start_time'])
            ->create();
    }
}
